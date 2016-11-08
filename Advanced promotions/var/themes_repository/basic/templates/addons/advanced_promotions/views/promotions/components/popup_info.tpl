<h1 class="ty-promotion__header-name">{$promotion.name}</h1>
<div class="ty-promotion__img-wrap">
    {include file="common/image.tpl"
        show_detailed_link=false
        images=$promotion.main_pair
        no_ids=true
        image_width=300
        image_id="promotion_image"
        class="ty-promotion-img"
    }
</div>
<div class="ty-promotion__additional-info">
    <div class="ty-promotion__description">\
        {if $promotion.short_description}
            {$promotion.short_description nofilter}
        {else}
            {$promotion.detailed_description|strip_tags|truncate:160 nofilter}
        {/if}
    </div>
    <div class="ty-promotion__available-text">
        {__("addons.advanced_promotions.promotion_available")}
        <span class="ty-lowercase">
            {if $promotion.from_date > 0}
                {__("addons.advanced_promotions.from_date")} <span data-timestamp="{$promotion.from_date}" data-format="D MMMM" class="cm-moment-format"></span>
            {/if}
            {if $promotion.from_date > 0}
                {__("addons.advanced_promotions.to_date")} <span data-timestamp="{$promotion.to_date}" data-format="D MMMM" class="cm-moment-format"></span>
            {/if}
        </span>
    </div>
</div>
<div class="ty-promotion__products">
{if $products}
    <div class="ty-promotion__products-header clearfix">
        <h2>{__("addons.advanced_promotions.promotion_items_are")}:</h2>
        <a href="{"promotions.view?promotion_id=`$promotion.promotion_id`#promotion_products"|fn_url}">{__("addons.advanced_promotions.see_all_products")} →</a>
    </div>
    {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
    {include file="blocks/list_templates/grid_list.tpl"
    show_trunc_name=true
    show_old_price=true
    show_price=true
    show_rating=true
    show_clean_price=true
    show_list_discount=true
    show_add_to_cart=$show_add_to_cart|default:false
    but_role="action"
    show_discount_label=true
    columns=$product_columns
    no_pagination=true
    no_sorting=true
    products=$products}
{/if}
<div class="ty-promotion__to-promotion-page">
    <a href="{"promotions.view?promotion_id=`$promotion.promotion_id`"|fn_url}">{__("addons.advanced_promotions.view_promotion")} →</a>
</div>
</div>