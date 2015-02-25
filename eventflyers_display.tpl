{if not empty($events)}
<!-- MODULE Event Flyers {$event_class} Display -->
<script type="text/javascript"> 
	$(document).ready(function() {
		$('.eventflyers').fancybox({ 
			    padding : 0,                    // image border/frame  
			 openEffect : 'elastic',
			  openSpeed : 300,
			closeEffect : 'elastic',
			 closeSpeed : 300,
                wrapCSS : 'fancybox-custom',    // add more shadowing 
			 closeClick : false,                // clicking image closes gallery
              afterLoad : function() { 
                this.title = '<a href="'+ $(this.element).attr("data-fancybox-link") +'" target="'+ $(this.element).attr("target")+'" rel="nofollow"><span>{l s="Click here for more info!" mod="eventflyers"} </span></a>'; 
              },
    			helpers : {             
      				title : {
        			  type : 'outside'
				    },
//				  overlay : {                     // transparent white overlay
//					     css : { 
//					        'background' : 'rgba(238,238,238,0.85)'
//				  }  }
			    }
        });
	});
</script>
<div class="block">
<h4 class="title_block">{l s='Come visit these great shows...' mod='eventflyers'}</h4>
  <div id='eventflyers-{$event_class}' class="{$event_class}">
  {foreach from=$events item=event}
  	{if $event.active}
          <p>
            <a class="eventflyers" rel="nofollow" 
                href="{$this_path}{$event.image_name}"  
                target="{if $event.open_blank==0}_self{else}_blank{/if}"
                data-fancybox-group="gallery" 
                data-fancybox-link="{$event.image_link}{if $event.image_link|strstr:'mailto:'}?Subject={$event.description}{/if}"
                title="{$event.description}">
              <img src="{$this_path}t/{$event.image_name}" alt="{$event.description}" 
              {if $crop_fit==1} style="height:{$th_height}px;" {/if} />
            </a>
          </p>
  	{/if}
  {/foreach}
  </div>
</div>
<!-- /MODULE Event Flyers {$event_class} Display -->
{/if}