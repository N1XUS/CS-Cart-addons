<div id="additional_services_list">
{if $additional_services}
    <p><strong>{__("available_services_header")}</strong>:</p>
    {if $services_display_type == "radio"}
        <p class="ty-shipping-options__additional-services">
            <input type="radio" class="ty-valign" id="sh_service_0" name="service_ids[]" value="" onclick="fn_calculate_total_services_cost();" {if !$cart.additional_services} checked="checked"{/if} />
            <label for="sh_service_0" class="ty-valign">{__("no_thanks")}</label>
        </p>        
    {/if}
    {foreach from=$additional_services item="service"}
        <p class="ty-shipping-options__additional-services">
            <input type="{$services_display_type}" class="ty-valign" id="sh_service_{$service.service_id}" name="service_ids[]" value="{$service.service_id}" onclick="fn_calculate_total_services_cost();" {if $cart.additional_services[$service.service_id]} checked="checked"{/if} />
            <label for="sh_service_{$service.service_id}" class="ty-valign">{$service.name} ({if $service.price == 0}{__("free")}{else}{include file="common/price.tpl" value=$service.price}{/if})</label>
            {if $service.description}
                <div class="ty-shipping-option__description">
                    {$service.description nofilter}
                </div>
            {/if}
        </p>
    {/foreach}
    <hr />
{/if}
<!--additional_services_list--></div>