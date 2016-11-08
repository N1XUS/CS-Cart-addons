<div class="control-group">
    <label class="control-label" for="elm_feature_comparsion_feature_{$id}">{if $is_group || $feature.feature_type == "ProductFeatures::GROUP"|enum}{__("use_features_for_comparsion")}{else}{__("use_as_comparsion_feature")}{/if}</label>
    <div class="controls">
        <input type="hidden" name="feature_data[compare_feature]" value="N" />
        <input id="elm_feature_comparsion_feature_{$id}" type="checkbox" name="feature_data[compare_feature]" value="Y"  data-ca-display-id="Comparsion" {if $feature.compare_feature == "Y"}checked="checked"{/if}{if $feature.parent_id && $group_features[$feature.parent_id].compare_feature == "Y"} disabled="disabled"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_feature_option_variant_{$id}">{if $is_group || $feature.feature_type == "ProductFeatures::GROUP"|enum}{__("use_features_as_options")}{else}{__("use_as_option")}{/if}</label>
    <div class="controls">
        <input type="hidden" name="feature_data[option_variant]" value="N" />
        <input id="elm_feature_option_variant_{$id}" type="checkbox" name="feature_data[option_variant]" value="Y"  data-ca-display-id="OptionVariant" {if $feature.option_variant == "Y"}checked="checked"{/if}{if $feature.parent_id && $group_features[$feature.parent_id].option_variant == "Y"} disabled="disabled"{/if} />
    </div>
</div>