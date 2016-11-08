<fieldset>
    <div class="control-group">
        <label class="control-label" for="ship_np_mode">{__("addons.novaposhta.shipping_type")}</label>
        <div class="controls">
            <select id="ship_np_mode" name="shipping_data[service_params][shipping_type]">
                <option value="WarehouseDoors" {if $shipping.service_params.shipping_type == "WarehouseDoors"}selected="selected"{/if}>{__("addons.novaposhta.warehouse_doors")}</option>
                <option value="WarehouseWarehouse" {if $shipping.service_params.shipping_type == "WarehouseWarehouse"}selected="selected"{/if}>{__("addons.novaposhta.warehouse_warehouse")}</option>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="ship_np_default_weight">{__("addons.novaposhta.default_weight")} ({$settings.General.weight_symbol})</label>
        <div class="controls">
            <input id="ship_np_default_weight" type="text" name="shipping_data[service_params][default_weight]" size="30" value="{$shipping.service_params.default_weight|default:"1000"}" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="ship_np_default_volume">{__("addons.novaposhta.default_volume")}</label>
        <div class="controls">
            <input id="ship_np_default_volume" type="text" name="shipping_data[service_params][default_volume]" size="30" value="{$shipping.service_params.default_volume|default:"0.00"}" />
        </div>
    </div>
</fieldset>