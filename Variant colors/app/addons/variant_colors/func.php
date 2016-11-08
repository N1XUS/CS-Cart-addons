<?php
    
function fn_variant_colors_update_product_feature_pre(&$feature_data, &$feature_id, &$lang_code) {
    $gdata = db_get_field("SELECT color FROM ?:product_features WHERE feature_id = ?i", $feature_data['parent_id']);
    if (!empty($gdata)) {
        $feature_data['color'] = $gdata['color'];
    }
}
    
function fn_variant_colors_get_product_features(&$fields) {
    $fields[] = 'pf.color'; 
}
    
function fn_variant_colors_get_product_feature_data_before_select(&$fields) {
    $fields[] = '?:product_features.color';
}

function fn_variant_colors_get_product_features_list_before_select(&$fields, &$join) {
    $fields .= db_quote(", f.color");
}

function fn_variant_colors_get_product_features_list_post(&$features_list) {
    
    if (!empty($features_list)) {
        foreach($features_list as $k => $v) {
            if ($v['color'] == 'Y') {
                if (!empty($v['variants'])) {
                    foreach ($v['variants'] as $_k => $_v) {
                        // Get color for variant
                        $v['variants'][$_k]['color'] = db_get_field("SELECT color FROM ?:product_feature_variants WHERE variant_id = ?i", $_v['variant_id']);
                    }
                }
                $features_list[$k]['variants'] = $v['variants'];         
            }
        }
    }
}

function fn_variant_colors_get_filters_products_count_before_select_filters(&$sf_fields) {
    $sf_fields .= db_quote(", ?:product_features.color");
}

function fn_variant_colors_get_product_feature_variants(&$fields) {
    $fields[] = '?:product_feature_variants.color';
}

function fn_variant_colors_get_color($variant_id) {
    return db_get_field("SELECT color FROM ?:product_feature_variants WHERE variant_id = ?i", $variant_id);
}