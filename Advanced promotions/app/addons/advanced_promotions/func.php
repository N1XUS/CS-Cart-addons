<?php
    
function fn_advanced_promotions_install() {
    $promotions = db_get_array("SELECT promotion_id, conditions FROM ?:promotions");
    if (!empty($promotions)) {
        foreach($promotions as $promotion) {
            $condition = unserialize($promotion['conditions']);
            $product_ids = array();
            if (!empty($condition['conditions'])) {
                foreach((array) $condition['conditions'] as $_condition) {
                    if ($_condition['condition'] == 'products' && $_condition['operator'] == 'in') {
                        if (!empty($_condition['value'])) {
                            if (is_array($_condition['value'])) {
                                foreach($_condition['value'] as $_v) {
                                    $product_ids[] = $_v['product_id'];
                                }
                            } else {
                                $product_ids[] = $_condition['value'];
                            }
                        }
                    }
                }
            }
            if (!empty($product_ids)) {
                $product_ids = implode(',', array_unique(explode(',', implode(',', $product_ids))));
                $db_data = array(
                    'promotion_id' => $promotion['promotion_id'],
                    'product_ids' => $product_ids
                );
                db_query("REPLACE INTO ?:promotion_products ?e", $db_data);
            }
        }
    }
}

/* HOOKS */

function fn_advanced_promotions_get_promotions(&$params, &$fields, &$sortings, &$condition, &$join) {
    if (!empty($params['product_ids'])) {
        $join .= db_quote(" LEFT JOIN ?:promotion_products ON ?:promotions.promotion_id = ?:promotion_products.promotion_id");
        $arr = (strpos($params['product_ids'], ',') !== false || !is_array($params['product_ids'])) ? explode(',', $params['product_ids']) : $params['product_ids'];
        $condition .= db_quote(" AND (" . fn_find_array_in_set($arr, '?:promotion_products.product_ids') . ")");
    }
    if (!empty($params['or_promotion_id'])) {
        $condition .= db_quote(' OR ?:promotions.promotion_id IN (?n)', $params['or_promotion_id']);
    }
}

function fn_advanced_promotions_get_product_data_post(&$product_data, &$auth, &$preview, &$lang_code) {
    if (!empty($product_data)) {
        $params = array (
            'active' => true,
            'get_hidden' => false,
            'product_ids' => $product_data['product_id'],
            'sort_by' => 'priority',
            'sort_order' => 'desc'
        );
        if (!empty($product_data['promotions'])) {
            $params['or_promotion_id'] = array_keys($product_data['promotions']);
        }
        list($promotions, $search) = fn_get_promotions($params);
        if (!empty($promotions)) {
            foreach($promotions as $id => $promotion) {
                $promotions[$id]['main_pair'] = fn_get_image_pairs($id, 'promotion', 'M', true, true, CART_LANGUAGE);
                $promotions[$id]['list_pair'] = fn_get_image_pairs($id, 'promotion_list', 'M', true, true, CART_LANGUAGE);
            }
            $product_data['promotions_list'] = $promotions;
        }
    }
}

function fn_advanced_promotions_delete_promotion($promotion_ids) {
    if (!is_array($promotion_ids)) {
        $promotion_ids = array($promotion_ids);
    }

    if (fn_allowed_for('ULTIMATE')) {
        foreach ($promotion_ids as $promotion_id => $promotion) {
            if (!fn_check_company_id('promotions', 'promotion_id', $promotion)) {
                unset($promotion_ids[$promotion_id]);
            }
        }
    }

    foreach ($promotion_ids as $pr_id) {
        db_query("DELETE FROM ?:promotion_products WHERE promotion_id = ?i", $pr_id);
    }    
}