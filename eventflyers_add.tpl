{*
<script type="text/javascript" src="../js/jquery/jquery.tablednd_0_5.js"></script>
<script type="text/javascript" src="../js/admin-dnd.js"></script>
*}
{literal}
<script language="javascript">
	function deleteEvent(evnt, question) {
		if (confirm(question)){
			document.deleteEventForm.eventDelete.value = evnt;
			document.deleteEventForm.deleteEventSubmit.click();
		} else {
			return false;
		}
	}
	function addEvent() {
		document.getElementById('addEvent').style.display = '';
		document.getElementById('addEvent').scrollIntoView();
		document.getElementById('description').focus();
	}
</script>
{/literal}
<br/><br/>
<!--Add new event -->
<div style="display:none;" id="addEvent" name="addEvent">
<form action="{$smarty.server.REQUEST_URI}" method="post" enctype="multipart/form-data">
<fieldset>
    <legend><img src="../img/admin/add.gif" />{l s='Add new event' mod='eventflyers'}</legend>
    <span>{l s='Provide the following information to create a new event' mod='eventflyers'}</span><br /><br />
    <label for="event_description">{l s='Event description' mod='eventflyers'}:</label>
    <div class="margin-form">
        <input id="description" type="text" name="event_description" size="80" />
    </div>
    <br />
	<label for="event_link">{l s='Event link' mod='eventflyers'}:</label>
    <div class="margin-form">
        <input type="text" name="event_link" size="80" />
        <p>{l s='Provide the URL (http optional) or Email Address for the event link.' mod='eventflyers'}</p>
    </div>
    <br />
    {*
	<label for="event_order">{l s='Event order' mod='eventflyers'}:</label>
    <div class="margin-form">
        <input type="text" name="event_order" size="4" />
        <p>{l s='The order within the block' mod='eventflyers'}</p>
    </div>
    <br />
    *}
    <input type="hidden" name="event_order" size="4" value="0" />
    
	<label for="event_block_id">{l s='Block space' mod='eventflyers'}:</label>
    <div class="margin-form">
    	<input type="radio" id="event_block_id_left"  value="1" name="event_block_id" />
        <label for="event_block_id_left" class="t">{l s='Left' mod='eventflyers'}</label>
        <br />
    	<input type="radio" id="event_block_id_right" value="2" name="event_block_id" />
        <label for="event_block_id_right" class="t">{l s='Right' mod='eventflyers'}</label>
        <br />
    	<input type="radio" id="event_block_id_home" value="3" name="event_block_id" checked="checked" />
        <label for="event_block_id_home" class="t">{l s='Home' mod='eventflyers'}</label>
    </div>
    <br />
	<label for="event_image">{l s='Event flyer image' mod='eventflyers'}:</label>
    <div class="margin-form">
        <input type="file" name="event_image" />
        <p>{l s='Select an image from your computer' mod='eventflyers'}</p>
    </div>
    <br />
	<label for="event_blank">{l s='Open in new window?' mod='eventflyers'}:</label>
    <div class="margin-form">
        <input type="checkbox" name="event_blank" checked="checked" />
        <p>{l s='Check it if you want the link opens in a new window' mod='eventflyers'}</p>
    </div>
    <br />
	<label for="event_active">{l s='Active?' mod='eventflyers'}:</label>
    <div class="margin-form">
        <input type="checkbox" name="event_active" checked="checked" />
        <p>{l s='Check it if you want to enable the new event' mod='eventflyers'}</p>
    </div>
    <br />
	<p class="center"><input class="button" name="addEventSubmit" value="{l s='Add event' mod='eventflyers'}" type="submit" /></p>
</fieldset>
</form>

<!--Delete event -->
<form action="{$smarty.server.REQUEST_URI}" method="post" class="hidden" name="deleteEventForm">
	<fieldset>
	<input type="hidden" value="" name="eventDelete" />
	<input class="hidden" name="deleteEventSubmit" value="{l s='Delete event' mod='eventflyers'}" type="submit" />
	</fieldset>
</form>
<br/><br/>
</div>