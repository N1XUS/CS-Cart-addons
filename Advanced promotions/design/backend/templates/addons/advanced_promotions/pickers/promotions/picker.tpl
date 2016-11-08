{assign var="data_id" value=$data_id|default:"promotions_id"}
{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}
{assign var="start_pos" value=$start_pos|default:0}

{script src="js/tygh/picker.js"}

{if ($item_ids) && !$item_ids|is_array}
    {assign var="item_ids" value=","|explode:$item_ids}
{/if}

<div class="clearfix">
    {if !$multiple}
        <div class="choose-input">
    {/if}

    {if $view_mode != "list"}
        {capture name="add_buttons"}
            {if $multiple == true}
                {assign var="display" value="checkbox"}
            {else}
                {assign var="display" value="radio"}
            {/if}
    
            {if !$extra_url}
                {assign var="extra_url" value="&get_tree=multi_level"}
            {/if}
    
            {if $extra_var}
                {assign var="extra_var" value=$extra_var|escape:url}
            {/if}
            <div class="pull-right">
                {if !$no_container}<div class="{if !$multiple}choose-icon input-append{else}buttons-container{/if}">{/if}
                    {if $multiple}
                        {assign var="lang_add_promotions" value=__("add_promotions")}
                        {assign var="_but_text" value=$but_text|default:"<i class='icon-plus'></i> `$lang_add_promotions`"}
                        {assign var="_but_role" value="add"}
                    {else}
                        {assign var="_but_text" value="<i class='icon-plus'></i>"}
                        {assign var="_but_role" value="icon"}
                    {/if}
        
                    {$_root = $default_name|escape:'url'}
                    {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="promotions.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&root=`$_root`&except_id=`$except_id`&data_id=`$data_id``$extra_url`&company_id=`$company_id`"|fn_url but_text=$_but_text but_role=$_but_role but_target_id="content_`$data_id`" but_meta="cm-dialog-opener add-on btn"}
        
                {if !$no_container}</div>{/if}
            </div>
    
            <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_promotions")}">
            </div>
        {/capture}
        {if !$prepend}
            {$smarty.capture.add_buttons nofilter}
            {capture name="add_buttons"}{/capture}
        {/if}
    {/if}
{if $view_mode != "button"}
    {if $multiple}
        <table width="100%" class="table table-middle">
        <thead>
        <tr>
            {if $positions}<th>{__("position_short")}</th>{/if}
            <th width="100%">{__("name")}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
    {else}
        <div id="{$data_id}" class="{if $multiple && !$item_ids}hidden{elseif !$multiple}cm-display-radio pull-left{/if}">
    {/if}

    {if $multiple}
        <tr class="hidden">
            <td colspan="{if $positions}3{else}2{/if}">
    {/if}
    <input id="a{$data_id}_ids" type="hidden" class="cm-picker-value" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />
    {if $multiple}
            </td>
        </tr>
    {/if}

    {if $item_ids}
    {foreach name="items" from=$item_ids item="p_id"}
        <div class="input-append">
            <div class="pull-left">
                {if $multiple}
                    {$extra_class = "input-large"}
                {else}
                    {$extra_class = ""}
                {/if}

                {include file="addons/advanced_promotions/pickers/promotions/js.tpl" promotion_id=$p_id holder=$data_id input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button hide_input=true first_item=$smarty.foreach.items.first position_field=$positions position=$smarty.foreach.items.iteration+$start_pos}
            </div>
            {$smarty.capture.add_buttons nofilter}
        </div>
    {/foreach}
    {elseif !$multiple}
        <div class="input-append">
            <div class="pull-left">
                {include file="addons/advanced_promotions/pickers/promotions/js.tpl" promotion_id="" holder=$data_id input_name=$input_name hide_link=$hide_link hide_delete_button=$hide_delete_button}
            </div>
            {$smarty.capture.add_buttons nofilter}
        </div>
    {/if}

    {if $multiple}
        {include file="addons/advanced_promotions/pickers/promotions/js.tpl" promotion_id="`$ldelim`promotion_id`$rdelim`" holder=$data_id input_name=$input_name clone=true hide_link=$hide_link hide_delete_button=$hide_delete_button hide_input=true position_field=$positions position="0"}
    {/if}

    {if $multiple}
    </tbody>
    <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
    <tr class="no-items">
        <td colspan="{if $positions}3{else}2{/if}"><p>{$no_item_text|default:__("no_items") nofilter}</p></td>
    </tr>
    </tbody>
    </table>
    {else}
    </div>
    {/if}
{/if}
    {if !$multiple}
        </div>
    {/if}
</div>
