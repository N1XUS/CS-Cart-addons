<?php

use Tygh\Registry;

function fn_features_as_options_get_product_feature_data_before_select(&$fields) {
    $fields[] = '?:product_features.compare_feature';
    $fields[] = '?:product_features.option_variant';
}

function fn_features_as_options_get_product_features_list_before_select(&$fields, &$join) {
    $fields .= db_quote(", f.compare_feature, f.option_variant");
}

function fn_features_as_options_update_product_post(&$product_data, &$product_id) {
    $product_data['product_id'] = $product_id;
    $features_options = fn_features_as_options_get_option_features($product_data);
    $product_data['option_features'] = $features_options['option_features'];
    $child_products = $features_options['child_products'];
    $parent_product_id = false;
    
    $child_products = array_unique(explode(',', $child_products));
    $key = array_search($product_data['product_id'], $child_products);
    if ($key !== false) {
        unset($child_products[$key]);
    }
    
    $child_products = implode(',', $child_products);
    if ($child_products) {
        $parent_product_id = db_get_field("SELECT product_id FROM ?:products WHERE parent = ?s AND product_id IN (?p)", 'Y', $child_products);
    }
    
    if (!$parent_product_id) {
        $product_data['parent'] = 'Y';
    }
    
    $product_data['child_products'] = $child_products;
    
    db_query("UPDATE ?:products SET parent = ?s WHERE product_id = ?i", $product_data['parent'], $product_id);
    
    if ($product_data['parent'] == "Y") {
        if (!empty($product_data['child_products'])) {
            $child_products = explode(',', $product_data['child_products']);
            foreach((array)$child_products as $child) {
                $u_data = array(
                    'parent' => 'N',
                    'parent_product_id' => $product_id
                );
                db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $u_data, $child);
            }
        }
    } else {
        $u_data = array(
            'parent' => 'N',
            'parent_product_id' => $parent_product_id
        );
        db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $u_data, $product_id);
    }
}

function fn_features_as_options_get_product_data_post(&$product_data, &$auth, &$preview, &$lang_code) {
    $features_options = fn_features_as_options_get_option_features($product_data);
    
    $product_data['option_features'] = $features_options['option_features'];
    $child_products = $features_options['child_products'];
    
    $child_products = array_unique(explode(',', $child_products));
    $key = array_search($product_data['product_id'], $child_products);
    if ($key !== false) {
        unset($child_products[$key]);
    }
    
    $child_products = implode(',', $child_products);
    
    $product_data['child_products'] = $child_products;
}


// Get our cool features

function fn_features_as_options_get_option_features($product_data) {
    
    $temp_features = fn_get_product_features_list($product_data, 'A');
    
    $return = array(
        'child_products' => false,
        'option_features' => false
    );
    
    $compare_features = array();
    
    $option_features = array();
    
    $selected_options = array();
    
    $child_products = array();
    
    foreach($temp_features as $k=>$v) {
        if ($v['compare_feature'] == 'Y') {
            $compare_features[$k] = $v;
        }
        if ($v['option_variant'] == 'Y') {
            $option_features[$k] = $v;
        }
    }
    
    unset($temp_features);
    
    $product_ids = '';
    
    foreach($compare_features as $v) {
        if (!empty($product_ids)) {
            $condition = db_quote(" AND product_id IN(?p)", $product_ids);
        }
        $product_ids = db_get_field("SELECT GROUP_CONCAT(a.product_id SEPARATOR ',') FROM ?:product_features_values AS a LEFT JOIN ?:products AS b ON a.product_id = b.product_id WHERE a.feature_id = ?i AND a.variant_id = ?i AND b.status != 'D'", $v['feature_id'], $v['variant_id']);
    }
    
    if (!$product_ids) {
        return $return;
    }
    
    foreach($option_features as $k => $v) {
        $params = array(
            'feature_id' => $v['feature_id']
        );
        list($variants, ) = fn_get_product_feature_variants($params);

        $option_features[$k]['variants'] = $variants;
        
        $selected_options[$k] = $v['variant_id'];
    }
    
    foreach($option_features as $k => $v) {
        $_sel_opts = $selected_options;
        unset($_sel_opts[$k]);
        // Get product ids for selected options
        foreach($v['variants'] as $_k => $_v) {
            if ($v['variant_id'] == $_k) {
                $_v['product_id'] = $product_data['product_id'];
            } else {
                $pids = $product_ids;
                foreach($_sel_opts as $__k => $__v) {
                    if ($pids) {
                        $pids = db_get_field("SELECT GROUP_CONCAT(product_id SEPARATOR ',') FROM ?:product_features_values WHERE feature_id = ?i AND variant_id = ?i AND product_id IN(?p)", $__k, $__v, $pids);
                    } else {
                        break;
                    }
                }
                
                $_v['product_id'] = db_get_field("SELECT product_id FROM ?:product_features_values WHERE feature_id = ?i AND variant_id = ?i AND product_id IN(?p)", $_v['feature_id'], $_v['variant_id'], $pids);
            }
            if (empty($_v['product_id'])) {
                unset($option_features[$k]['variants'][$_k]);
            } else {
                $option_features[$k]['variants'][$_k] = $_v;
            }
        }
    }
    
    if (!empty($product_ids)) {
        $product_ids = array_unique(explode(',', $product_ids));

        $child_products = $product_ids;
    }
    
    if (!empty($child_products)) {
        $child_products = implode(',', $child_products);
    }
    
    $return = array(
        'child_products' => $child_products,
        'option_features' => $option_features
    );
    
    return $return;
    
}

function fn_exim_get_parent_product_code($product_id) {
    $parent_product_id = db_get_field("SELECT parent_product_id FROM ?:products WHERE product_id = ?i", $product_id);
    return db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $parent_product_id);
}

function fn_exim_set_parent_product_code($product_id, $data) {
    if (empty($data)) {
        db_query("UPDATE ?:products SET parent_product_id = '' WHERE product_id = ?i", $product_id);
    
        return true;
    }
    $parent_product_id = db_get_field("SELECT product_id WHERE product_code = ?s", $data);
    if (!empty($parent_product_id)) {
        db_query("UPDATE ?:products SET parent_product_id = ?i WHERE product_id = ?i", $parent_product_id, $product_id);
    }
}

function fn_exim_get_parent_relation($product_id) {
    $type = db_get_field("SELECT parent FROM ?:products WHERE product_id = ?i", $product_id);
    if ($type == 'N') {
        $type = Registry::get('addons.features_as_options.word_for_child');
    } else if ($type == 'Y') {
        $type = Registry::get('addons.features_as_options.word_for_parent');
    }
    return $type;
}

function fn_exim_set_parent_relation($product_id, $data) {
    if (!empty($data)) {
        if ($data == Registry::get('addons.features_as_options.word_for_child')) {
            $data = 'N';
        } else if ($data == Registry::get('addons.features_as_options.word_for_parent')) {
            $data = 'Y';
        }
        db_query("UPDATE ?:products SET parent = ?s WHERE product_id = ?i", $data, $product_id);
    }
}

?>