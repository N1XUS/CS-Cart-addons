{hook name="products:product_main_promotion"}
    {if $product.promotions_list}
        {assign var="promotion" value=$product.promotions_list|array_shift}
        {include file="addons/advanced_promotions/views/promotions/components/product_promotion.tpl" promotion=$promotion}
    {/if}
{/hook}