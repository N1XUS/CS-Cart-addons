{if $additional_services}
    <div class="control-group shift-top">
        <div class="control-label">
            <h4 class="subheader">{__("additional_services")}</h4>
        </div>
    </div>
    {foreach from=$additional_services item="service"}
        <div class="control-group">
            <label><input type="checkbox" name="service_ids[]" value="{$service.service_id}" />&nbsp;{$service.name} ({include file="common/price.tpl" value=$service.price})</label>
        </div>    
    {/foreach}
{/if}