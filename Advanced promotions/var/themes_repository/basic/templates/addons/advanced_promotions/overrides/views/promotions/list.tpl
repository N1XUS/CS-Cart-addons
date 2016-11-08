<div class="ty-wysiwyg-content ty-promotions-list">
{if $promotions}
    {assign var="columns" value="3"}
    {split data=$promotions size=$columns assign="splitted_promotions"}
    {foreach from=$splitted_promotions item="spromotions"}
        {foreach from=$spromotions item="promotion"}
            {if $promotion.promotion_id}
                <div class="ty-column{$columns}">
                    <div class="ty-promotions-list__wrapper">
                        <div class="ty-center ty-promotions-list__img-wrapper">
                            <a href="{"promotions.view?promotion_id=`$promotion.promotion_id`"|fn_url}">
                                {include file="common/image.tpl"
                                    show_detailed_link=false
                                    images=$promotion.list_pair
                                    no_ids=true
                                    image_width=300
                                    image_height=250
                                    image_id="promotion_image"
                                    class="ty-promotion-img"
                                }
                            </a>
                        </div>
                        <div class="ty-promotions-list__content-wrapper clearfix">
                            {if $promotion.to_date > 0}
                                <div class="ty-promotions-list__days-left">
                                    {capture name="days_left_value"}<span data-timestamp="{$promotion.to_date}" class="days-left-value cm-moment-calendar-format"></span>{/capture}
                                    <span class="days-left-label">{__("addons.advanced_promotions.promotion_ends", ["[DAYS_LEFT_VALUE]" => $smarty.capture.days_left_value]) nofilter}</span>
                                </div>
                            {/if}
                            <div class="ty-promotions-list__main-info">
                                <div class="inner-wrapper">
                                    {if $promotion.to_date > 0 || $promotion.from_date > 0}
                                        <div class="ty-promotions-list__time-period">
                                            {if $promotion.from_date > 0}
                                                {__("addons.advanced_promotions.from_date")} <span data-timestamp="{$promotion.from_date}" data-format="D MMMM" class="cm-moment-format"></span>
                                            {/if}
                                            {if $promotion.from_date > 0}
                                                {__("addons.advanced_promotions.to_date")} <span data-timestamp="{$promotion.to_date}" data-format="D MMMM" class="cm-moment-format"></span>
                                            {/if}
                                        </div>
                                    {/if}
                                    <div class="ty-promotions-list__summary">
                                        <a href="{"promotions.view?promotion_id=`$promotion.promotion_id`"|fn_url}">{$promotion.name}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        {/foreach}
    {/foreach}
{else}
    <p>{__("text_no_active_promotions")}</p>
{/if}
{capture name="mainbox_title"}{__("active_promotions")}{/capture}