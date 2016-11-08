{if $order_info.additional_services}
<table style="padding: 15px 0px 12px 0px;" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr align="left">
        <td style="white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{__("additional_services")}:</b></td>
    </tr>
    {foreach from=$order_info.additional_services item="service" key="service_id"}
        <tr>
            <td style="white-space: nowrap; font-size: 12px; font-family: Arial;">{$service.name} ({include file="common/price.tpl" value=$service.price})</td>
        </tr>
    {/foreach}
</table>
{/if}