<div class="control-group">
    <label class="control-label" for="elm_feature_color_variants_{$id}">{__("color_feature")}</label>
    <div class="controls">
        <input type="hidden" name="feature_data[color]" value="N" />
        <input id="elm_feature_color_variants_{$id}" type="checkbox" class="cm-color-feature" name="feature_data[color]" value="Y" data-ca-feature-id="{$feature.feature_id|default:0}" data-ca-display-id="Color" {if $feature.color == "Y"}checked="checked"{/if}{if $feature.parent_id && $group_features[$feature.parent_id].color == "Y"} disabled="disabled"{/if} />
    </div>
</div>