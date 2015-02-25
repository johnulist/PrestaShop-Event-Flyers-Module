<?php 
/*
*  
*  NOTICE OF LICENSE
*  
*  Event Flyers Module 
*  Allows to upload multiple event flyers and and display them on your home page.
*  Copyright (C) 2014  Larry Sacherich
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of 
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/
    
/*
*  Designed for PrestaShop™ 1.5.6.2
*
*  Event Flyers Module - Allows to upload multiple event flyers and
*  	and display them on your home page.
* 
*  Based on Banner Manager by by Gastón Franzé 
*	 Modified by Larry Sacherich  2014-06-02
*
*  A.K.A. Event flyers
*    A small handbill advertising an event or product. 
*    i.e. leaflet, handout, handbill, brochure, circular or advertisement
*    @since 1.5.6.2
*    Inspired by: Banner Manager Module 
*               
*  Features:
*  - Displays flyers on homepage (and left or right columns - NOT TESTED)
*  - Uses jQuery FancyBox
*  - Expanded configuration options
*  - Select sizes of main image and thumbnail 
*  - Keeps the original image for future image resizing
*  - Option to crop thumbnail to show only top of flyers
* 
*/

if (!defined('_PS_VERSION_'))
	exit;

class eventflyers extends Module
{
	public $path;

	private $_html = '';
	private $_postErrors = array();
	private $dst_width = 640;
  private $dst_height = 480;
  private $dst_thumb_width = 120;
  private $dst_thumb_height = 90;
  private $maxImageSize = 1207200;
  private $crop_to_fit = 0;
  private $errors = '';
  private $haystack = array('http', 'https', 'mailto');

	function __construct()
	{
		$this->name = 'eventflyers';
		$this->tab = 'advertising_marketing';
		$this->version = '1.0';
    $this->author = 'Larry Sacherich';

		parent::__construct();  // The parent construct is required for translations

    $this->displayName = $this->l('Event Flyers');
    $this->description = $this->l('Allows you to add as many Event Flyers as you want on both right or left columns and home page');

		$this->_errors = array();
		$this->path = $this->_path;   //   /modules/eventflyers/
  if ($event_flyers_config = Configuration::get('EVENT_FLYERS_CONFIG'))
    list($this->dst_width, $this->dst_height, $this->dst_thumb_width, $this->dst_thumb_height, $this->maxImageSize, $this->crop_to_fit) = explode(",", $event_flyers_config);
//     list($this->dst_width, $this->dst_height, $this->dst_thumb_width, $this->dst_thumb_height, $this->maxImageSize, $this->crop_to_fit) = explode(",", Configuration::get('EVENT_FLYERS_CONFIG'));
	}

	function install()
	{
		if (parent::install() == false
				|| !$this->registerHook('header')
				|| !$this->registerHook('home')
				|| !$this->registerHook('leftColumn')
				|| !$this->registerHook('rightColumn')
        || !$this->registerHook('displayBackOfficeHeader')
        || !Configuration::updateValue('EVENT_FLYERS_CONFIG', "$this->dst_width, $this->dst_height, $this->dst_thumb_width, $this->dst_thumb_height, $this->maxImageSize, $this->crop_to_fit") 
        || $this->_createTables() == false)
			return false;
		return true;
	}

	function uninstall()
	{
		$db = Db::getInstance();
		$query = 'DROP TABLE `'._DB_PREFIX_.'eventflyers`';
    if (!parent::uninstall()
				|| !$db->Execute($query)
				|| !$this->unregisterHook('header')
				|| !$this->unregisterHook('home')
				|| !$this->unregisterHook('leftColumn')
				|| !$this->unregisterHook('rightColumn')
        || !$this->unregisterHook('displayBackOfficeHeader')
        || !Configuration::deleteByName('EVENT_FLYERS_CONFIG'))
			return false;
      
    $dst_evnt_dir = dirname(__FILE__).'/flyers/';
    $this->delAllImages($dst_evnt_dir);
     
		return true;
	}

	/**
	*	createTables()
	*	Called from within eventflyers.php when intalling
	*/
	public function _createTables()
	{
		$db = Db::getInstance();
		/*	Create events flyers table */
		$query = 'CREATE TABLE `'._DB_PREFIX_.'eventflyers` (
			  `id_eventflyer` int(6) NOT NULL AUTO_INCREMENT,
			  `description` varchar(255) NOT NULL default "",
			  `image_name` varchar(255) NOT NULL default "",
			  `image_link` varchar(255) NOT NULL default "",
			  `open_blank` tinyint(1) NOT NULL default "0",
			  `active` tinyint(1) NOT NULL default "1",
			  `block_id` int(2) NOT NULL default "0",
			  `order` int(10) NOT NULL default "0",
			  PRIMARY KEY (`id_eventflyer`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8';
		$result = $db->Execute($query);
		if (!$result)
			return false;
		return true;
	}
  
  function delAllImages($dir) {
     $files = array_diff(scandir($dir), array('.','..'));
      foreach ($files as $file) {
        (is_dir("$dir/$file")) ? $this->delAllImages("$dir/$file") : unlink("$dir/$file");
      }
      // return rmdir($dir);
  }

	/**   
	*	getContent()
	*	Called in Back Office when user clicks "Configure"
	*/
	function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';		//Display Header
		if (!empty($_POST)){
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= "<div class='alert error'>{$err}</div>";
		} else
			$this->_html .= "<br />";

		$this->_displayEventflyersHeader();
		$this->_setConfigurationForm();
		$this->_displayEventsAdd();
		return $this->_html;
	}

	/**
	*	_displayEventflyersHeader()
	*	Called in Back Office during Module Configuration
	*/
	private function _displayEventflyersHeader()
  {
		$modDesc 	= $this->l('This module allows you to include as many event flyers as you like.');
		$modStatus	= $this->l('You can upload, order, activate or deactivate as many event flyers and select if you want them in the right or left columns.');
		$this->_html .= "<img src='../modules/eventflyers/eventflyers.gif' style='float:left; margin-right:15px;' /><br>
						<b>{$modDesc}</b><br>
						{$modStatus}<br><br><br><br>"; 
	}

	/**
	*	_setConfigurationForm()
	*	Called upon successful module configuration validation
	*/
	private function _setConfigurationForm()
  {
    $this->context->controller->addJqueryPlugin('tabpane');
		$this->_html .= '
    <label><p style="padding-bottom:20px;">'.$this->l('Add a new event:').'</p></label>
    <div>
      <a href="" onclick="addEvent();return false;" rel="nofollow"><img border="0" src="'.$this->path.'new.png" /></a>
    </div>
		<script type="text/javascript">
			var pos_select = '.(($tab = intval(Tools::getValue('tabs'))) ? $tab : '0').';
		</script>
		<script type="text/javascript" src="'._PS_BASE_URL_._PS_JS_DIR_.'tabpane.js"></script>
		<link type="text/css" rel="stylesheet" href="'._PS_BASE_URL_._PS_CSS_DIR_.'tabpane.css" />
    <div>
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
			<input type="hidden" name="tabs" id="tabs" value="0" />
    	<div class="tab-pane" id="tab-pane-1" style="width:100%; margin:10px">
        <div class="tab-page" id="step1">
          <h4 class="tab">'.$this->l('Configuration').'</h4>
      		<!--form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post"-->
      		<fieldset><legend><img src="'.$this->path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
    			<p class="eventHelp">'.$this->l('For best results keep the image width x height proportional. For example 640x480 or 800x600 and thumbnails at 100x75, 120x90 or 140x105.').'</p>
          <label>'.$this->l('Image width:').'</label>
    			<div class="margin-form">
    				<input type="text" size="4" name="img_width" value="'.$this->dst_width.'" /> 
    				<p class="clear">'.$this->l('Define the width of the full image (default: 640).').'</p>
    			</div>
    			<label>'.$this->l('Image height:').'</label>      
    			<div class="margin-form">
    				<input type="text" size="4" name="img_height" value="'.$this->dst_height.'" />
    				<p class="clear">'.$this->l('Define the height of the full image (default: 480).').'</p>
    			</div>
    			<label>'.$this->l('Thumbnail width:').'</label>
    			<div class="margin-form">
    				<input type="text" size="4" name="th_img_width" value="'.$this->dst_thumb_width.'" />
    				<p class="clear">'.$this->l('Define the width of the new thumbnail image (default: 120).').'</p>
    			</div>
    			<label>'.$this->l('Thumbnail height:').'</label>
    			<div class="margin-form">
    				<input type="text" size="4" name="th_img_height" value="'.$this->dst_thumb_height.'" />
    				<p class="clear">'.$this->l('Define the height of the new thumbnail image (default: 90).').'</p>
    			</div>
    			<label>'.$this->l('Maximum image size:').'</label>
    			<div class="margin-form">
    				<input type="text" size="8" name="max_img_size" value="'.$this->maxImageSize.'" />
    				<p class="clear">'.$this->l('Maximum image size allowed for upload (default: 1207200).').'</p>
    			</div>
          <label for="event_block_id">'.$this->l('Crop-to-fit:').'</label>
          <div class="margin-form">
            <input id="event_block_id_left" value="1" name="crop_to_fit" type="radio"'.($this->crop_to_fit==1 ? ' checked="checked" ' : ' ').' />
            <label for="event_block_id_left" class="t">'.$this->l('Yes').'</label>
            <br>
            <input id="event_block_id_right" value="0" name="crop_to_fit" type="radio"'.($this->crop_to_fit==0 ? ' checked="checked" ' : ' ').' />
            <label for="event_block_id_right" class="t">'.$this->l('No').'</label>
    				<p class="clear">'.$this->l('Crop to fit thumbnail image height. (default: No).').'</p>
          </div>  
          <p class="margin-form">
            <input class="button" name="updateConfigSubmit" value="'.$this->l('Update configuration').'" type="submit" />
          </p>
		</form>      
    </div>
		<div class="tab-page" id="step2">
			<h4 class="tab">'.$this->l('Left Events').'</h4>
			'.$this->_displayEventsTab('1', 'Left').'
		</div>
		<div class="tab-page" id="step3">
			<h4 class="tab">'.$this->l('Right Events').'</h4>
			'.$this->_displayEventsTab('2', 'Right').'
		</div>
		<div class="tab-page" id="step4">
			<h4 class="tab">'.$this->l('Home Events').'</h4>
			'.$this->_displayEventsTab('3', 'Home').'
		</div>
		</div>
		<div class="clear"></div>
		<script type="text/javascript">
  		function loadTab(id){}
  		setupAllTabs();
		</script>
    <div>
    <!-- popup_image -->        
    <a href="#x" class="overlay" id="popup_image"></a>        
    <div class="popup">            
      <img id="popImg" src="" style="max-height:750px;" />            
      <a class="close" href="#close"></a>        
    </div>
    </div>
		';
	}

	private function _displayEventsTab($block, $title)
  {
		global $smarty, $currentIndex;

		$smarty->assign(array(
			'path'			   => $this->path,
			'events' 	     => $this->getEvents($block),
			'block'			   => $block,
			'title'			   => $title,
			'leftEvents'	 => '2',
			'currentIndex' => $currentIndex,
			'rand'		     => rand()
		));
		return $this->display(__FILE__,'eventflyers_edit.tpl');
	}

	private function _displayEventsAdd()
  {
		global $smarty, $currentIndex;
		$smarty->assign(array(
			'path'			=> $this->path
		));

		$this->_html .=  $this->display(__FILE__,'eventflyers_add.tpl');
	}

	/**
	*	_postProcess()
	*	Called upon successful module configuration validation
	*/
	private function _postProcess()
  {
		// Event flyers Configuration Submit // 
    if (isset($_POST['updateConfigSubmit'])) 
    {
      $recreateImages = false;
      $this->errors = '';
      
      // Do images need to be recreated?
      if ( $this->dst_width != (int)Tools::getValue('img_width')
        || $this->dst_height != (int)Tools::getValue('img_height') 
        || $this->dst_thumb_width != (int)Tools::getValue('th_img_width')
        || $this->dst_thumb_height != (int)Tools::getValue('th_img_height')
        || $this->crop_to_fit != Tools::getValue('crop_to_fit')
        )
        $recreateImages = true;
    
			$this->dst_width = (int)Tools::getValue('img_width');
	    $this->dst_height = (int)Tools::getValue('img_height');
			$this->dst_thumb_width = (int)Tools::getValue('th_img_width');
			$this->dst_thumb_height = (int)Tools::getValue('th_img_height');
			$this->maxImageSize = (int)Tools::getValue('max_img_size');
			$this->crop_to_fit = (int)Tools::getValue('crop_to_fit');
      
      if ( !$this->dst_width > 0
        || !$this->dst_height > 0
        || !$this->dst_thumb_width > 0
        || !$this->dst_thumb_height > 0
        || !$this->maxImageSize > 0 
        )
        $this->errors .= $this->displayError($this->l('Error: All values must be greater than zero.'));        
      elseif ($this->crop_to_fit != 0 AND $this->crop_to_fit != 1)
        $this->errors .= $this->displayError($this->l('Crop-to-fit must be 0 or 1.'));        
      elseif (!Configuration::updateValue('EVENT_FLYERS_CONFIG', "$this->dst_width, $this->dst_height, $this->dst_thumb_width, $this->dst_thumb_height, $this->maxImageSize, $this->crop_to_fit"))
        $this->errors .= $this->displayError($this->l('Error saving configuration information.'));        
      else 
      {  
        if ($recreateImages AND $this->errors=='') 
        {
          if (!$this->recreateImages())
    				$this->errors .= '<div class="alert error">'.$this->l('Error: Recreating images failed').'</div>';
          // Optional message 
          // else  
          //   $this->_html .= $this->displayConfirmation($this->l('Images were recreated successfully'));  
        }
      }
      $this->_html .= (isset($this->errors) && $this->errors != '') ? $this->errors : $this->displayConfirmation('Event flyers configuration updated successfully.');         
    } // End Configuration Submit
  
		// Event flyers Update Submit - Multiple Events //
		if (isset($_POST['updateEventSubmit']))
    {
			$events = Tools::getValue('eventflyersId');
      $this->errors = '';
			if ($events AND is_array($events) AND count($events))
      {
				foreach ($events AS $row)
        {
					$evnt = array();
					$evnt['id'] = $row;
					$evnt['description'] = Tools::getValue('desc_'.$row);
          $evnt['image_link']  = Tools::getValue('link_'.$row);
					$evnt['block_id']    = Tools::getValue('block_'.$row);
					$evnt['order']       = Tools::getValue('order_'.$row);
					$evnt['blank']       = (Tools::getValue('blank_'.$row) ? '1' : '0');
					$evnt['active']      = (Tools::getValue('active_'.$row) ? '1' : '0');
          
          if (!in_array(strstr($evnt['image_link'],':',true), $this->haystack))
          {
    				if (!strpos($evnt['image_link'], "@") === false) 
              $evnt['image_link'] = 'mailto:'.$evnt['image_link'];
            elseif (strpos($evnt['image_link'], "/") === false) 
              $evnt['image_link'] = 'http://'.$evnt['image_link']; 
            else
              $this->errors .= '<div class="alert error">'.$this->l('Link for "'.$evnt['description'].'" must start with: ').implode(", ",$this->haystack).'</div>';      
          }
          
    			/* upload the image */
    			if (isset($_FILES['event_image_'.$row]) AND isset($_FILES['event_image_'.$row]['tmp_name']) AND !empty($_FILES['event_image_'.$row]['tmp_name']))
    			{
    				Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
            
            $src_path     = $_FILES['event_image_'.$row];              //  Array(name, type, tmp_name, error, size)
            $src_name     = $_FILES['event_image_'.$row]['name'];      //  realname.jpg
            $src_tmp_name = $_FILES['event_image_'.$row]['tmp_name'];  //  /tmp/phpQKj3ss
            
    				$dst_image    = dirname(__FILE__).'/flyers/'.$src_name;    // Main image
    				$dst_org      = dirname(__FILE__).'/flyers/o/'.$src_name;  // Original image
    				$dst_thumb    = dirname(__FILE__).'/flyers/t/'.$src_name;  // Thumbnail image

    				if ($error = ImageManager::validateUpload($src_path, $this->maxImageSize))
              $this->errors .= $error; 
    				elseif (!move_uploaded_file($src_tmp_name, $dst_org))
    					$this->errors .= $this->displayError($this->l('An error occurred during the image upload.'));
            elseif (!ImageManager::resize($dst_org, $dst_image, $this->dst_width, $this->dst_height))
              $this->errors .= $this->displayError($this->l('An error occurred during the image resize.'));
            else
            {
        		$img_info  = getimagesize($dst_org);
            $long_img  = (($img_info[1] > $img_info[0]) ? 1 : 0); // height > width 
            }
      
            /* Crop to Fit Thumbnail Height */
            if ($this->crop_to_fit==1 && $long_img==1 && $this->errors=='')
            {
              if (!ImageManager::resize($dst_org, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
                $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image resize.'));        
              elseif (!ImageManager::cut($dst_thumb, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
                $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image crop.'));        
            } 
            else 
            {
              /* Resize only, no cropping */
              if (!ImageManager::resize($dst_org, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
                $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image resize.'));        
            }

    				if (isset($this->errors) && $this->errors)
    					$this->errors .= $this->displayError($this->l('Error creating event.'));
    				else 
            	$evnt['image_name'] = $src_name;
    			}
          else
            $evnt['image_name'] = Tools::getValue('image_name_'.$row); 

          $evnts[] = $evnt;
				} // foreach
        
				if (!$this->saveEvents($evnts))
					$this->errors .= '<div class="alert error">'.$this->l('There were problems saving the event flyers.').'</div>';
		  } 
		  $this->_html .= (isset($this->errors) && $this->errors != '') ? $this->errors : $this->displayConfirmation('Event flyers were successfully updated.');      
    } // End Update Submit
          
		// Event flyers Add Submit //
    if (isset($_POST['addEventSubmit'])) 
    {
      $this->errors = '';
      $evnt = array();
      $evnt['image_name']  = $_FILES['event_image']['name']; 
			$evnt['description'] = Tools::getValue('event_description');
	    $evnt['image_link']  = Tools::getValue('event_link');
			$evnt['block_id']    = Tools::getValue('event_block_id');
   		$evnt['order']       = Tools::getValue('event_order');
			$evnt['blank']       = (Tools::getValue('event_blank') ? '1' : '0');
			$evnt['active']      = (Tools::getValue('event_active') ? '1' : '0');

			/* upload the image */
    	if (isset($_FILES['event_image']) AND isset($_FILES['event_image']['tmp_name']) AND !empty($_FILES['event_image']['tmp_name']))
    	{
    		Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);

        $src_path     = $_FILES['event_image'];
        $src_name     = $_FILES['event_image']['name'];    
        $src_tmp_name = $_FILES['event_image']['tmp_name']; 
           
    		$dst_image    = dirname(__FILE__).'/flyers/'.$src_name;
    		$dst_org      = dirname(__FILE__).'/flyers/o/'.$src_name;
    		$dst_thumb    = dirname(__FILE__).'/flyers/t/'.$src_name;

        if (file_exists($dst_image)) 
        {
          $file_parts = pathinfo($dst_image);     
          $name = $file_parts['filename'];        //  imagename
          $ext = $file_parts['extension'];        //  jpg
          $i = 0;
          while (file_exists($dst_image)) 
          {
            $i++;  
    		    $dst_image = dirname(__FILE__).'/flyers/'.$name.$i.'.'.$ext;
          }
          $dst_org = dirname(__FILE__).'/flyers/o/'.$name.$i.'.'.$ext;
          $dst_thumb = dirname(__FILE__).'/flyers/t/'.$name.$i.'.'.$ext;
          $evnt['image_name'] = $name.$i.'.'.$ext;
        }
 
        if (!in_array(strstr($evnt['image_link'],':',true), $this->haystack))
        {
  				if (!strpos($evnt['image_link'], "@") === false) 
            $evnt['image_link'] = 'mailto:'.$evnt['image_link'];
          elseif (strpos($evnt['image_link'], "/") === false) 
            $evnt['image_link'] = 'http://'.$evnt['image_link']; 
          else
            $this->errors .= '<div class="alert error">'.$this->l('Link for "'.$evnt['description'].'" must start with: ').implode(", ",$this->haystack).'</div>';      
        }
      
    		if ($error = ImageManager::validateUpload($src_path, $this->maxImageSize))
          $this->errors .= $error; 
    		elseif (!move_uploaded_file($src_tmp_name, $dst_org))
    			$this->errors .= $this->displayError($this->l('An error occurred during the image upload.'));
        elseif (!ImageManager::resize($dst_org, $dst_image, $this->dst_width, $this->dst_height))
          $this->errors .= $this->displayError($this->l('An error occurred during the image resize.'));
        else
        {
        $img_info  = getimagesize($dst_org);
        $long_img  = (($img_info[1] > $img_info[0]) ? 1 : 0); // height > width 
        }
      
        /* Crop to Fit Thumbnail Height */
        if ($this->crop_to_fit==1 && $long_img==1 && $this->errors=='')
        {
          if (!ImageManager::resize($dst_org, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
            $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image resize.'));        
          elseif (!ImageManager::cut($dst_thumb, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
            $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image crop.'));        
        } 
        else 
        { 
          /* Resize only, no cropping */
          if (!ImageManager::resize($dst_org, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
            $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image resize.'));        
        }

    		if (isset($this->errors) && $this->errors)
    			$this->errors .= $this->displayError($this->l('Error creating event.'));
    	} 
      else
        $this->errors .= $this->displayError($this->l("Event Flyer's image is missing."));
      
      if ($this->errors == '')    
        if (!$this->addEvent($evnt))
          $this->errors .= $this->displayError($this->l('Error creating event in database.'));

      $this->_html .= (isset($this->errors) && $this->errors != '') ? $this->errors : $this->displayConfirmation('Event added successfully.');
    } // End Add Submit
    
		// Event Flyers Delete Submit //
		if (isset($_POST['deleteEventSubmit']))
    {
			$evnt = Tools::getValue('eventDelete');
			if ($this->deleteEvent($evnt)){
				$this->_html .= "<div class='conf confirm'>".$this->l('Event succesfully deleted.')."</div>";
			} else
				$this->_html .= '<div class="alert error">'.$this->l('Problems deleting this event.').'</div>';
		} // End delete
	}

	public function hookHeader($params)
	{
	}

	function hookHome($params)
	{
		$this->context->controller->addCSS($this->path.'css/eventflyers.css');
		$this->context->controller->addCSS($this->path.'css/jquery.fancybox.css');
		$this->context->controller->addJS($this->path.'js/jquery.fancybox.js');

		global $cookie, $smarty;
		$smarty->assign(array(
			'event_class' => 'home',
      'crop_fit'  => (int)$this->crop_to_fit,
      'th_width'  => $this->dst_thumb_width,
      'th_height' => $this->dst_thumb_height,
			'events' => $this->getEvents('3'),
			'rel_path' => $this->path,
			'this_path' => $this->path.'flyers/'
		));
		return $this->display(__FILE__, 'eventflyers_display.tpl');
	}

	function hookLeftColumn($params)
	{                                                 /*** NOT TESTED ***/
		global $cookie, $smarty;
		$smarty->assign(array(
			'event_class' => 'left',
      'crop_fit'  => (int)$this->crop_to_fit,
      'th_width'  => $this->dst_thumb_width,
      'th_height' => $this->dst_thumb_height,
			'events'    => $this->getEvents('1'),
			'rel_path'  => $this->path,
			'this_path' => $this->path.'flyers/'
		));
		return $this->display(__FILE__, 'eventflyers_display.tpl');
	}
  
	function hookRightColumn($params)
	{                                                 /*** NOT TESTED ***/
		global $cookie, $smarty;
		$smarty->assign(array(
			'event_class' => 'right',
      'crop_fit'  => (int)$this->crop_to_fit,
      'th_width'  => $this->dst_thumb_width,
      'th_height' => $this->dst_thumb_height,
			'events'    => $this->getEvents('2'),
			'rel_path'  => $this->path,
			'this_path' => $this->path.'flyers/'
		));
		return $this->display(__FILE__, 'eventflyers_display.tpl');
	}

	public function hookDisplayBackOfficeHeader() 
  {
		$out = '<link href="'.$this->_path.'/css/eventflyers_admin.css'.'" rel="stylesheet" type="text/css" media="all" />'; 
		return $out;
  }

	/**
	*	getEvents()
	*	Returns the events from the database
	*	  block_id = 1 	=> left
	*   block_id = 2 	=> right
	*   block_id = 3 	=> home   
	*/
	public function getEvents($block_id = NULL)
	{
		$db = Db::getInstance();
		$result = $db->ExecuteS('
		  SELECT `id_eventflyer`, `description`, `image_name`, `image_link`, `block_id`, `order`, `active`, `open_blank` FROM `'._DB_PREFIX_.'eventflyers`'.(isset($block_id) ? ' WHERE `block_id` = '.$block_id : ' ').' ORDER BY `block_id`, `order`;');
		return $result;
	}

	/**
	*	addEvent($evnt)
	*	Add new event
	*/
	public function addEvent($evnt)
  {
		$db = Db::getInstance();
		// Insert new record
		$sql = 'INSERT INTO `'._DB_PREFIX_.'eventflyers` (`description`, `image_name`, `image_link`, `block_id`, `order`, `active`, `open_blank`) VALUES ("'.$evnt['description'].'", "'.$evnt['image_name'].'", "'.$evnt['image_link'].'", "'.$evnt['block_id'].'", "'.$evnt['order'].'", "'.$evnt['active'].'", "'.$evnt['blank'].'")';
		$result = $db->Execute($sql);
		if (!$result)
			return false;
		return true;
	}
  
	/**
	*	recreateImages()
	*	Recreate the main image and thumbnail from the original image.
	*/
	public function recreateImages()
  {
    $evnts = $this->getEvents();
    
    // PS_IMAGE_GENERATION_METHOD = 0;  Shrink until it fits within thumbnail
    // PS_IMAGE_GENERATION_METHOD = 1;  Variable height but fixed width
    // PS_IMAGE_GENERATION_METHOD = 2;  Variable width but fixed height
    Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);

		foreach ($evnts as $evnt)
    {
  		$dst_image = dirname(__FILE__).'/flyers/'.$evnt['image_name'];
  		$dst_org   = dirname(__FILE__).'/flyers/o/'.$evnt['image_name'];
  		$dst_thumb = dirname(__FILE__).'/flyers/t/'.$evnt['image_name'];

      if (!ImageManager::resize($dst_org, $dst_image, $this->dst_width, $this->dst_height))
        $this->errors .= $this->displayError($this->l('An error occurred during the image resize.'));

    	$img_info  = getimagesize($dst_org);
      $long_img  = (($img_info[1] > $img_info[0]) ? true : false); // height=$img_info[1] > width=$img_info[0] 
  
      /* Crop to Fit Thumbnail Height */
      if ($this->crop_to_fit && $long_img)
      {
        // if (!ImageManager::cut($dst_org, $dst_thumb, 640, 480, 'jpg', 0, 0))  // $dst_x, $dst_y
        if (!ImageManager::resize($dst_org, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
          $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image resize.'));        
        elseif (!ImageManager::cut($dst_thumb, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
          $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image crop.'));        
      } 
      else 
      { 
        // Resize only, no cropping //
        if (!ImageManager::resize($dst_org, $dst_thumb, $this->dst_thumb_width, $this->dst_thumb_height))
          $this->errors .= $this->displayError($this->l('An error occurred during the thumbnail image resize.'));        
      }
      
      if (isset($this->errors) && $this->errors)
//         return $this->errors; 
        return false; 
		}
		return true;
	}

	/**
	*	saveEvents($evnts)
	*	Save event data
	*/
	public function saveEvents($evnts)
  {
		foreach ($evnts as $evnt)
    {
			$db = Db::getInstance();
      $sql = 'UPDATE `'._DB_PREFIX_.'eventflyers` SET `description` = "'.$evnt['description'].'", `image_name` = "'.$evnt['image_name'].'", `image_link` = "'.$evnt['image_link'].'", `block_id` = "'.$evnt['block_id'].'", `order` = "'.$evnt['order'].'", `active` = "'.$evnt['active'].'", `open_blank` = "'.$evnt['blank'].'"  WHERE id_eventflyer = '.$evnt['id'];
			$result = $db->Execute($sql);
			if (!$result)
				return false;
		}
		return true;
	}
  
	/**
	*	deleteEvent($evnt)  id_eventflyer
	*	Delete an event
	*/
	public function deleteEvent($evnt)
	{
		$db = Db::getInstance();
		$result = $db->ExecuteS('SELECT `image_name` FROM `'._DB_PREFIX_.'eventflyers` WHERE `id_eventflyer` = "'.$evnt.'" LIMIT 1;') ;
		if (!isset($result['0']['image_name']))
      return false;

    $dst_evnt_dir = dirname(__FILE__).'/flyers/';
    
		@unlink($dst_evnt_dir.$result['0']['image_name']);
		@unlink($dst_evnt_dir.'o/'.$result['0']['image_name']);
		@unlink($dst_evnt_dir.'t/'.$result['0']['image_name']);

    $sql='DELETE FROM `'._DB_PREFIX_.'eventflyers` WHERE `id_eventflyer` = "'.$evnt.'"';
		$result = $db->Execute($sql);
		return $result;
	}
}
