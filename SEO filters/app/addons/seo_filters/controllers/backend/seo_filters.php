<?php
    
use Tygh\Registry;
use Tygh\Enum\ProductFeatures;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    fn_trusted_vars (
        'filter_data',
        'combination_data'
    );
    if ($mode == 'update') {
        $combination_id = fn_update_seo_filter($_REQUEST['filter_data'], $_REQUEST['combination_id']);
        if ($combination_id) {
            return array(CONTROLLER_STATUS_OK, "seo_filters.update?combination_id=" . $combination_id);
        } else {
            return array(CONTROLLER_STATUS_OK, "seo_filters.add");
        }
    }
    if ($mode == 'm_update') {
        $combinations = $_REQUEST['combination_data'];
        if (!empty($combinations)) {
            $index = 0;
            Registry::set('spfilters_static', array());
            foreach($combinations as $combination_id => $combination_data) {
                if (!empty($combination_data['combination_name'])) {
                    if (!empty($combination_data['seo_name'])) {
                        $combination_data['seo_name'] = fn_seo_filters_generate_name($combination_data['seo_name'], $combination_data['category_ids'], $combination_id, $index, DESCR_SL);
                    } else {
                        // Generate seo name from combination name
                        $combination_data['seo_name'] = fn_seo_filters_generate_name($combination_data['combination_name'], $combination_data['category_ids'], $combination_id, $index, DESCR_SL);
                    }                    
                }
                foreach($combination_data as $k => $v) {
                    if (empty($v)) {
                        unset($combination_data[$k]);
                    }
                }
            
                db_query("UPDATE ?:seo_filters SET ?u WHERE combination_id = ?i", $combination_data, $combination_id);
                db_query("UPDATE ?:seo_filter_descriptions SET ?u WHERE combination_id = ?i AND lang_code = ?s", $combination_data, $combination_id, DESCR_SL);
            }
        }
        return array(CONTROLLER_STATUS_OK, "seo_filters.manage");
    }
    if ($mode == 'delete') {
        fn_seo_filters_delete_combination($_REQUEST['combination_id']);
        return array(CONTROLLER_STATUS_OK, "seo_filters.manage");
    }
    if ($mode == 'm_delete') {
        fn_seo_filters_delete_combination($_REQUEST['combination_ids']);
        return array(CONTROLLER_STATUS_OK, "seo_filters.manage");
    }
}

if ($mode == 'manage') {
    $params = $_REQUEST;
    $items_per_page = Registry::get('settings.Appearance.admin_pages_per_page');
    if (isset($_REQUEST['items_per_page'])) {
        $items_per_page = $_REQUEST['items_per_page'];
    }
    $params['items_per_page'] = $items_per_page;
    list($seo_filters, $search) = fn_get_seo_filters($params);
    
    $ids = array();
    
    if (!empty($seo_filters)) {
        foreach($seo_filters as $k => $filter_data) {
            $combinations = db_get_array("SELECT filter_id, variant_id FROM ?:seo_filter_combinations WHERE combination_id = ?i ORDER BY variant_id ASC", $filter_data['combination_id']);
            if (!empty($combinations)) {
                foreach($combinations as $v) {
                    $filter_data['combinations'][$v['filter_id']][] = $v['variant_id'];
                }
            }
            if (!empty($filter_data['combinations'])) {
                $combination_description = array();
                $price_filter = db_get_field("SELECT filter_id FROM ?:product_filters WHERE field_type = ?s AND filter_id IN(?n)", 'P', array_keys($filter_data['combinations']));
                $range_filters = db_get_fields("SELECT filter_id FROM ?:product_filters AS a LEFT JOIN ?:product_features AS b ON a.feature_id = b.feature_id WHERE (b.feature_type = ?s OR b.feature_type = ?s) AND a.filter_id IN(?n)", ProductFeatures::NUMBER_SELECTBOX, ProductFeatures::NUMBER_FIELD, array_keys($filter_data['combinations']));
                foreach($filter_data['combinations'] as $filter_id => $variants) {
                    $combination_description[$filter_id] = array(
                        'name' => db_get_field("SELECT filter FROM ?:product_filter_descriptions WHERE filter_id = ?i AND lang_code = ?s", $filter_id, DESCR_SL),
                        'variants' => array()
                    );
                    if (!empty($price_filter) && $price_filter == $filter_id) {
                        $combination_description[$filter_id]['type'] = 'price';
                        $combination_description[$filter_id]['value'] = $filter_data['price_from'] . '-' . $filter_data['price_to'];
                    } elseif (in_array($filter_id, $range_filters)) {
                        $combination_description[$filter_id]['type'] = 'number';
                        $combination_description[$filter_id]['value'] = implode('-', $variants);
                    } else {
                        foreach ($variants as $variant) {
                            $combination_description[$filter_id]['variants'][$variant] = db_get_field("SELECT variant FROM ?:product_feature_variant_descriptions WHERE variant_id = ?i AND lang_code = ?s", $variant, DESCR_SL);
                        }                        
                    }
                }
                $filter_data['combination_description'] = $combination_description;
                $seo_filters[$k] = $filter_data;
            }        
            if (!empty($filter_data['category_ids']) && !empty($filter_data['combinations'])) {
                $uri = 'categories.view?category_id=' . $filter_data['category_ids'] . '&features_hash=' . fn_generate_filter_hash($filter_data['combinations']);
                $preview_uri = fn_url($uri, 'C', 'http', DESCR_SL);
                $seo_filters[$k]['preview_url'] = $preview_uri;
            }    
        }
    }
    
    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $filter_params = array(
            'get_variants' => true,
            'short' => true
        );
        list($filters) = fn_get_product_filters($filter_params);
        Tygh::$app['view']->assign('filter_items', $filters);
        unset($filters);
    }    
    Tygh::$app['view']->assign('seo_filters', $seo_filters);
    Tygh::$app['view']->assign('search', $search);
}
    
if ($mode == 'update') {
    if (!empty($_REQUEST['combination_id'])) {
        $filter_data = fn_get_filter_combination_data($_REQUEST['combination_id']);
        if ($filter_data) {
            if (!empty($filter_data['combinations'])) {
                $combination_description = array();
                $price_filter = db_get_field("SELECT filter_id FROM ?:product_filters WHERE field_type = ?s AND filter_id IN(?n)", 'P', array_keys($filter_data['combinations']));
                $range_filters = db_get_fields("SELECT filter_id FROM ?:product_filters AS a LEFT JOIN ?:product_features AS b ON a.feature_id = b.feature_id WHERE (b.feature_type = ?s OR b.feature_type = ?s) AND a.filter_id IN(?n)", ProductFeatures::NUMBER_SELECTBOX, ProductFeatures::NUMBER_FIELD, array_keys($filter_data['combinations']));
                foreach($filter_data['combinations'] as $filter_id => $variants) {
                    $combination_description[$filter_id] = array(
                        'name' => db_get_field("SELECT filter FROM ?:product_filter_descriptions WHERE filter_id = ?i AND lang_code = ?s", $filter_id, DESCR_SL),
                        'variants' => array()
                    );
                    if (!empty($price_filter) && $price_filter == $filter_id) {
                        $combination_description[$filter_id]['type'] = 'price';
                        $combination_description[$filter_id]['value'] = $filter_data['price_from'] . '-' . $filter_data['price_to'];
                    } elseif (in_array($filter_id, $range_filters)) {
                        $combination_description[$filter_id]['type'] = 'number';
                        $combination_description[$filter_id]['value'] = implode('-', $variants);
                    } else {
                        foreach ($variants as $variant) {
                            $combination_description[$filter_id]['variants'][$variant] = db_get_field("SELECT variant FROM ?:product_feature_variant_descriptions WHERE variant_id = ?i AND lang_code = ?s", $variant, DESCR_SL);
                        }                        
                    }
                }
                if ($filter_data['category_ids'] && $filter_data['combinations']) {
                    $uri = 'categories.view?category_id=' . $filter_data['category_ids'] . '&features_hash=' . fn_generate_filter_hash($filter_data['combinations']);
                    $preview_uri = fn_url($uri, 'C', 'http', DESCR_SL);
                    Tygh::$app['view']->assign('view_uri', $preview_uri);
                }
                $filter_data['combination_description'] = $combination_description;
            }
            Tygh::$app['view']->assign('filter_data', $filter_data);
            $filter_params = array(
                'get_variants' => true,
                'short' => true,
                'category_ids' => $filter_data['category_ids']
            );
            list($filters) = fn_get_product_filters($filter_params);
            Tygh::$app['view']->assign('filter_items', $filters);
            unset($filters);
        } else {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
}