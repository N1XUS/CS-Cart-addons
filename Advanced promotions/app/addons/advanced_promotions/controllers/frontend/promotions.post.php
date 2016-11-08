<?php

use Tygh\Registry;
    
if ($mode == 'list') {
    $promotions = Tygh::$app['view']->getTemplateVars('promotions');
    if (!empty($promotions)) {
        foreach($promotions as $id => $promotion) {
            $promotions[$id]['main_pair'] = fn_get_image_pairs($id, 'promotion', 'M', true, true, CART_LANGUAGE);
            $promotions[$id]['list_pair'] = fn_get_image_pairs($id, 'promotion_list', 'M', true, true, CART_LANGUAGE);
        }
        Tygh::$app['view']->assign('promotions', $promotions);
    }
}

if ($mode == 'view' || $mode == 'quick_view') {
    if (!empty($_REQUEST['promotion_id'])) {
        $promotion = fn_get_promotion_data($_REQUEST['promotion_id']);
        $product_ids = array();
        if (empty($promotion)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
        if (!empty($promotion['conditions']['conditions'])) {
            foreach((array) $promotion['conditions']['conditions'] as $k => $v) {
                if ($v['condition'] == 'products' && $v['operator'] == 'in') {
                    if (!empty($v['value'])) {
                        if (is_array($v['value'])) {
                            foreach($v['value'] as $_v) {
                                $product_ids[] = $_v['product_id'];
                            }
                        } else {
                            $product_ids[] = $v['value'];
                        }                        
                    }
                }
            }
        }
        
        $promotion['main_pair'] = fn_get_image_pairs($_REQUEST['promotion_id'], 'promotion', 'M', true, true, CART_LANGUAGE);
        $promotion['list_pair'] = fn_get_image_pairs($_REQUEST['promotion_id'], 'promotion_list', 'M', true, true, CART_LANGUAGE);
        Tygh::$app['view']->assign('promotion', $promotion);
        if (!empty($product_ids)) {
            $product_ids = array_unique(explode(',', implode(',', $product_ids)));
            $params = $_REQUEST;
    
            if ($items_per_page = fn_change_session_param($_SESSION, $_REQUEST, 'items_per_page')) {
                $params['items_per_page'] = $items_per_page;
            }
            if ($sort_by = fn_change_session_param($_SESSION, $_REQUEST, 'sort_by')) {
                $params['sort_by'] = $sort_by;
            }
            if ($sort_order = fn_change_session_param($_SESSION, $_REQUEST, 'sort_order')) {
                $params['sort_order'] = $sort_order;
            }
            $params['pid'] = $product_ids;
            $per_page = Registry::get('settings.Appearance.products_per_page');
            if ($mode == 'quick_view') {
                $per_page = 3;
            }
            list($products, $search) = fn_get_products($params, $per_page, CART_LANGUAGE);
            
            if (isset($search['page']) && ($search['page'] > 1) && empty($products)) {
                return array(CONTROLLER_STATUS_NO_PAGE);
            }
            
            fn_gather_additional_products_data($products, array(
                'get_icon' => true,
                'get_detailed' => true,
                'get_additional' => true,
                'get_options' => true,
                'get_discounts' => true,
                'get_features' => false
            ));
            $selected_layout = fn_get_products_layout($_REQUEST);
            Tygh::$app['view']->assign('show_qty', true);
            Tygh::$app['view']->assign('products', $products);
            Tygh::$app['view']->assign('search', $search);
            Tygh::$app['view']->assign('selected_layout', $selected_layout);
        }
        
        fn_add_breadcrumb(__("promotions"), "promotions.list");
        
        fn_add_breadcrumb($promotion['name']);        
        
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);   
    }
}