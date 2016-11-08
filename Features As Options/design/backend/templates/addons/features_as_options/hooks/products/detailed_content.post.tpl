{include file="common/subheader.tpl" title=__("features_as_options") target="#features_as_options_setting"}
<div id="features_as_options_setting" class="in collapse">
	<fieldset>
        <div class="control-group">
            <label class="control-label" for="elm_features_as_options_parent_product">{__("is_parent_product")}:</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="hidden" name="product_data[parent]" value="N" />
                    <input type="hidden" name="product_data[child_products]" value="{$product_data.child_products}" />
                    <input type="checkbox" name="product_data[parent]" value="Y" id="elm_features_as_options_parent_product"{if $product_data.parent == "Y"} checked="checked"{/if} />
                </label>
            </div>
        </div>
	</fieldset>
</div>