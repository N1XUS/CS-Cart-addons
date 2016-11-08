<?php
    
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        if (!empty($_REQUEST['promotion_id'])) {

            fn_attach_image_pairs('promotion', 'promotion', $_REQUEST['promotion_id'], DESCR_SL);
            
            fn_attach_image_pairs('promotion_list', 'promotion_list', $_REQUEST['promotion_id'], DESCR_SL);
        }
    }
    
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['promotion_ids'])) {
            fn_advanced_promotions_delete_promotion($_REQUEST['promotion_ids']);
        }        
    }
    
    if ($mode == 'delete') {
        if (!empty($_REQUEST['promotion_id'])) {
            fn_advanced_promotions_delete_promotion($_REQUEST['promotion_id']);
        }
    }
}

if ($mode == 'update') {
    $promotion_data = Tygh::$app['view']->getTemplateVars('promotion_data');
    if (!empty($promotion_data['promotion_id'])) {
        $product_ids = array();
        // Since we dont have hooks in needed functions we will update promotion products table each time user goes to promotion update page
        if (!empty($promotion_data['conditions']['conditions'])) {
            foreach($promotion_data['conditions']['conditions'] as $k => $v) {
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
        $product_ids = implode(',', array_unique($product_ids));
        $db_data = array(
            'promotion_id' => $promotion_data['promotion_id'],
            'product_ids' => $product_ids
        );
        db_query("REPLACE INTO ?:promotion_products ?e", $db_data);
        $tabs = Registry::get('navigation.tabs');
        $tabs['advanced_promotions'] = array(
            'title' => __('promotion_images'),
            'href' => "promotions.update?promotion_id=$promotion_data[promotion_id]&selected_section=advanced_promotions",
            'js' => true        
        );
        $promotion_data['main_pair'] = fn_get_image_pairs($promotion_data['promotion_id'], 'promotion', 'M', true, true, DESCR_SL);
        $promotion_data['list_pair'] = fn_get_image_pairs($promotion_data['promotion_id'], 'promotion_list', 'M', true, true, DESCR_SL);
        Tygh::$app['view']->assign('promotion_data', $promotion_data);
        Registry::set('navigation.tabs', $tabs);
    }
}

if ($mode == 'picker') {
    list($promotions, $search) = fn_get_promotions($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);

    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('promotions', $promotions);
    
    Tygh::$app['view']->display('addons/advanced_promotions/pickers/promotions/picker_contents.tpl');
    exit;
}