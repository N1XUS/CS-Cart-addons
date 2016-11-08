{if $order_info.additional_services}
<tr>
    <td style="text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{__("additional_services")}:</b>&nbsp;</td>
    <td style="text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;">{include file="common/price.tpl" value=$order_info.additional_services_total}</td>
</tr>
{/if}