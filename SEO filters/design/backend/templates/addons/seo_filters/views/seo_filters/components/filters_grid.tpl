{split data=$filter_features size="3" assign="splitted_filter" preverse_keys=true}

<table cellpadding="8">
{foreach from=$splitted_filter item="filters_row" name="filters_row"}
<thead>
    <tr>
    {foreach from=$filters_row item="filter"}
        {if $filter && $filter.field_type != "P"}
        <td><strong>{$filter.filter|default:$filter.description}</strong></td>
        {/if}
    {/foreach}
    </tr>
</thead>
<tr valign="top"{if ($splitted_filter|sizeof > 1) && $smarty.foreach.filters_row.first} class="delim"{/if}>
{foreach from=$filters_row item="filter"}

    {if $filter && $filter.field_type != "P"}
        {$id = $filter.filter_id|default:$filter.feature_id}
        <td width="33%">
            {if $filter.feature_type == "ProductFeatures::TEXT_SELECTBOX"|enum
                || $filter.feature_type == "ProductFeatures::EXTENDED"|enum
                || $filter.feature_type == "ProductFeatures::MULTIPLE_CHECKBOX"|enum
                || $filter.feature_type == "ProductFeatures::NUMBER_SELECTBOX"|enum && !$id}
                <div class="object-selector">
                    <select id="{$prefix}variants_{$id}"
                            class="cm-object-selector"
                            multiple
                            name="{$data_name}[{$id}][]"
                            data-ca-placeholder={__("search")}
                            data-ca-enable-images="true"
                            data-ca-image-width="30"
                            data-ca-image-height="30"
                            data-ca-enable-search="true"
                            data-ca-page-size="10"
                            {if $filter.feature_id}
                            {if $filter.use_variant_picker}
                            data-ca-load-via-ajax="true"
                            {/if}
                            data-ca-data-url="{"product_features.get_variants_list?feature_id=`$filter.feature_id`"|fn_url nofilter}"
                            {/if}
                            data-ca-close-on-select="false">
                        {foreach from=$filter.variants key="variant_id" item="variant"}
                            <option value="{$variant_id}"{if $variant_id|in_array:$filter_data.combinations[$id]} selected="selected"{/if}>{$filter.prefix}{$variant.variant}{$filter.suffix}</option>
                        {/foreach}
                    </select>
                </div>
            {elseif $filter.feature_type == "ProductFeatures::NUMBER_FIELD"|enum
                || $filter.feature_type == "ProductFeatures::NUMBER_SELECTBOX"|enum && $id
                || $filter.feature_type == "ProductFeatures::DATE"|enum
                || $filter.condition_type == "D"}
                <div>
                    <label class="radio"><input class="cm-switch-availability cm-switch-inverse" type="radio" name="range_selected[{$id}]" id="sw_{$prefix}select_custom_{$id}_suffix_N" value="" {if !$filter_data.combinations[$id]}checked="checked"{/if} />{__("none")}
                    </label>
                </div>

                {$disable = true}
                <label class="radio"><input class="cm-switch-availability" type="radio" name="range_selected[{$id}]" id="sw_{$prefix}select_custom_{$id}_suffix_Y" value="1" {if $filter_data.combinations[$id]}{$disable = false}checked="checked"{/if}  />{__("your_range")}</label>

                <div id="{$prefix}select_custom_{$id}">
                    {if $filter.feature_type == "ProductFeatures::DATE"|enum}
                        {if $disable}
                            {$date_extra = "disabled=\"disabled\""}
                        {else}
                            {$date_extra = ""}
                        {/if}

                        {include file="common/calendar.tpl" date_id="`$prefix`range_`$id`_from" date_name="`$data_name`[`$id`][0]" date_val=$filter_data.combinations[$id].0 extra=$date_extra start_year=$settings.Company.company_start_year}

                        {include file="common/calendar.tpl" date_id="`$prefix`range_`$id`_to" date_name="`$data_name`[`$id`][1]" date_val=$filter_data.combinations[$id].1 extra=$date_extra start_year=$settings.Company.company_start_year}

                    {else}

                        {$from_value = $filter_data.combinations[$id].0}
                        {$to_value = $filter_data.combinations[$id].1}

                        {strip}
                        <input type="text" name="{$data_name}[{$id}][0]" id="{$prefix}range_{$id}_from" size="3" class="input-mini" value="{$from_value}" {if $disable}disabled="disabled"{/if} />
                        -
                        <input type="text" name="{$data_name}[{$id}][1]" id="{$prefix}range_{$id}_to" size="3" class="input-mini" value="{$to_value}" {if $disable}disabled="disabled"{/if} />
                        {/strip}
                    {/if}
                </div>

            {elseif $filter.feature_type == "ProductFeatures::SINGLE_CHECKBOX"|enum || $filter.condition_type == "C"}
                    <label for="{$prefix}ranges_{$id}_none" class="radio">
                    <input type="radio" name="{$data_name}[{$id}][]" id="{$prefix}ranges_{$id}_none" value="" {if !$filter_data.combinations[$id].0}checked="checked"{/if} />
                    {__("none")}</label>

                    <label for="{$prefix}ranges_{$id}_yes" class="radio">
                    <input type="radio" name="{$data_name}[{$id}][]" id="{$prefix}ranges_{$id}_yes" value="Y" {if $filter_data.combinations[$id].0 == "Y"}checked="checked"{/if} />
                    {__("yes")}</label>

                    <label for="{$prefix}ranges_{$id}_no" class="radio">
                    <input type="radio" name="{$data_name}[{$id}][]" id="{$prefix}ranges_{$id}_no" value="N" {if $filter_data.combinations[$id].0 == "N"}checked="checked"{/if} />
                    {__("no")}</label>

            {elseif $filter.feature_type == "ProductFeatures::TEXT_FIELD"|enum}
                {$filter.prefix}<input type="text" name="{$data_name}[{$id}][]" class="input-mini" value="{$filter_data.combinations[$id].0}" />{$filter.suffix}
            {/if}
        </td>
    {/if}
{/foreach}
</tr>
{/foreach}
</table>