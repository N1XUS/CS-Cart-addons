{if $shipping.module == "nova_poshta" && $cart.chosen_shipping.$group_key == $shipping.shipping_id}
    {if $warehouses && $shipping.service_params.shipping_type == "WarehouseWarehouse"}
        {if $selected_warehouse == false}
            {assign var="selected_warehouse_info" value=$warehouses|reset}
        {else}
            {assign var="selected_warehouse_info" value=$selected_warehouses_info.$group_key}
        {/if}
        {if $smarty.const.DESCR_SL == "uk"}
            {assign var="descr_key" value="Description"}
        {else}
            {assign var="descr_key" value="DescriptionRu"}
        {/if}
        <select class="cm-warehouse-select" id="warehouse_select_{$group_key}" name="warehouse[{$group_key}]">
        {foreach from=$warehouses item="warehouse" name="wh"}
            <option data-phone="{$warehouse.Phone}" data-lat="{$warehouse.Latitude}" data-lng="{$warehouse.Longitude}" value="{$warehouse.Ref}"{if $selected_warehouse.$group_key == $warehouse.Ref} selected="selected"{/if}>{$warehouse.$descr_key}</option>
        {/foreach}
        </select>
        <div style="height: 300px; margin-top: 20px; margin-bottom: 20px;" data-group-key="{$group_key}" data-id="{$selected_warehouse_info.Ref}" data-lat="{$selected_warehouse_info.Latitude}" data-lng="{$selected_warehouse_info.Longitude}" class="warehouse-map" id="warehouse_map_{$group_key}"></div>
        <div class="warehouse-info hidden" id="warehouse_info_{$group_key}_{$selected_warehouse_info.Ref}">
            <p><strong>{__("phone")}:</strong> {$selected_warehouse_info.Phone}</p>
            <p>{__("reception_time")}:</p>
            <ul>
            {foreach from=$selected_warehouse_info.reception_time item="days" key="hours"}
                <li><strong>{__("weekday_`$smarty.const[$days.start]`")}{if $days.end}&nbsp;-&nbsp;{__("weekday_`$smarty.const[$days.end]`")}{/if}:</strong>&nbsp;{$hours}</li>
            {/foreach}
            </ul>
        </div>
        <script type="text/javascript"  class="cm-ajax-force">
        //<![CDATA[
                Tygh.$("#warehouse_select_{$group_key}").chosen({$ldelim}
                    no_results_text: '{__("no_items")|escape:"javascript"}'
                {$rdelim});
        //]]>
        </script>
    {/if}
{/if}