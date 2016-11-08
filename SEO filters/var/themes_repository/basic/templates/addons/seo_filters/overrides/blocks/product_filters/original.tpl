{** block-description:original **}

{script src="js/tygh/product_filters.js"}

{if $block.type == "product_filters"}
    {$ajax_div_ids = "product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,selected_filters_*,category_name_*,category_description_*"}
    {$curl = $config.current_url}
{else}
    {$curl = "products.search"|fn_url}
    {$ajax_div_ids = ""}
{/if}

{$filter_base_url = $curl|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"features_hash":"subcats":"page":"total"}

<div class="cm-product-filters" data-ca-target-id="{$ajax_div_ids}" data-ca-base-url="{$filter_base_url|fn_url}" id="product_filters_{$block.block_id}">
<div class="ty-product-filters__wrapper">
{if $items}

{foreach from=$items item="filter" name="filters"}
    {hook name="blocks:product_filters_variants"}
    {assign var="filter_uid" value="`$block.block_id`_`$filter.filter_id`"}
    {assign var="cookie_name_show_filter" value="content_`$filter_uid`"}
    {if $filter.display == "N"}
        {* default behaviour of cm-combination *}
        {assign var="collapse" value=true}
        {if $smarty.cookies.$cookie_name_show_filter}
            {assign var="collapse" value=false}
        {/if}
    {else}
        {* reverse behaviour of cm-combination *}
        {assign var="collapse" value=false}
        {if $smarty.cookies.$cookie_name_show_filter}
            {assign var="collapse" value=true}
        {/if}
    {/if}

    {$reset_url = ""}
    {if $filter.selected_variants || $filter.selected_range}
        {$reset_url = $filter_base_url}
        {$fh = $smarty.request.features_hash|fn_delete_filter_from_hash:$filter.filter_id}
        {if $fh}
            {$reset_url = $filter_base_url|fn_link_attach:"features_hash=$fh"}
        {/if}
    {/if}

    <div class="ty-product-filters__block">
        <div id="sw_content_{$filter_uid}" class="ty-product-filters__switch cm-combination-filter_{$filter_uid}{if !$collapse} open{/if} cm-save-state {if $filter.display == "Y"}cm-ss-reverse{/if}">
            <span class="ty-product-filters__title">{$filter.filter}{if $filter.selected_variants} ({$filter.selected_variants|sizeof}){/if}{if $reset_url}<a class="cm-ajax cm-ajax-full-render cm-history" href="{$reset_url|fn_url}" data-ca-event="ce.filtersinit" data-ca-target-id="{$ajax_div_ids}" data-ca-scroll=".ty-mainbox-title"><i class="ty-icon-cancel-circle"></i></a>{/if}</span>
            <i class="ty-product-filters__switch-down ty-icon-down-open"></i>
            <i class="ty-product-filters__switch-right ty-icon-up-open"></i>
        </div>

        {if $filter.slider}
            {if $filter.feature_type == "ProductFeatures::DATE"|enum}
                {include file="blocks/product_filters/components/product_filter_datepicker.tpl" filter_uid=$filter_uid filter=$filter}
            {else}
                {include file="blocks/product_filters/components/product_filter_slider.tpl" filter_uid=$filter_uid filter=$filter}
            {/if}
        {else}
            {include file="blocks/product_filters/components/product_filter_variants.tpl" filter_uid=$filter_uid filter=$filter collapse=$collapse}
        {/if}
    </div>
    {/hook}
{/foreach}

{if $ajax_div_ids}
<div class="ty-product-filters__tools clearfix">

    <a href="{$filter_base_url|fn_url}" rel="nofollow" class="ty-product-filters__reset-button cm-ajax cm-ajax-full-render cm-history" data-ca-event="ce.filtersinit" data-ca-scroll=".ty-mainbox-title" data-ca-target-id="{$ajax_div_ids}"><i class="ty-product-filters__reset-icon ty-icon-cw"></i> {__("reset")}</a>

</div>
{/if}

{/if}
</div>
<!--product_filters_{$block.block_id}--></div>
