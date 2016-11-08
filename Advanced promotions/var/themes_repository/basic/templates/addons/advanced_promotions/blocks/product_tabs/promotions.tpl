{** block-description:promotions_block **}
{if $product.promotions_list}
    {foreach from=$product.promotions_list item="promotion"}
        {include file="addons/advanced_promotions/views/promotions/components/product_promotion.tpl" promotion=$promotion}
    {/foreach}
{/if}