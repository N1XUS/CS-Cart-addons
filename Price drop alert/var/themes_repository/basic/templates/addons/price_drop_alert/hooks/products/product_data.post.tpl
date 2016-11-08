{if !$hide_form && $show_add_to_cart == "Y"}

{$id = "price_drop_bscription_{$obj_prefix}{$product.product_id}"}

<div class="hidden" id="content_{$id}" title="{__("addons.price_drop_alert.notify_when_price_drops")}">
    {include file="addons/price_drop_alert/views/price_drop_alert/components/price_drop_alert_content.tpl" product=$product id=$id}
</div>

{/if}
