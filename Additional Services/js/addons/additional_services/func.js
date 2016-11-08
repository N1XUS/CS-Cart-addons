function fn_calculate_total_services_cost()
{
    params = [];
    parents = Tygh.$('#additional_services_list');
    items = Tygh.$('input:checked', parents);
    
    console.log(items.length);
    
    if (items.length > 0) {
        Tygh.$.each(items, function(id, elm) {
            params.push({name: elm.name, value: elm.value});
        });        
    } else {
        params.push({name: "service_ids[]", value: ""});
    }

    url = fn_url('checkout.checkout');

    for (var i in params) {
        url += '&' + params[i]['name'] + '=' + escape(params[i]['value']);
    }

    Tygh.$.ceAjax('request', url, {
        result_ids: 'shipping_rates_list,checkout_info_summary_*,checkout_info_order_info_*,additional_services_list',
        method: 'get',
        full_render: true
    });
}
