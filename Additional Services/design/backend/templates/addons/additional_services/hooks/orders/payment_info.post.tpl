{if $order_info.additional_services}
    <div class="control-group shift-top">
        <div class="control-label">
            {include file="common/subheader.tpl" title=__("additional_services")}
        </div>
    </div>
    {foreach from=$order_info.additional_services item="service"}
        <div class="control-group">
            <div class="control-label">{$service.name}</div>
            <div id="tygh_services_info" class="controls">
                {include file="common/price.tpl" value=$service.price}
            </div>
        </div>    
    {/foreach}
{/if}