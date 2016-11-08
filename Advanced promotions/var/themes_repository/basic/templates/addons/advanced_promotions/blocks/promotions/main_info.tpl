<h1 class="ty-promotion__header-name">{$promotion.name}</h1>
{if $block.properties.display_coundown == "Y" && $promotion.to_date > $smarty.now}
    {math equation="x - y" x=$promotion.to_date y=$smarty.now assign="to_date"}
    <div class="ty-promotion__days_left">{__("addons.advanced_promotions.days_left")}:</div>
    <div data-timestamp="{$to_date}" class="cm-clipclock flip-clock-small-wrapper"></div>
{/if}
{if $block.properties.display_time_period == "Y" && ($promotion.to_date > 0 || $promotion.from_date > 0)}
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
{/if}