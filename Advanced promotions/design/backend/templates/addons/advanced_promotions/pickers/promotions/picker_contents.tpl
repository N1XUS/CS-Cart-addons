{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    var display_type = '{$smarty.request.display|escape:javascript nofilter}';

    $.ceEvent('on', 'ce.formpost_promotions_form', function(frm, elm) {
        var promotions = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                promotions[id] = $('#promotion_title_' + id).text();
            });

            {literal}
            
            $.cePicker('add_js_item', frm.data('caResultId'), promotions, '', {
                '{promotion_id}': '%id',
                '{promotion}': '%item'
            });
            {/literal}

            if (display_type != 'radio') {
                $.ceNotification('show', {
                    type: 'N', 
                    title: _.tr('notice'), 
                    message: _.tr('text_items_added'), 
                    message_state: 'I'
                });
            }
        }

        return false;        
    });
}(Tygh, Tygh.$));
</script>
{/if}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="promotions_form">

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="center">
        {if $smarty.request.display != "radio"}
        {include file="common/check_items.tpl"}</th>
        {/if}
    <th>{__("id")}</th>
    <th>{__("name")}</th>
</tr>
</thead>
{foreach from=$promotions item=promotion}
<tr>
    <td class="center">
        {if $smarty.request.display == "radio"}
        <input type="radio" name="{$smarty.request.checkbox_name|default:"promotions_ids"}" value="{$promotion.promotion_id}" class="radio" />
        {else}
        <input type="checkbox" name="{$smarty.request.checkbox_name|default:"promotions_ids"}[{$promotion.promotion_id}]" value="{$promotion.promotion_id}" class="checkbox cm-item" />
        {/if}
    </td>
    <td><a href="{"promotions.update?promotion_id=`$promotion.promotion_id`"|fn_url}">&nbsp;<span>{$promotion.promotion_id}</span>&nbsp;</a></td>
    <td><a id="promotion_title_{$promotion.promotion_id}" href="{"promotions.update?promotion_id=`$promotion.promotion_id`"|fn_url}">{$promotion.name}</a></td>
</tr>
{foreachelse}
<tr class="no-items">
    <td colspan="2"><p>{__("no_data")}</p></td>
</tr>
{/foreach}
</table>

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

<div class="buttons-container">
    {if $smarty.request.display == "radio"}
        {assign var="but_close_text" value=__("choose")}
    {else}
        {assign var="but_close_text" value=__("add_promotions_and_close")}
        {assign var="but_text" value=__("add_promotions")}
    {/if}
    {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
