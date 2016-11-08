<ul class="ty-product-filters {if $collapse}hidden{/if}" id="content_{$filter_uid}">

    {if $filter.display_count && $filter.variants|count > $filter.display_count}
    <li>
        {script src="js/tygh/filter_table.js"}

        <div class="ty-product-filters__search">
        <input type="text" placeholder="{__("search")}" class="cm-autocomplete-off ty-input-text-medium" name="q" id="elm_search_{$filter_uid}" value="" />
        <i class="ty-product-filters__search-icon ty-icon-cancel-circle hidden" id="elm_search_clear_{$filter_uid}" title="{__("clear")}"></i>
        </div>
    </li>
    {/if}


    {* Selected variants *}
    {foreach from=$filter.selected_variants key="variant_id" item="variant"}
    {assign var="url" value=fn_seo_filters_dettach_filter_link($variant.variant_id, $filter.filter_id)}
        <li class="cm-product-filters-checkbox-container ty-product-filters__group">
            <label>
                <a {if preg_match("/\?features_hash/", fn_url($url))} rel="nofollow" {/if}data-ca-target-id={$ajax_div_ids} class="cm-ajax cm-ajax-full-render cm-ajax-cache cm-history" href="{$url|fn_url}">
                    <input class="cm-product-filters-checkbox" type="checkbox" name="product_filters[{$filter.filter_id}]" data-ca-filter-id="{$filter.filter_id}" value="{$variant.variant_id}" id="elm_checkbox_{$filter_uid}_{$variant.variant_id}" checked="checked">
                    {if $filter.feature_type && $filter.is_image == "Y"}
                        {include file="common/image.tpl" images=$variant.image_pair image_width=$addons.filter_by_images.image_width image_height=$addons.filter_by_images.image_width}<span class="hidden">{$variant.variant|fn_text_placeholders}</span>
                    {else}  
                        {$filter.prefix}{$variant.variant|fn_text_placeholders}{$filter.suffix}
                    {/if}
                </a>
            </label>
        </li>
    {/foreach}

    {if $filter.variants}
        <li class="ty-product-filters__item-more">
            <ul id="ranges_{$filter_uid}" {if $filter.display_count}style="max-height: {$filter.display_count * 2}em;"{/if} class="ty-product-filters__variants cm-filter-table" data-ca-input-id="elm_search_{$filter_uid}" data-ca-clear-id="elm_search_clear_{$filter_uid}" data-ca-empty-id="elm_search_empty_{$filter_uid}">

                {foreach from=$filter.variants item="variant"}
                {assign var="url" value=fn_seo_filters_attach_filter_link($variant.variant_id, $filter.filter_id)}
                <li class="cm-product-filters-checkbox-container ty-product-filters__group">
                    <label {if $variant.disabled}class="disabled"{/if}>
                        {if !$variant.disabled}<a {if preg_match("/\?features_hash/", fn_url($url))} rel="nofollow" {/if}data-ca-target-id={$ajax_div_ids} class="cm-ajax cm-ajax-full-render cm-ajax-cache cm-history" href="{$url|fn_url}">{/if}
                            <input class="cm-product-filters-checkbox" type="checkbox" name="product_filters[{$filter.filter_id}]" data-ca-filter-id="{$filter.filter_id}" value="{$variant.variant_id}" id="elm_checkbox_{$filter_uid}_{$variant.variant_id}" {if $variant.disabled}disabled="disabled"{/if}>
                            {if $filter.feature_type && $filter.is_image == "Y"}
                                {include file="common/image.tpl" images=$variant.image_pair image_width=$addons.filter_by_images.image_width image_height=$addons.filter_by_images.image_width}<span class="hidden">{$variant.variant|fn_text_placeholders}</span>
                            {else}   
                                {$filter.prefix}{$variant.variant|fn_text_placeholders}{$filter.suffix}
                            {/if}
                        {if !$variant.disabled}</a>{/if}
                    </label>
                </li>
                {/foreach}
            </ul>
            <p id="elm_search_empty_{$filter_uid}" class="ty-product-filters__no-items-found hidden">{__("no_items_found")}</p>
        </li>
    {/if}
</ul>
