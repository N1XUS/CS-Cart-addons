(function(_, $) {
    $(_.doc).on("change", ".cm-warehouse-select", function(e) {
        params = [];
        parents = Tygh.$('#shipping_rates_list');
        radio = Tygh.$('input[type=radio]:checked', parents);
    
        Tygh.$.each(radio, function(id, elm) {
            params.push({name: elm.name, value: elm.value});
        });
        
        warehouse_list = Tygh.$('.cm-warehouse-select', parents);

        Tygh.$.each(warehouse_list, function(id, elm) {
            params.push({name: elm.name, value: elm.value}); 
        });
    
        url = fn_url('checkout.checkout');
    
        for (var i in params) {
            url += '&' + params[i]['name'] + '=' + escape(params[i]['value']);
        }
    
        Tygh.$.ceAjax('request', url, {
            result_ids: 'shipping_rates_list,checkout_info_summary_*,checkout_info_order_info_*',
            method: 'get',
            full_render: true
        });
    });
    
    var np_maps = [];
    
    var initNovaPoshtaMap = function() {
        if (!('ymaps' in window)) {
            $.getScript('//api-maps.yandex.ru/2.1/?lang=' + Tygh.cart_language, function () {
                ymaps.ready(function () {
                    ShowNovaPoshtaMap();
                });
            });
        } else {
            ShowNovaPoshtaMap();
        } 
    }
    
    var ShowNovaPoshtaMap = function() {
        if ($(".warehouse-map").length == 0) {
            return;
        }
        $(".warehouse-map").each(function() {
            var group_key = $(this).attr('data-group-key');
            if (np_maps[group_key]) {
                np_maps[group_key].destroy();
            }
            var map = new ymaps.Map($(this).attr('id'), {
                center: [$(this).attr('data-lat'), $(this).attr('data-lng')],
                controls: ['zoomControl', 'typeSelector',  'fullscreenControl'],
                zoom: 13
            }, {
                searchControlProvider: 'yandex#search'
            });
            map.geoObjects.add(new ymaps.Placemark([$(this).attr('data-lat'), $(this).attr('data-lng')], {
                balloonContent: $("#warehouse_info_" + $(this).attr('data-group-key') + '_' + $(this).attr('data-id')).html()
            }));
            np_maps[group_key] = map;
        });        
    }
    $.ceEvent('on', 'ce.commoninit', function() {
        initNovaPoshtaMap();
    });

    $.ceEvent('on', 'ce.ajaxdone', function() {
        initNovaPoshtaMap();
    });
}(Tygh, Tygh.$));