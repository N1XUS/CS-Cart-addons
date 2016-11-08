{capture name="mainbox"}
    {if $seo_filters}
        <form  action="{""|fn_url}" method="post" name="manage_seo_filters_form" id="manage_seo_filters_form">
            {include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}
            {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
            {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
            {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
            {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}
            <table width="100%" class="table table-middle">
                <thead>
                    <tr>
                        <th class="left">
                            {include file="common/check_items.tpl"}
                        </th>
                        <th width="5%"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                        <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("name")}{if $search.sort_by == "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                        <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=seo_name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("seo_name")}{if $search.sort_by == "seo_name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                        <th width="30%"><a class="cm-ajax" href="{"`$c_url`&sort_by=combination_seo_name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("combination_seo_name")}{if $search.sort_by == "combination_seo_name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                        <th width="10%"><a class="cm-ajax" href="{"`$c_url`&sort_by=category&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("category")}{if $search.sort_by == "category"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                        <th width="5%">&nbsp;</th>
                    </tr>
                </thead>         
                <tbody>
                    {foreach from=$seo_filters item="combination"}
                        <tr>
                            <td class="left">
                                <input type="checkbox" name="combination_ids[]" value="{$combination.combination_id}" class="checkbox cm-item" /></td>
                            </td>
                            <td width="5%">
                                {if $combination.combination_description}
                                    {capture name="combination_description"}
                                        <p>{__("current_combination")}:</p>
                                        {foreach from=$combination.combination_description item="filter"}
                                            {if $filter.type == "price"}
                                                <p><strong>{$filter.name}</strong>:&nbsp;{$filter.value}&nbsp;({$currencies.$primary_currency.symbol nofilter})</p>
                                            {elseif $filter.type == "number"}
                                                <p><strong>{$filter.name}</strong>:&nbsp;{$filter.value}</p>
                                            {else}
                                                {if $filter.variants}
                                                    <p><strong>{$filter.name}</strong>:&nbsp;{implode(", ", $filter.variants)}</p>
                                                {/if}                                               
                                            {/if}
                                        {/foreach}
                                    {/capture}
                                {else}
                                    {capture name="combination_description"}{/capture}
                                {/if}
                                <a {if $combination.combination_description}class="cm-tooltip" title="{strip}{$smarty.capture.combination_description}{/strip}"{/if}href="{"seo_filters.update?combination_id=`$combination.combination_id`"|fn_url}">
                                    <strong>#{$combination.combination_id}</strong>
                                </a>
                            </td>
                            <td width="25%">
                                <input type="text" name="combination_data[{$combination.combination_id}][combination_name]" size="15" maxlength="32" value="{$combination.combination_name}" class="input-hidden" />
                            </td>
                            <td width="25%">
                                <input type="text" name="combination_data[{$combination.combination_id}][seo_name]" size="15" maxlength="32" value="{$combination.seo_name}" class="input-hidden" />
                            </td>
                            <td width="30%">
                                <input type="text" name="combination_data[{$combination.combination_id}][combination_seo_name]" size="15" maxlength="32" value="{$combination.combination_seo_name}" class="input-hidden" />
                            </td>
                            <td width="35%">
                                {if $combination.category}
                                    <input type="hidden" name="combination_data[{$combination.combination_id}][category_ids]" value="{$combination.category_ids}" />
                                    <a href="{"categories.update?category_id=`$combination.category_ids`"|fn_url}">{$combination.category}</a>
                                {else}
                                    <input type="hidden" name="combination_data[{$combination.combination_id}][category_ids]" value="" />
                                    -
                                {/if}
                            </td>
                            <td class="nowrap">
                                <div class="hidden-tools">
                                    {capture name="tools_list"}
                                        {if $combination.preview_url}
                                            <li>{btn type="list" target="_blank" text=__("preview") href=$combination.preview_url}</li>
                                        {/if}
                                        <li>{btn type="list" text=__("edit") href="seo_filters.update?combination_id=`$combination.combination_id`"}</li>
                                        <li>{btn type="list" text=__("delete") class="cm-confirm cm-post" href="seo_filters.delete?combination_id=`$combination.combination_id`"}</li>
                                    {/capture}
                                    {dropdown content=$smarty.capture.tools_list}
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>       
            </table>
    
            <div class="clearfix">
                {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
            </div>
        </form>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="seo_filters.manage" view_type="seo_filters"}
    <div class="sidebar-row">
        <h6>{__("search")}</h6>
        <form action="{""|fn_url}" name="subscribers_search_form" method="get" class="cm-disable-empty">
            {capture name="simple_search"}
                <input type="hidden" name="type" value="{$search_type|default:"simple"}" autofocus="autofocus" />
                <div class="sidebar-field">
                    <label>{__("name")}</label>
                    <input type="text" name="combination_name" size="40" value="{$search.combination_name}" />
                </div>
                <div class="sidebar-field">
                    <label>{__("seo_name")}</label>
                    <input type="text" name="seo_name" size="40" value="{$search.seo_name}" />
                </div>
                <div class="sidebar-field">
                    <label>{__("search_in_category")}</label>
                    {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
                        {if $search.category_ids}
                            {assign var="s_cid" value=$search.category_ids}
                        {else}
                            {assign var="s_cid" value="0"}
                        {/if}
                        {include file="pickers/categories/picker.tpl" company_ids=$picker_selected_companies data_id="location_category" input_name="category_ids" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
                    {else}
                        {if $runtime.mode == "picker"}
                            {assign var="trunc" value="38"}
                        {else}
                            {assign var="trunc" value="25"}
                        {/if}
                        <select name="category_ids">
                            <option value="0" {if $category_data.parent_id == "0"}selected="selected"{/if}>- {__("all_categories")} -</option>
                            {foreach from=0|fn_get_plain_categories_tree:false:$smarty.const.CART_LANGUAGE:$picker_selected_companies item="search_cat" name=search_cat}
                            {if $search_cat.store}
                            {if !$smarty.foreach.search_cat.first}
                                </optgroup>
                            {/if}
            
                            <optgroup label="{$search_cat.category}">
                                {assign var="close_optgroup" value=true}
                                {else}
                                <option value="{$search_cat.category_id}" {if $search_cat.disabled}disabled="disabled"{/if} {if $search.category_ids == $search_cat.category_id}selected="selected"{/if} title="{$search_cat.category}">{$search_cat.category|escape|truncate:$trunc:"...":true|indent:$search_cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                                {/if}
                                {/foreach}
                                {if $close_optgroup}
                            </optgroup>
                            {/if}
                        </select>
                    {/if}
                </div>    
            {/capture}
            {capture name="advanced_search"}
                <div class="group form-horizontal">
                    <div class="control-group">
                        <input type="hidden" name="empty_combinations" value="N" />
                        <label for="empty_combinations" class="checkbox inline"><input type="checkbox" value="Y" name="empty_combinations" id="empty_combinations"{if $search.empty_combinations == "Y"} checked="checked"{/if}>{__("with_empty_combinations")}</label>
                    </div>
                </div>
                <div class="group form-horizontal">
                {if !"ULTIMATE:FREE"|fn_allowed_for && $filter_items}
                <div class="control-group">
                
                    <a href="#" class="search-link cm-combination open cm-save-state" id="sw_filter">
                    <span id="on_filter" class="exicon-expand cm-save-state {if $smarty.cookies.filter}hidden{/if}"> </span>
                    <span id="off_filter" class="exicon-collapse cm-save-state {if !$smarty.cookies.filter}hidden{/if}"></span>
                    {__("search_by_product_filters")}</a>
                
                    <div class="controls">
                        <div id="filter"{if !$smarty.cookies.filter} class="hidden"{/if}>
                            {include file="views/products/components/advanced_search_form.tpl" filter_features=$filter_items prefix="filter_" data_name="filter_variants"}
                        </div>
                    </div>
                </div>
                {/if}
                </div>            
            {/capture}
            {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch="seo_filters.manage" view_type="seo_filters" in_popup=false}
        </form>
    </div>
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="seo_filters.add" prefix="top" hide_tools="true" title=__("add_seo_filter") icon="icon-plus"}
{/capture}

{capture name="buttons"}
    {if $seo_filters}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[seo_filters.m_delete]" form="manage_seo_filters_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save.tpl" but_name="dispatch[seo_filters.m_update]" but_role="submit-button" but_target_form="manage_seo_filters_form"}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("manage_seo_filters") select_languages=$seo_filters adv_buttons=$smarty.capture.adv_buttons content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar content_id="manage_seo_filters"}