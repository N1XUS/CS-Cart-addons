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
                <th width="5%">{__("id")}</th>
                <th width="10%">{__("subscribers")}</th>
                <th width="20%">{__("product")}</th>
                <th width="10%">{__("min_price")}</th>
                <th width="10%">{__("max_price")}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$subscribers item="subscriber"}
                <tr>
                    <td>
                        <strong>#{$subscriber.product_id}</strong>
                    </td>
                    <td>
                        {$subscriber.users}
                    </td>
                    <td>
                        <a href="{"products.update?product_id=`$subscriber.product_id`"|fn_url}">{$subscriber.product}</a>
                    </td>
                    <td>
                        {include file="common/price.tpl" value=$subscriber.min_price}
                    </td>
                    <td>
                        {include file="common/price.tpl" value=$subscriber.max_price}
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
    {include file="common/saved_search.tpl" dispatch="price_drop.statistics" view_type="pda_subscriber_stats"}
    <div class="sidebar-row">
        <h6>{__("search")}</h6>
        <form action="{""|fn_url}" name="subscribers_search_form" method="get" class="cm-disable-empty">
            {capture name="simple_search"}
            
            <input type="hidden" name="type" value="{$search_type|default:"simple"}" autofocus="autofocus" />
            <div class="sidebar-field">
                <label for="status" class="control-label">{__("status")}</label>
                <select name="status" id="status">
                    <option value="">--</option>
                    {foreach from=$subscription_statuses item="status_name" key="status"}
                        <option value="{$status}" {if $search.status == $status}selected="selected"{/if}>{$status_name}</option>
                    {/foreach}
                </select>
            </div>

            {/capture}
                        
            {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search dispatch="price_drop.statistics" view_type="pda_subscriber_stats" in_popup=false no_adv_link=true}
        </form>
    </div>
{/capture}

{include file="common/mainbox.tpl" title=__("subscribers_statistics") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar content_id="manage_subscribers"}