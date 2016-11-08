{capture name="mainbox"}

{assign var="checkbox_name" value="service_ids"}

<form action="{""|fn_url}" method="post" name="pages_tree_form" id="services_form">
    <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    
    {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id hide_position=$hide_position}
    
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
    {if $services}
        <table width="100%" class="table table-tree table-middle table-nobg">
            <thead>
                <tr>
                    <th width="3%">{include file="common/check_items.tpl"}</th>
                    <th class="left" width="7%">{__("position_short")}</th>
                    <th class="left" width="60%">{__("name")}</th>
                    <th class="left" width="15%">{__("price")} ({$currencies.$primary_currency.symbol nofilter})</th>
                    <th width="10%">&nbsp;</th>
                    <th width="15%" class="right">{__("status")}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$services item="service"}
                    <tr class="cm-row-status-{$service.status|lower}">
                        <td>
                            <input type="checkbox" name="{$checkbox_name}[]" id="checkbox_{$service.service_id}" value="{$service.service_id}" class="cm-item" />
                        </td>
                        <td width="7%">
                            <input type="text" name="services_data[{$service.service_id}][position]" size="3" maxlength="10" value="{$service.position}" class="input-micro input-hidden" />
                            {if "ULTIMATE"|fn_allowed_for}
                                <input type="hidden" name="services_data[{$service.service_id}][company_id]" size="3" maxlength="3" value="{$service.company_id}" class="hidden" />
                            {/if}
                        </td>
                        <td width="60%">
                            <input type="text" class="input-large input-hidden" name="services_data[{$service.service_id}][name]" value="{$service.name}" />
                        </td>
                        <td width="20%">
                            <input type="text" class="input-mini input-hidden" name="services_data[{$service.service_id}][price]" value="{$service.price}" />
                        </td>
                        <td class="nowrap">
                            <div class="hidden-tools">
                                {capture name="tools_list"}
                                    <li>
                                        {include file="common/popupbox.tpl" id="update_service_{$service.service_id}" text="{__('update_service')}: {$service.name}" act="edit" picker_meta=$picker_meta link_text=__("update_service") href="services.update?service_id={$service.service_id}" no_icon_link=true}
                                    </li>
                                    <li>{btn type="text" text=__("delete") href="services.delete?service_id={$service.service_id}" class="cm-confirm cm-tooltip cm-ajax cm-post cm-ajax-force cm-ajax-full-render"}</li>
                                {/capture}
                                {dropdown content=$smarty.capture.tools_list}
                            </div>
                        </td>
                        <td width="15%" class="right nowrap">
                            {include file="common/select_popup.tpl" popup_additional_class="dropleft" id=$service.service_id status=$service.status hidden=true object_id_name="service_id" table="services"}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
    
<!--services_form--></form>

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="addons/additional_services/views/services/update.tpl" service=[]}
    {/capture}
    {include file="common/popupbox.tpl" id="add_service" text=__("add_service") content=$smarty.capture.add_new_picker title=__("add_service") act="general" icon="icon-plus"}
{/capture}

{capture name="buttons"}
    {if $services}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[services.m_delete]" form="services_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save.tpl" but_name="dispatch[services.m_update]" but_role="submit-button" but_target_form="services_form"}
    {/if}
{/capture}

{/capture}

{include file="common/mainbox.tpl" title=__("manage_services") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar content_id="manage_services" select_languages=true}