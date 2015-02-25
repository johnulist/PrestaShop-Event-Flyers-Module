{*
{literal}
<script type="text/javascript">
	var token = '{$token}';
	var come_from = 'AdminModulesPositions';
</script>
<script type="text/javascript" src="../js/jquery/jquery.tablednd_0_5.js"></script>
<script type="text/javascript" src="../js/admin-dnd.js"></script>
{/literal}
*}
<div>
<form action="{$smarty.server.REQUEST_URI}" method="post" enctype="multipart/form-data">
	<fieldset>
	<legend><img src="{$path}logo.gif" />{$title} {l s='Events' mod='eventflyers'}</legend>
    <table cellspacing="0" cellpadding="0" class="table tableDnD" style="width:80%;" id="eventTable-{$block}">
            <thead>
                <tr class="nodrag nodrop">
                    <th>{l s='Id' mod='eventflyers'}</th>
                    <th>{l s='Image' mod='eventflyers'}</th>
                    <th>{l s='Description' mod='eventflyers'}</th>
                    <th>{l s='Link or Email' mod='eventflyers'}<br><span>{l s='(http optional)' mod='eventflyers'}</span></th>
                    <th align="center">{l s='Order' mod='eventflyers'}</th>
                    <th align="center">{l s='New<br>Window' mod='eventflyers'}</th>
                    <th align="center">{l s='Active' mod='eventflyers'}</th>
                    <th align="center">{l s='Actions' mod='eventflyers'}</th>
                </tr>
            </thead>    
            <tbody>
			{if !$events}
                <tr>
                    <td colspan="8" align="left" class="">
						<label for="eventBlock_empty" class="t">{l s='There are no events for' mod='eventflyers'} <b>{$title}</b></label>
                    </td>
                </tr>
			{/if}
            {assign var='oldBlock' value=0}
            {assign var='irow' value=0}
            {foreach from=$events key='index' item='event' name='event'}
                {* 
                {if $event.block_id != $oldBlock}
                    <tr>
                        <th colspan="8" align="left" class="alt_row">
                            <label for="eventBlock_{$event.id_eventflyer}" class="t">{l s='Events assigned to the' mod='eventflyers'} <b>{if $event.block_id == 1}{l s='left' mod='eventflyers'}{else}{l s='right' mod='eventflyers'}{/if}</b> {l s='column' mod='eventflyers'}</label>
                        </th>
                    </tr>
                {/if} 
                *}
                {assign var='oldBlock' value=$event.block_id}
                <tr name="event_{$event.id_eventflyer}" id="{$event.id_eventflyer}" {if $irow++ % 2}class="alt_row"{/if}>
                    <td class="positions" width="15">{$event.id_eventflyer}
                        <input type="checkbox" style="display:none" value="{$event.id_eventflyer}" name="eventflyersId[]" checked="checked" />
                    </td>
                    <td>
                    <div>
                        <a href="#popup_image" onClick="getElementById('popImg').src='{$path}flyers/{$event.image_name}'"> 
                            <img style="height:100px;" src="{$path}flyers/{$event.image_name}" name="image_{$event.id_eventflyer}" />
                        </a>
                    </div>
                        <br>
                        <input type="file"   name="event_image_{$event.id_eventflyer}" />
                        <input type="hidden" name="image_name_{$event.id_eventflyer}" value="{$event.image_name}" />
                    </td>
                    <td><input type="text" value="{$event.description}" name="desc_{$event.id_eventflyer}" size="30" /></td>
                    <td>
                        <input type="text" name="link_{$event.id_eventflyer}" value="{$event.image_link}" size="35" />
                    {* <textarea rows="3" name="link_{$event.id_eventflyer}" cols="35">{$event.image_link}</textarea> *}
                    </td>
                    {*                     
                    <!--Drag & drop images -->
                    <td {if $leftEvents >= 2}class="dragHandle"{/if} id="td_{$event.id_eventflyer}">
                       <a {if $event.order == 1}style="display: none;"{/if} href="{$currentIndex}&id_event={$event.id_eventflyer}&direction=0&changePosition={$rand}#{$event.block_id}"><img src="../img/admin/up.gif" alt="{l s='Up' mod='eventflyers'}" title="{l s='Up' mod='eventflyers'}" /></a>
                       <a {if $event.order == count($events)}style="display: none;"{/if} href="{$currentIndex}&id_event={$event.id_eventflyer}&direction=1&changePosition={$rand}#{$event.block_id}"><img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
                    *}
                   {* NOTE: $event@iteration - Re-numbering Does Not get updated in database unless submit is clicked *}                   
                    <td class="nodrag nodrop">
                        <input type="text" value="{$event@iteration}" name="order_{$event.id_eventflyer}" align="right" size="2" />
                    </td>
                    <td align="center"><input type="checkbox" class="noborder" value="{$event.id_eventflyer}" name="blank_{$event.id_eventflyer}" {if (intval($event.open_blank))} checked="checked"{/if} /></td>
                    <td align="center"><input type="checkbox" class="noborder" value="{$event.id_eventflyer}" name="active_{$event.id_eventflyer}" {if (intval($event.active))} checked="checked"{/if} /><input type="hidden" name="block_{$event.id_eventflyer}" value="{$event.block_id}" /></td>
                    <td align="center"><img src="../img/admin/delete.gif" alt="{l s='Delete Event' mod='eventflyers'}" title="{l s='Delete Event' mod='eventflyers'}" onclick="deleteEvent({$event.id_eventflyer}, '{l s='Do you want to delete the following event?' mod='eventflyers'}');" /></td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    <br />
	<p class="center" style="width:80%;"><input class="button" name="updateEventSubmit" value="{l s='Update events' mod='eventflyers'}" type="submit" /></p>
	</fieldset>
</form>
</div>
