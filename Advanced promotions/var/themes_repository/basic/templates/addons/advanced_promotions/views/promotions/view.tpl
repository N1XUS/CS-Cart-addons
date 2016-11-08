{if $products}
    {assign var="layouts" value=""|fn_get_products_views:false:0}
    {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
    {if $layouts.$selected_layout.template}
        {include file="`$layouts.$selected_layout.template`" columns=$product_columns}
    {/if}
    {capture name="mainbox_title"}{__("addons.advanced_promotions.promotion_items_are")}{/capture}
{/if}