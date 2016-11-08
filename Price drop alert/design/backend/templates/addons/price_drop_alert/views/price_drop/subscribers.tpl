{capture name="mainbox"}

{assign var="subscription_statuses" value=fn_get_subscription_status_filters()}

{if $subscribers}
<form  action="{""|fn_url}" method="post" name="manage_products_form" id="manage_subscribers_form">
    {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}
    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
    {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
    {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}
    <table width="100%" class="table table-middle">
        <thead>
            <tr>
                <th class="left">
                    {include file="common/check_items.tpl" check_statuses=$subscription_statuses}
                </th>
                <th width="5%"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                <th width="10%"><a class="cm-ajax" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("date")}{if $search.sort_by == "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                <th width="10%"><a class="cm-ajax" href="{"`$c_url`&sort_by=email&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("email")}{if $search.sort_by == "email"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=product&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("product")}{if $search.sort_by == "product"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                <th width="10%"><a class="cm-ajax" href="{"`$c_url`&sort_by=target_price&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("target_price")}{if $search.sort_by == "target_price"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                <th width="10%">{__("notification_type")}</th>
                <th width="10%">{__("language")}</th>
                <th width="5%">&nbsp;</th>
                <th width="10%" class="right"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$subscribers item="subscriber"}
                <tr class="cm-row-status-{$subscriber.status|lower}">
                    <td class="left">
                        <input type="checkbox" name="subscriber_ids[]" value="{$subscriber.subscriber_id}" class="checkbox cm-item cm-item-status-{$subscriber.status|lower}" /></td>
                    </td>
                    <td>
                        <strong>#{$subscriber.subscriber_id}</strong>
                    </td>
                    <td>
                        {$subscriber.timestamp|date_format:"`$settings.Appearance.date_format`"},{$subscriber.timestamp|date_format:"`$settings.Appearance.time_format`"}
                    </td>
                    <td>
                        {if $subscriber.user_id}
                            <a href="{"profiles.update?user_id=`$subscriber.user_id`"}">{$subscriber.email}</a>
                        {else}
                            <a href="mailto:{$subscriber.email}">{$subscriber.email}</a>
                        {/if}
                    </td>
                    <td>
                        <a href="{"products.update?product_id=`$subscriber.product_id`"|fn_url}">{$subscriber.product}</a>
                    </td>
                    <td>
                        {include file="common/price.tpl" value=$subscriber.target_price}
                    </td>
                    <td>
                        {__("addons.price_drop_alert.notification_type_`$subscriber.notification_type`"|lower)}
                    </td>
                    <td>
                        {$languages[$subscriber.lang_code]["name"]}
                    </td>
                    <td class="nowrap">
                        <div class="hidden-tools">
                            {capture name="tools_list"}
                                <li>{btn type="list" text=__("delete") class="cm-confirm cm-post" href="price_drop.delete?subscriber_id=`$subscriber.subscriber_id`"}</li>
                            {/capture}
                            {dropdown content=$smarty.capture.tools_list}
                        </div>
                    </td>
                    <td>
                        {__("addons.price_drop_alert.status_`$subscriber.status`"|lower)}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</form>
<div class="clearfix">
    {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
        <ul class="nav nav-list saved-search">
            <li {if $runtime.mode == "subscribers"}class="active"{/if}><a href="{"price_drop.subscribers"|fn_url}">{__("price_drop_subscribers")}</a></li>
            <li {if $runtime.mode == "statistics"}class="active"{/if}><a href="{"price_drop.statistics"|fn_url}">{__("price_drop_statistics")}</a></li>
        </ul>
    </div>
    {include file="common/saved_search.tpl" dispatch="price_drop.subscribers" view_type="pda_subscribers"}
    <div class="sidebar-row">
        <h6>{__("search")}</h6>
        <form action="{""|fn_url}" name="subscribers_search_form" method="get" class="cm-disable-empty">
            {capture name="simple_search"}
            <input type="hidden" name="type" value="{$search_type|default:"simple"}" autofocus="autofocus" />
            <div class="sidebar-field">
                <label>{__("email")}</label>
                <input type="text" name="email" size="40" value="{$search.email}" />
            </div>
            
            <div class="sidebar-field">
                <label>{__("price")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
                <input type="text" name="price_from" size="1" value="{$search.price_from}" onfocus="this.select();" class="input-small" /> - <input type="text" size="1" name="price_to" value="{$search.price_to}" onfocus="this.select();" class="input-small" />
            </div>
            {/capture}
            
            {capture name="advanced_search"}
                <div class="row-fluid">
                    <div class="group span12 form-horizontal">
                        <div class="control-group">
                            <label class="control-label">{__("period")}</label>
                            <div class="controls">
                                {include file="common/period_selector.tpl" period=$search.period form_name="subscribers_search_form"}
                            </div>
                        </div>                  
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="group span12 form-horizontal">
                        <div class="control-group">
                            <label for="status" class="control-label">{__("status")}</label>
                            <div class="controls">
                                <select name="status" id="status">
                                    <option value="">--</option>
                                    {foreach from=$subscription_statuses item="status_name" key="status"}
                                        <option value="{$status}" {if $search.status == $status}selected="selected"{/if}>{$status_name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>                    
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="group span12 form-horizontal">
                        <div class="control-group">
                            <label for="status" class="control-label">{__("products")}</label>
                            <div class="controls">
                                {if $search.product_ids}
                                    {assign var="product_ids" value=","|explode:$search.product_ids}
                                {/if}
                                {include file="pickers/products/picker.tpl" data_id="products" but_text=__("add") item_ids=$product_ids input_name="product_ids" type="links" no_container=true picker_view=true}
                            </div>
                        </div>                    
                    </div>
                </div>
            {/capture}
            
            {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch="price_drop.subscribers" view_type="pda_subscribers" in_popup=false}
        </form>
    </div>
{/capture}

{capture name="buttons"}
    {if $subscribers}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[price_drop.m_delete]" form="manage_subscribers_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("manage_subscribers") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar content_id="manage_subscribers"}