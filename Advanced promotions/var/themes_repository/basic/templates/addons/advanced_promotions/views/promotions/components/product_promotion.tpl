<div class="ty-product-promotion clearfix">
    <div class="ty-product-promotion__image-wrap">
        <a href="{"promotions.view?promotion_id=`$promotion.promotion_id`"|fn_url}">
            {include file="common/image.tpl"
                show_detailed_link=false
                images=$promotion.list_pair
                no_ids=true
                image_width=100
                image_height=100
                image_id="promotion_image"
                class="ty-promotion-img"
            }
        </a>
    </div>
    <div class="ty-product-promotion__main-info">
        <div class="ty-product-promotion__name">
            <a href="{"promotions.view?promotion_id=`$promotion.promotion_id`"|fn_url}">{$promotion.name}</a>
        </div>
        {if $promotion.to_date > $smarty.now}
            {math equation="x - y" x=$promotion.to_date y=$smarty.now assign="to_date"}
            <div class="ty-promotion__days-left">{__("addons.advanced_promotions.days_left")}:</div>
            <div data-timestamp="{$to_date}" class="cm-clipclock flip-clock-small-wrapper"></div>
            <div class="ty-promotion__more-link">
                {$id = "promotion_info_{$promotion.promotion_id}"}
                <a id="opener_{$id}" href="{"promotions.quick_view?promotion_id=`$promotion.promotion_id`"|fn_url}" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__text" data-ca-target-id="content_{$id}" rel="nofollow">{__("addons.advanced_promotions.more_info")}</a>
                {if $promotion.name|strlen > 50}
                    {assign var="promotion_name" value=$promotion.name|truncate:50:"...":true}
                {else}
                    {assign var="promotion_name" value=$promotion.name}
                {/if}
                <div class="hidden" id="content_{$id}" title="{__("addons.advanced_promotions.promotion_info")}: {$promotion_name}"></div>
            </div>
        {/if}
    </div>
</div>
