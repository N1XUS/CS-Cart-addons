{if $service}
    {assign var="id" value=$service.service_id}
{else}
    {assign var="id" value=0}
{/if}
<div id="content_update_service_{$id}">
    <form action="{""|fn_url}" name="update_filter_form_{$id}" method="post" class="form-horizontal form-edit">
        <input type="hidden" class="cm-no-hide-input" name="service_data[service_id]" value="{$id}" />
        <input type="hidden" class="cm-no-hide-input" name="redirect_url" value="{$smarty.request.return_url}" />
        <fieldset>
            <div class="control-group">
                <label for="elm_service_name_{$id}" class="control-label cm-required">{__("name")}</label>
                <div class="controls">
                    <input type="text" id="elm_service_name_{$id}" name="service_data[name]" class="span9" value="{$service.name}" />
                </div>
            </div>
            
            <div class="control-group">
                <label for="elm_price_price" class="control-label">{__("price")} ({$currencies.$primary_currency.symbol nofilter}):</label>
                <div class="controls">
                    <input type="text" name="service_data[price]" id="elm_price_price" size="10" value="{$service.price|default:"0.00"|fn_format_price:$primary_currency:null:false}" class="input-long" />
                </div>
            </div>
            
            <div class="control-group">
                <label for="elm_service_description_{$id}" class="control-label">{__("description")}</label>
                <div class="controls">
                    <textarea id="elm_service_description_{$id}" name="service_data[description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$service.description}</textarea>
                </div>
            </div>
            
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                <div class="control-group">
                    <label class="control-label">{__("usergroups")}:</label>
                    <div class="controls">
                        {include file="common/select_usergroups.tpl" id="ug_id" name="service_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$service.usergroup_ids input_extra="" list_mode=false}
                    </div>
                </div>
            {/if}
            
            {assign var="simple_shipping_methods" value=fn_additional_services_get_shipping_methods()}
            {if $simple_shipping_methods}
                <div class="control-group">
                    <label for="elm_price_price" class="control-label">{__("shipping_methods")}:</label>
                    <div class="controls">
                        <input type="hidden" name="service_data[shipping_ids]" value="0" />
                        {foreach from=$simple_shipping_methods item="shipping" key="shipping_id"}
                            <label><input type="checkbox" name="service_data[shipping_ids][]" value="{$shipping_id}"{if $service.shipping_ids && in_array($shipping_id, $service.shipping_ids)} checked="checked"{/if} />&nbsp;{$shipping}</label>
                        {/foreach}
                    </div>
                </div>
            {/if}
            
            <div class="control-group">
                <label for="location_position" class="control-label">{__("categories")}: </label>
                <div class="controls">
                    <div id="service_{$id}_category_ids">
                        {include file="pickers/categories/picker.tpl" data_id="categories" input_name="service_data[category_ids]" item_ids=$service.category_ids multiple=true use_keys="N" view_mode="links"}
                    </div>
                </div>
            </div>
            
            <div class="control-group">
                <label for="location_position" class="control-label">{__("products")}: </label>
                <div class="controls">
                    <div id="service_{$id}_product_ids">
                        {if $service.product_ids}
                            {assign var="product_ids" value=","|explode:$service.product_ids}
                        {/if}
                        {include file="pickers/products/picker.tpl" data_id="products" but_text=__("add") item_ids=$product_ids input_name="service_data[product_ids]" type="links" no_container=true picker_view=true}
                    </div>
                </div>
            </div>
            
        </fieldset>
        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_name="dispatch[services.update]" cancel_action="close" hide_first_button=false save=$id}
        </div>

    </form>
<!--content_update_service_{$id}--></div>