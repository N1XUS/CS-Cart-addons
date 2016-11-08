{if $cart.additional_services}
<tr>
    <td class="ty-checkout-summary__item ty-checkout-summary__taxes">{__("additional_services")}</td>
    <td class="ty-checkout-summary__item"></td>
</tr>
{foreach from=$cart.additional_services item="service"}
<tr>
    <td class="ty-checkout-summary__item" data-ct-checkout-summary="tax-name {$service.name}">
        <div class="ty-checkout-summary__taxes-name">{$service.name}</div>
    </td>
    <td class="ty-checkout-summary__item ty-right" data-ct-checkout-summary="taxes">
        <span class="ty-checkout-summary__taxes-amount">{include file="common/price.tpl" value=$service.price}</span>
    </td>
</tr>
{/foreach}
{/if}