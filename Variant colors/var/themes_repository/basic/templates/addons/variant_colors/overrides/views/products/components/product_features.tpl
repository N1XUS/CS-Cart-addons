{foreach from=$product_features item="feature"}
    {if $feature.feature_type != "ProductFeatures::GROUP"|enum}
        <div class="ty-product-feature">
        <span class="ty-product-feature__label">{$feature.description nofilter}{if $feature.full_description|trim}{include file="common/help.tpl" text=$feature.description content=$feature.full_description id=$feature.feature_id show_brackets=false link_text="<span class=\"ty-tooltip-block\"><i class=\"ty-icon-help-circle\"></i></span>" wysiwyg=true}{/if}:</span>

        {if $feature.feature_type == "ProductFeatures::MULTIPLE_CHECKBOX"|enum}
            {assign var="hide_affix" value=true}
        {else}
            {assign var="hide_affix" value=false}
        {/if}

        {strip}
        <div class="ty-product-feature__value">
            {if $feature.prefix && !$hide_affix}<span class="ty-product-feature__prefix">{$feature.prefix}</span>{/if}
            {if $feature.feature_type == "ProductFeatures::SINGLE_CHECKBOX"|enum}
            <span class="ty-compare-checkbox" title="{$feature.value}">{if $feature.value == "Y"}<i class="ty-compare-checkbox__icon ty-icon-ok"></i>{/if}</span>
            {elseif $feature.feature_type == "ProductFeatures::DATE"|enum}
                {$feature.value_int|date_format:"`$settings.Appearance.date_format`"}
            {elseif $feature.feature_type == "ProductFeatures::MULTIPLE_CHECKBOX"|enum && $feature.variants}
                <ul class="ty-product-feature__multiple">
                {foreach from=$feature.variants item="var"}
                    {assign var="hide_variant_affix" value=!$hide_affix}
                    {if $var.selected}<li class="ty-product-feature__multiple-item">{if $feature.color == "Y" && $var.color}<span style="background: {$var.color}" class="ty-feature-color__icon"></span>{else}<span class="ty-compare-checkbox" title="{$var.variant}"><i class="ty-compare-checkbox__icon ty-icon-ok"></i></span>{/if}{if !$hide_variant_affix}<span class="ty-product-feature__prefix">{$feature.prefix}</span>{/if}{$var.variant}{if !$hide_variant_affix}<span class="ty-product-feature__suffix">{$feature.suffix}</span>{/if}</li>{/if}
                {/foreach}
                </ul>
            {elseif $feature.feature_type == "ProductFeatures::TEXT_SELECTBOX"|enum || $feature.feature_type == "ProductFeatures::EXTENDED"|enum}
                {foreach from=$feature.variants item="var"}
                    {if $var.selected}{if $feature.color == "Y" && $var.color}<span style="background: {$var.color}" class="ty-feature-color__icon"></span>{/if}{$var.variant}{/if}
                {/foreach}
            {elseif $feature.feature_type == "ProductFeatures::NUMBER_SELECTBOX"|enum || $feature.feature_type == "ProductFeatures::NUMBER_FIELD"|enum}
                {$feature.value_int|floatval|default:"-"}
            {else}
                {$feature.value|default:"-"}
            {/if}
            {if $feature.suffix && !$hide_affix}<span class="ty-product-feature__suffix">{$feature.suffix}</span>{/if}
        </div>
        {/strip}
        </div>
    {/if}
{/foreach}

{foreach from=$product_features item="feature"}
    {if $feature.feature_type == "ProductFeatures::GROUP"|enum && $feature.subfeatures}
        <div class="ty-product-feature-group">
        {include file="common/subheader.tpl" title=$feature.description tooltip=$feature.full_description text=$feature.description}
        {include file="views/products/components/product_features.tpl" product_features=$feature.subfeatures}
        </div>
    {/if}
{/foreach}