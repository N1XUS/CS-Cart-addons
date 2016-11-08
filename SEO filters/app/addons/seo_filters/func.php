<?php
    
use Tygh\Registry;
use Tygh\Database;
use Tygh\Navigation\LastView;
use Tygh\Enum\ProductFeatures;

function fn_get_seo_filter_name($combination_id, $lang_code = CART_LANGUAGE) {
    return db_get_field("SELECT combination_name FROM ?:seo_filter_descriptions WHERE combination_id = ?i AND lang_code = ?s", $combination_id, $lang_code);
}

function fn_get_seo_filters($params, $lang_code = CART_LANGUAGE) {
    $view_type = 'seo_filters';
    // Init filter
    $params = LastView::instance()->update($view_type, $params);
    
    $default_params = array(
        'items_per_page' => 0,
        'page' => 1,
    );
    $params = array_merge($default_params, $params);
    
    $sortings = array(
        'id' => '?:seo_filters.combination_id',
        'seo_name' => '?:seo_filters.seo_name',
        'name' => '?:seo_filter_descriptions.combination_name',
        'category' => '?:category_descriptions.category',
        'combination_seo_name' => '?:seo_filter_descriptions.combination_seo_name'
    );
    
    $condition = $limit = '';
    
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }
    
    $sorting = db_sort($params, $sortings, 'id', 'asc');
    
    $fields = array(
        '?:seo_filters.combination_id',
        '?:seo_filters.seo_name',
        '?:seo_filters.price_from',
        '?:seo_filters.price_to',
        '?:seo_filters.combination_hash',
        '?:seo_filters.category_ids',
        '?:seo_filter_descriptions.combination_name',
        '?:seo_filter_descriptions.combination_seo_name',
        '?:category_descriptions.category'
    );
    
    $joins = '';
    $joins .= db_quote("LEFT JOIN ?:seo_filter_descriptions ON ?:seo_filters.combination_id = ?:seo_filter_descriptions.combination_id AND ?:seo_filter_descriptions.lang_code = ?s", $lang_code);
    $joins .= db_quote(" LEFT JOIN ?:category_descriptions ON ?:seo_filters.category_ids = ?:category_descriptions.category_id AND ?:category_descriptions.lang_code = ?s", $lang_code);
    
    if (!empty($params['combination_id'])) {
        $condition .= db_quote(" AND ?:seo_filters.combination_id = ?i", $params['combination_id']);
    }
    
    if (!empty($params['combination_name'])) {
        $condition .= db_quote(" AND ?:seo_filter_descriptions.combination_name LIKE ?l", '%' . $params['combination_name'] . '%');
    }
    
    if (!empty($params['seo_name'])) {
        $condition .= db_quote(" AND ?:seo_filters.seo_name LIKE ?l", '%' . $params['seo_name'] . '%');
    }
    
    if (!empty($params['category_ids'])) {
        $condition .= db_quote(" AND ?:seo_filters.category_ids = ?i", $params['category_ids']);
    }
    
    if (!empty($params['empty_combinations']) && $params['empty_combinations'] == 'Y') {
        $condition .= db_quote(" AND ?:seo_filters.combination_hash = ''");
    }
    
    if (!empty($params['filter_variants'])) {
        $combinations = array();
        foreach($params['filter_variants'] as $filter_id => $variants) {
            foreach($variants as $variant) {
                if (empty($variant)) {
                    continue;
                }
                $combinations[] = $filter_id . '-' . $variant;
            }
        }
        if (!empty($combinations)) {
            $condition .= db_quote(" AND ?p", fn_seo_filters_find_array_in_set($combinations, '?:seo_filters.combination_hash'));
        }
    }
    
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:seo_filters.combination_id)) FROM ?:seo_filters ?p WHERE 1 ?p ?p", $joins, $condition, $sorting);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    
    $result = db_get_array("SELECT ?p FROM ?:seo_filters ?p WHERE 1 ?p ?p ?p", implode(', ', $fields), $joins, $condition, $sorting, $limit);
    
    LastView::instance()->processResults($view_type, $result, $params);
    return array($result, $params);
}

function fn_generate_seo_filter($variant_id, $feature_id, $lang_code, $category_id = '') {
    Registry::set('spfilters_static', array());
    $combination_name = db_get_field("SELECT variant FROM ?:product_feature_variant_descriptions WHERE variant_id = ?i", $variant_id);
    if (empty($combination_name)) {
        return false;
    }
    
    $data = array(
        'category_display_name' => Registry::get('addons.seo_filters.default_category_display_name'),
        'page_title' => Registry::get('addons.seo_filters.default_page_title'),
        'meta_description' => Registry::get('addons.seo_filters.default_meta_description'),
        'meta_keywords' => Registry::get('addons.seo_filters.default_meta_keywords'),
        'combinations' => array(
            $feature_id => array($variant_id)
        ),
        'category_ids' => '',
        'combination_name' => $combination_name
    );
    list($data['seo_name'], $data['category_ids']) = fn_autogenerate_seo_name($combination_name, $category_id, $lang_code);
    
    if (empty($data['seo_name'])) {
        return false;
    }
    $data['autogenerated'] = 'Y';
    $combination_id = fn_update_seo_filter($data, false, $lang_code);
    
    if ($combination_id) {
        $seo_name = db_get_field("SELECT seo_name FROM ?:seo_filters WHERE combination_id = ?i", $combination_id);
        return $seo_name;
    }
    return false;
}

function fn_autogenerate_seo_name($name, $category_id, $lang_code) {
    $index = 0;
    $default_name = fn_generate_name($name, '', 0);
    $exist_in_category = db_get_field("SELECT category_ids FROM ?:seo_filters WHERE seo_name = ?s AND category_ids = ?i", $default_name, $category_id);

    $return = array(
        fn_seo_filters_generate_name($default_name, $exist_in_category, false, $index, $lang_code, true, $category_id),
        $exist_in_category
    );
    return($return);
}

function fn_update_seo_filter($data, $combination_id, $lang_code = DESCR_SL) {
    // Generate combinations hash
    Registry::set('spfilters_static', array());
    if (!empty($data['price_from']) || !empty($data['price_to'])) {
        $data['price_from'] = intval($data['price_from']);
        $data['price_to'] = intval($data['price_to']);
        $price_filter_id = db_get_field("SELECT filter_id FROM ?:product_filters WHERE field_type = ?s", 'P');
        if ($price_filter_id) {
            if (empty($data['price_from'])) {
                $data['price_from'] = 0;
            }
            if (empty($data['price_to'])) {
                $data['price_to'] = 100000000;
            }
            if ($data['price_from'] == $data['price_to']) {
                $data['price_to'] = $data['price_to'] + 1;
            } elseif($data['price_from'] > $data['price_to']) {
                $price_to = $data['price_to'];
                $data['price_to'] = $data['price_from'];
                $data['price_from'] = $price_to;
            }
            $data['combinations'][$price_filter_id] = array($data['price_from'] . '-' . $data['price_to'] . '-' . CART_PRIMARY_CURRENCY);
        }
        $data['currency_code'] = CART_PRIMARY_CURRENCY;
    }
    $hash = array();
    if (!empty($data['combinations'])) {
        foreach($data['combinations'] as $filter_id => $variants) {
            asort($variants);
            $data['combinations'][$filter_id] = $variants;
            foreach($variants as $k => $variant_id) {
                if ($variant_id) {
                    $hash[] = $filter_id . '-' . $variant_id;
                } else {
                    unset($data['combinations'][$filter_id][$k]);
                }
            }
        }
    }
    
    if ($combination_id == true && empty($data['combinations'])) {
        if (AREA == 'A') {
            fn_set_notification('W', __('warning'), __('addons.seo_filters.no_combinations_defined'));
            return $combination_id;
        }
        return false;
    }
    
    $_combination_data = $data;
    $index = 0;
    if (!empty($data['seo_name'])) {
        $_combination_data['seo_name'] = fn_seo_filters_generate_name($data['seo_name'], $data['category_ids'], $combination_id, $index, $lang_code);
    } else {
        // Generate seo name from combination name
        $_combination_data['seo_name'] = fn_seo_filters_generate_name($data['combination_name'], $data['category_ids'], $combination_id, $index, $lang_code);
    }
    
    asort($hash);
    
    unset($_combination_data['combinations']);
    $_combination_data['combination_hash'] = implode($hash, ',');
    
    // Check if hash already exists in database
    
    if ($data['category_ids']) {
        $_condition = db_quote("AND (category_ids = ?i OR category_ids = '')", $data['category_ids']);
    } else {
        $_condition = db_quote("AND (category_ids = '')");
    }
    $hash_exist = db_get_field("SELECT combination_id FROM ?:seo_filters WHERE combination_hash = ?s AND (category_ids = ?i)", $_combination_data['combination_hash'], $data['category_ids']);
    if ($hash_exist && ($hash_exist != $combination_id)) {
        if (AREA == 'A') {
            $url = fn_url('seo_filters.update?combination_id=' . $hash_exist);
            fn_set_notification('W', __('warning'), __('addons.seo_filters.combination_exists', array('[URL]' => $url)));
            if ($combination_id) {
                return $combination_id;
            }
            return false;
        }
    }
    
    if ($combination_id) {
        $old_seo_name = db_get_row("SELECT seo_name, category_ids FROM ?:seo_filters WHERE combination_id = ?i", $combination_id);
        if ($data['seo_create_redirect'] == true && ($old_seo_name['seo_name'] != $_combination_data['seo_name'])) {
            if (!empty($old_seo_name['seo_name']) && !empty($old_seo_name['category_ids'])) {
                $src = fn_seo_filters_get_seo_parent_uri($old_seo_name['category_ids'], $lang_code);
                $src = $src['prefix'];
                $dest = fn_seo_filters_get_seo_parent_uri($_combination_data['category_ids'], $lang_code);
                $dest = $dest['prefix'];
                
                if ($src != '/' && $dest != '/') {
                    $redirect_data = array(
                        'src' => $src . $old_seo_name['seo_name'],
                        'dest' => $dest . $_combination_data['seo_name']
                    );
                    
                    fn_seo_update_redirect($redirect_data, 0);                    
                }
            }
        }
        
        db_query("UPDATE ?:seo_filters SET ?u WHERE combination_id = ?i", $_combination_data, $combination_id);
        db_query("UPDATE ?:seo_filter_descriptions SET ?u WHERE combination_id = ?i AND lang_code = ?s", $_combination_data, $combination_id, $lang_code);
        db_query("DELETE FROM ?:seo_filter_combinations WHERE combination_id = ?i", $combination_id);
    } else {
        $combination_id = $_combination_data['combination_id'] = db_query("INSERT INTO ?:seo_filters ?e", $_combination_data);
        foreach(fn_get_translation_languages() as $_combination_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:seo_filter_descriptions ?e", $_combination_data);
        }
    }
    if (!empty($data['combinations'])) {
        foreach($data['combinations'] as $filter_id => $variants) {
            foreach($variants as $variant_id) {
                $combination_data = array(
                    'combination_id' => $combination_id,
                    'filter_id' => $filter_id,
                    'variant_id' => $variant_id
                );
                db_query("INSERT INTO ?:seo_filter_combinations ?e", $combination_data);
            }
        }        
    }
    return $combination_id;
}

function fn_get_filter_combination_data($combination_id, $lang_code = DESCR_SL) {
    $combination_data = db_get_row("SELECT * FROM ?:seo_filters AS a LEFT JOIN ?:seo_filter_descriptions AS b ON a.combination_id = b.combination_id AND b.lang_code = ?s WHERE a.combination_id = ?i", $lang_code, $combination_id);
    if (!empty($combination_data)) {
        // Get variants
        $combinations = db_get_array("SELECT filter_id, variant_id FROM ?:seo_filter_combinations WHERE combination_id = ?i ORDER BY variant_id ASC", $combination_id);
        if (!empty($combinations)) {
            foreach($combinations as $v) {
                $combination_data['combinations'][$v['filter_id']][] = $v['variant_id'];
            }
        }
    }
    return $combination_data;
}

function fn_seo_filters_generate_name($name, $category_ids = '', $combination_id, $index = 0, $lang_code = DESCR_SL, $auto = false, $_category_id = '') {
    // Check if we already have name for this combination in db
    $category_ids = (!empty($category_ids)) ? $category_ids : '';
    $_seo_name = db_get_field("SELECT seo_name FROM ?:seo_filters WHERE combination_id = ?i", $combination_id);
    $seo_name = fn_generate_name($name, '', 0);

    if ($index > 0) {
        if ($index == 1) {
            $seo_name = $seo_name . SEO_DELIMITER . $lang_code;
        } else {
            $seo_name = preg_replace("/-\d+$/", "", $seo_name) . SEO_DELIMITER . $index;
        }        
    }

    $condition = '';
    $condition .= db_quote("AND seo_name = ?s", $seo_name);
    $condition .= db_quote(" AND combination_id != ?i", $combination_id);
    
    if (!empty($category_ids)) {
        $condition .= db_quote(" AND (category_ids = ?i OR category_ids = '')", $category_ids);
    } else {
        $condition .= db_quote(" AND category_ids = ''");
    }
    
    $exist = db_get_field("SELECT combination_id FROM ?:seo_filters WHERE 1 ?p", $condition);

    if (!empty($exist)) {
        $index++;
        $seo_name = fn_seo_filters_generate_name($seo_name, $category_ids, $combination_id, $index, $lang_code);
    } else {
        if ($auto == true && !empty($_category_id)) {
            $exist = db_get_field("SELECT combination_id FROM ?:seo_filters WHERE seo_name = ?s AND category_ids = ?i", $seo_name, $_category_id);
            if (!empty($exist)) {
                $index++;
                $seo_name = fn_seo_filters_generate_name($seo_name, $category_ids, $combination_id, $index, $lang_code, true, $_category_id);                
            } else {
                // Double-check for default seo names
                $exist = db_get_field("SELECT object_id FROM ?:seo_names WHERE name = ?s", $seo_name);
                if ($exist) {
                    $index++;
                    $seo_name = fn_seo_filters_generate_name($seo_name, $category_ids, $combination_id, $index, $lang_code, true, $_category_id);
                }
            }
        } else {
            // Double-check for default seo names
            $exist = db_get_field("SELECT object_id FROM ?:seo_names WHERE name = ?s", $seo_name);
            if ($exist) {
                $index++;
                $seo_name = fn_seo_filters_generate_name($seo_name, $category_ids, $combination_id, $index, $lang_code);
            }            
        }
    }
    return $seo_name;
}

function fn_seo_filters_delete_combination($combination_ids = array()) {
    foreach((array)$combination_ids as $cid) {
        db_query("DELETE FROM ?:seo_filters WHERE combination_id = ?i", $cid);
        db_query("DELETE FROM ?:seo_filter_combinations WHERE combination_id = ?i", $cid);
        db_query("DELETE FROM ?:seo_filter_descriptions WHERE combination_id = ?i", $cid);
    }
    Registry::set('spfilters_static', array());
}

function fn_seo_filters_generate_sitemap_link($category_id, $features_hash, $languages) {
    $links = array();
    
    $link = 'categories.view?category_id=' . $category_id . '&features_hash=' . $features_hash;
    
    if (count($languages) == 1) {
        $links[] = fn_url($link, 'C', fn_get_storefront_protocol(), CART_LANGUAGE);
    } else {
        foreach ($languages as $lang_code => $lang) {
            $links[] = fn_url($link . '&sl=' . $lang_code, 'C', fn_get_storefront_protocol(), $lang_code);
        }
    }
    
    return $links;
}

/* HOOKS */

function fn_seo_filters_sitemap_item(&$sitemap_settings, &$file, &$lmod, &$link_counter, &$file_counter) {
    
    $include_in_sitemap = Registry::get('addons.seo_filters.include_in_sitemap');
    
    if ($include_in_sitemap == "N") {
        return;
    }
    // Get filters for category
    
    $filters = db_get_array("SELECT combination_id, category_ids FROM ?:seo_filters WHERE category_ids != ''");
    
    if (!empty($filters)) {
        fn_set_progress('step_scale', count($filters));
        
        $languages = db_get_hash_single_array("SELECT lang_code, name FROM ?:languages WHERE status = 'A'", array('lang_code', 'name'));
        
        foreach($filters as $filter) {
            
            $categories = explode(',', $filter['category_ids']);

            $combination_hash = db_get_hash_multi_array("SELECT filter_id, variant_id FROM ?:seo_filter_combinations WHERE combination_id = ?i", array('filter_id', 'variant_id', 'variant_id'), $filter['combination_id']);
            
            $combination_hash = fn_generate_filter_hash($combination_hash);

            if (!empty($combination_hash)) {
                foreach($categories as $category_id) {
                    $links = fn_seo_filters_generate_sitemap_link($category_id, $combination_hash, $languages);
                    $item = fn_google_sitemap_print_item_info($links, $lmod, $sitemap_settings['categories_change'], $sitemap_settings['categories_priority']);
                    fn_google_sitemap_check_counter($file, $link_counter, $file_counter, $links, $simple_head, $simple_foot, 'category_filters');
        
                    fwrite($file, $item);
                }
            }        
        }
    }
}

/**
 * Define whether current page should be indexed
 *
 * $indexed_pages's element structure:
 * 'dipatch' => array( 'index' => array('param1','param2'),
 *                      'noindex' => array('param3'),
 *                  )
 * the page can be indexed only if the current dispatch is in keys of the $indexed_pages array.
 * If so, the page is indexed only if param1 and param2 are the keys of the $_REQUEST array and param3 is not.
 * @param array $request
 * @return bool $index_page  indicate whether indexed or not
 */
function fn_seo_filters_is_indexed_page($request)
{
    if (defined('HTTPS') && fn_get_storefront_protocol() == 'http') {
        return false;
    }
    $controller_status = Tygh::$app['view']->getTemplateVars('exception_status');

    if (!empty($controller_status) && $controller_status != CONTROLLER_STATUS_OK) {
        return false;
    }

    $index_schema = fn_get_schema('seo', 'indexation');

    // backward compatibility, since 4.3.1
    $seo_vars = fn_get_seo_vars();
    foreach ($seo_vars as $seo_var) {
        if (!empty($seo_var['indexed_pages'])) {
            $index_schema = fn_array_merge($index_schema, $seo_var['indexed_pages']);
        }
    }

    $controller = Registry::get('runtime.controller');
    $mode = Registry::get('runtime.mode');
    
    if ($_SERVER['X-SEO-FILTERS-ALLOW-INDEXATION']) {
        return true;
    }

    if (isset($index_schema[$controller . '.' . $mode]) && is_array($index_schema[$controller . '.' . $mode])) {

        $dispatch_rules = $index_schema[$controller . '.' . $mode];

        if (empty($dispatch_rules['index']) && empty($dispatch_rules['noindex'])) {
            $index_page = true;
        } else {
            $index_cond = true;
            if (!empty($dispatch_rules['index']) && is_array($dispatch_rules['index'])) {
                $index_cond = false;
                if (sizeof(array_intersect($dispatch_rules['index'], array_keys($request))) == sizeof($dispatch_rules['index'])) {
                    $index_cond = true;
                }
            }

            $noindex_cond = true;
            if (isset($dispatch_rules['noindex'])) {
                if (is_bool($dispatch_rules['noindex'])) {
                    $noindex_cond = false;
                } elseif (is_array($dispatch_rules['noindex'])) {
                    $noindex_cond = false;
                    if (sizeof(array_intersect($dispatch_rules['noindex'], array_keys($request))) == 0) {
                        $noindex_cond = true;
                    }
                }
            }

            $index_page = $index_cond && $noindex_cond;
        }
    } else {
        // All pages that are not listed at schema should be indexed
        $index_page = true;
    }

    return $index_page;
}


/**
 * "get_route" hook implemetation
 * @param array &$req input request
 * @param array &$result result of init function
 * @param string $area current working area
 * @param boolean $is_allowed_url Flag that determines if url is supported
 * @return bool true on success, false on failure
 */    
function fn_seo_filters_get_route(&$req, &$result, &$area, &$is_allowed_url) {
    
    if ($area == 'C') {
        $redirect = false;
        $custom_features_hash = false;
        $uri = fn_get_request_uri($_SERVER['REQUEST_URI']);
        $features_hash = array();
        $category_id = '';
        if (isset($req['features_hash'])) {
            $features_hash = fn_parse_filters_hash($req['features_hash']);
        }
        
        if (!empty($uri)) {
            $_uri = trim($uri, '/');
            
            $pieces = explode('/', $_uri);
            $seo_features_hash = array();
            
            // Get last category
            
            $category_path = $pieces;
            
            $combination_count = 0;
            
            foreach((array) $category_path as $k => $v) {
                if (strpos($v, 'page-') === false) {
                    $combination_id = db_get_field("SELECT combination_id FROM ?:seo_filters WHERE seo_name = ?s", $v);
                    if ($combination_id) {
                        unset($category_path[$k]);
                    }
                } else {
                    unset($category_path[$k]);
                }
            }
            
            if (!empty($category_path)) {
                $current_category = array_pop($category_path);
                $category_id = db_get_field("SELECT object_id FROM ?:seo_names WHERE name =?s AND type = ?s", $current_category, 'c');
            }
        }
        
        $autogenerate = (Registry::get('addons.seo_filters.enable_combination_autogeneration') == "Y") ? true : false;
        
        list($url_path, $_hash_params) = fn_seo_filters_generate_path($features_hash, $autogenerate, true, Registry::get('settings.Appearance.frontend_default_language'), $category_id);
        if (!empty($url_path)) {
            $uri .= '/' . $url_path;
            $_features_hash = $_hash_params;
            $redirect = true;
        }

        if (!empty($uri)) {
            if (!empty($category_id)) {
                $_uri = trim($uri, '/');
                $pieces = explode('/', $_uri);
                
                foreach((array) $pieces as $k => $v) {
                    if (strpos($v, 'page-') === false) {
                        // Check only for seo names, no need for page piece
                        $combination_id = db_get_field("SELECT combination_id FROM ?:seo_filters WHERE seo_name = ?s AND (category_ids = ?i) ORDER BY category_ids", $v, $category_id);
                        if (empty($combination_id)) {
                            $combination_id = db_get_field("SELECT combination_id FROM ?:seo_filters WHERE seo_name = ?s AND category_ids = '' ORDER BY category_ids", $v);
                        }
                        if ($combination_id) {
                            unset($pieces[$k]);

                            // This will be used after
                            Registry::set('runtime.current_filter_combination', $combination_id);
    
                            // Get features hash corresponding to this combination
                            $fhash = db_get_array("SELECT filter_id, variant_id FROM ?:seo_filter_combinations WHERE combination_id = ?i ORDER BY variant_id ASC", $combination_id);
                            if (!empty($fhash)) {
                                $custom_features_hash = true;
                                foreach($fhash as $variant) {
                                    $seo_features_hash[$variant['filter_id']][] = $variant['variant_id'];
                                    $seo_features_hash[$variant['filter_id']] = array_unique($seo_features_hash[$variant['filter_id']]);
                                } 
                            }
                        }
                    }
                }
            }
            
            if (!empty($seo_features_hash) && !empty($features_hash)) {
                Registry::set('runtime.current_filter_combination', false);
            }

            if ($custom_features_hash && empty($features_hash)) {
                $_SERVER['X-SEO-FILTERS-ALLOW-INDEXATION'] = true;
            }

            if ($custom_features_hash == true) {
                $pieces = implode('/', $pieces);
                if (!empty($seo_features_hash)) {
                    if (!empty($features_hash)) {
                        foreach($seo_features_hash as $f_id => $variants) {
                            if (!empty($features_hash[$f_id])) {
                                $features_hash[$f_id] = array_merge($features_hash[$f_id], $seo_features_hash[$f_id]);
                            } else {
                                $features_hash[$f_id] = $seo_features_hash[$f_id];
                            }
                        }
                    } else {
                        $features_hash = $seo_features_hash;
                    }
                    foreach($features_hash as $k => $v) {
                        $features_hash[$k] = array_unique($v);
                    }
                }
                
                $features_hash = fn_generate_filter_hash($features_hash);
                
                if (!empty($features_hash)) {
                    $req['features_hash'] = $features_hash;
                } else {
                    unset($req['features_hash']);
                }
                $url_query = Registry::get('config.current_path') . '/' . $pieces;
                $query_string = http_build_query($req);
                $_SERVER['REQUEST_URI'] = $url_query . '?' . $query_string;
                $_SERVER['QUERY_STRING'] = $query_string;
            }
        }
    }
}

function fn_seo_filters_seo_url_before_pager(&$seo_settings, &$url, &$parsed_url, &$link_parts, &$parsed_query, &$company_id_in_url, &$lang_code) {
    if (isset($parsed_query['features_hash']) && !empty($parsed_query['features_hash'])) {
        $features_hash = $parsed_query['features_hash'];
        $features_hash = fn_parse_filters_hash($features_hash);
        
        if (!empty($features_hash)) {
            $price_present = db_get_field("SELECT filter_id FROM ?:product_filters WHERE field_type = ?s AND filter_id IN(?n)", 'P', array_keys($features_hash));
            if (isset($features_hash[$price_present])) {
                $features_hash[$price_present] = array_unique($features_hash[$price_present]);
            }
        }
        
        $autogenerate = (Registry::get('addons.seo_filters.enable_combination_autogeneration') == "Y") ? true : false;

        list($url_parts, $_parsed_query) = fn_seo_filters_generate_path($features_hash, $autogenerate, true, $lang_code, $parsed_query['category_id']);
        
        if (!empty($url_parts)) {
            $link_parts['name'] .= $url_parts;
        }
        
        if (!empty($_parsed_query)) {
            $parsed_query['features_hash'] = $_parsed_query;
        } else {
            unset($parsed_query['features_hash']);
        }
    }
}

function fn_seo_filters_generate_path($features_hash, $generate_missing = false, $skip_cache = false, $lang_code = '', $category_id = '') {
    if (empty($features_hash)) {
        return;
    }
    
    $link_parts = '';
    $parsed_query = '';
    $url_parts = array();
    
    // Generate combination hash
    $combination_hash = array();
    $_combination_hash = array();
    
    $price_present = db_get_field("SELECT filter_id FROM ?:product_filters WHERE field_type = ?s AND filter_id IN(?n)", 'P', array_keys($features_hash));
    
    foreach($features_hash as $filter_id => $variants) {
        if ($filter_id == $price_present) {
            $currency_code = array_pop($variants);
            $combination_hash[] = $filter_id . '-' . implode('-', $variants) . '-' . $currency_code;
        } else {
            foreach($variants as $variant) {
                $combination_hash[] = $filter_id . '-' . $variant;
            }            
        }
    }

    if (empty($combination_hash)) {
        return;
    }
    
    asort($combination_hash);
    
    $registry_hash_key = $combination_hash;

    $registry_hash_key = 'spfilters_' . md5(implode('|', $registry_hash_key));
    
    $cached = Registry::get('spfilters_static.' . $registry_hash_key);
    
    if (!empty($cached) && $skip_cache == true) {
        return $cached;
    }
    
    if (count($combination_hash) < 2) {
        $combination_hash = $combination_hash[0];
    }
    if (is_array($combination_hash)) {
        $condition = db_quote("combination_hash = ?s", implode(',', $combination_hash));
    } else {
        $condition = db_quote("combination_hash = ?s", $combination_hash);
    }
    
    $combination_url = db_get_field("SELECT seo_name, combination_id FROM ?:seo_filters WHERE ?p AND (category_ids = ?i OR category_ids = '') ORDER BY category_ids DESC", $condition, $category_id);

    if (!empty($combination_url)) {
        $parsed_query = '';
        $link_parts .= $combination_url . '/';
    } else {
        // If we can't get exact combination, try to split combination into smaller ones
        if (is_array($combination_hash)) {
            $condition = fn_seo_filters_find_array_in_set($combination_hash, 'combination_hash', ' OR ');
        } else {
            $condition = db_quote("combination_hash = ?s", $combination_hash);
        }
        
        if (!empty($category_id)) {
            $condition .= db_quote(" AND category_ids = ?i", $category_id);
        } else {
            $condition .= db_quote(" AND category_ids = ''");
        }
        
        $select = array();
        
        foreach((array)$combination_hash as $v) {
            $select[] = db_quote("IF(?p, ?s, ?s)", fn_seo_filters_find_array_in_set((array)$v, 'combination_hash'), $v, '');
        }
        $select = implode(', ', $select);
        $select = db_quote("CONCAT_WS(',', ?p) AS combination_key", $select);

        if (empty($combination_urls)) {
            $combination_urls = db_get_hash_array("SELECT seo_name, ?p, combination_hash, (LENGTH(combination_hash) - LENGTH(REPLACE(combination_hash, ',', ''))) AS priority FROM ?:seo_filters WHERE ?p ORDER BY priority desc", 'combination_hash', $select, $condition);
        }
        
        if (!empty($combination_urls)) {
            
            // Remove garbage
            foreach($combination_urls as $k => $v) {
                if (!isset($combination_urls[$k])) {
                    continue;
                }
                $_hash = explode(',', $v['combination_hash']);
                $combination_key = explode(',', $v['combination_key']);
                foreach((array)$combination_key as $_k => $_v) {
                    if (empty($_v)) {
                        unset($combination_key[$_k]);
                    }
                }
                $combination_urls[$k]['combination_key'] = $combination_key;
                foreach($_hash as $variant_id) {
                    if (!in_array($variant_id, $combination_key)) {
                        unset($combination_urls[$k]);
                        continue;
                    }
                }
            }
            
            foreach($combination_urls as $k => $v) {
                $_hash = explode(',', $v['combination_hash']);
                foreach($_hash as $_k => $_v) {
                    list($_filter_id, $_variant_id) = explode('-', $_v);
                    $_combination_hash[$_filter_id][] = $_variant_id;
                    $_combination_hash[$_filter_id] = array_unique($_combination_hash[$_filter_id]);
                }
            }
            
            if ($_combination_hash) {
                foreach($_combination_hash as $filter_id => $variants) {
                    if (!isset($features_hash[$filter_id])) {
                        unset($_combination_hash[$filter_id]);
                    }
                }
                
                $__combination_urls = array();
    
                foreach($combination_urls as $k => $comb) {
                    if (!isset($combination_urls[$k])) {
                        continue;
                    }
                    // Remove garbage combinations
                    $_filters = explode(',', $k);
                    $_hash = array();
                    foreach((array)$_filters as $v) {
                        list($filter_id, $variant_id) = explode('-', $v);
                        if (!isset($_combination_hash[$filter_id])) {
                            unset($combination_urls[$k]);
                            continue;
                        }
                        $_hash[] = $v;
                        $__hash = implode($_hash, ',');
                        if (isset($combination_urls[$__hash]) && $__hash != $k) {
                            unset($combination_urls[$__hash]);
                        }
                        if (isset($combination_urls[$v]) && $v != $k) {
                            unset($combination_urls[$v]);
                        }
                    }
                    if (isset($combination_urls[$k])) {
                        $__combination_urls[$k] = $comb;
                    }
                }                
            }
        }
        
        if (!empty($__combination_urls)) {
            // Re-arrange combination urls in order of features hash
            
            $combination_urls = array();
    
            foreach($features_hash as $filter_id => $variants) {
                foreach($variants as $variant) {
                    $combination_id = $filter_id . '-' . $variant;
                    if (isset($__combination_urls[$combination_id])) {
                        $combination_urls[$combination_id] = $__combination_urls[$combination_id];
                        unset($__combination_urls[$combination_id]);
                    }
                }
            }
            $__combination_urls = array_merge($__combination_urls, $combination_urls);
            $combination = array_shift($__combination_urls);
            
            $url_parts[] = $combination['seo_name'];
            $filters = explode(',', $combination['combination_hash']);
            foreach((array)$filters as $filter) {
                list($filter_id, $variant) = explode('-', $filter);
                if (isset($features_hash[$filter_id])) {
                    $key = array_search($variant, $features_hash[$filter_id]);
                    if ($key === true) {
                        unset($features_hash[$filter_id][$key]);
                    }                        
                }
            }
        } else {
            // if we didn't found exact combination, generate path from all variants
            foreach($features_hash as $filter_id => $variants) {
                foreach($variants as $k => $variant) {
                    $hash = $filter_id . '-' . $variant;
                    $condition = db_quote("combination_hash = ?s", $hash);
                    
                    $combination_url = db_get_field("SELECT seo_name FROM ?:seo_filters WHERE ?p AND category_ids = ?i", $condition, $category_id);
                    if (empty($combination_url)) {
                        $combination_url = db_get_field("SELECT seo_name FROM ?:seo_filters WHERE ?p AND category_ids = ''", $condition);
                    }
                    if (!empty($combination_url)) {
                        $url_parts[] = $combination_url;
                        unset($features_hash[$filter_id][$k]);
                    }
                }
            }            
        }

        foreach($features_hash as $k => $v) {
            if (empty($v)) {
                unset($features_hash[$k]);
            }
        }
        
        if ($generate_missing == true && count($url_parts) < 1) {
            // If we have features filters, try to generate name for them
            if (!empty($features_hash)) {
                $variant_features = db_get_fields("SELECT filter_id FROM ?:product_filters AS a LEFT JOIN ?:product_features AS b ON a.feature_id = b.feature_id WHERE a.filter_id IN(?n) AND b.feature_type IN(?s, ?s, ?s)", array_keys($features_hash), ProductFeatures::TEXT_SELECTBOX, ProductFeatures::MULTIPLE_CHECKBOX, ProductFeatures::EXTENDED);
                if (!empty($variant_features)) {
                    foreach($variant_features as $feature) {
                        if (!empty($features_hash[$feature])) {
                            foreach($features_hash[$feature] as $k => $variant_id) {
                                $seo_name = fn_generate_seo_filter($variant_id, $feature, $lang_code, $category_id);
                                if (!empty($seo_name)) {
                                    $url_parts[] = $seo_name;
                                    unset($features_hash[$feature][$k]);                                
                                }
                            }
                        }
                    }
                }
            }
            
            foreach($features_hash as $k => $v) {
                if (empty($v)) {
                    unset($features_hash[$k]);
                }
            }            
        }
        if (!empty($features_hash)) {
            $parsed_query = fn_generate_filter_hash($features_hash);
        } else {
            $parsed_query = '';
        }
        if (!empty($url_parts)) {
            $url_parts = implode('/', $url_parts);
            $link_parts .= $url_parts . '/';            
        }
    }
    
    $return = array($link_parts, $parsed_query);
    if ($generate_missing == true) {
        //Registry::set('spfilters_static.' . $registry_hash_key, $return);
    }
   
    return $return;
}

function fn_seo_filters_find_array_in_set($arr, $set, $type = ' AND ', $find_empty = false) {
    $conditions = array();
    if ($find_empty) {
        $conditions[] = "$set = ''";
    }
    if (!empty($arr)) {
        foreach ($arr as $val) {
            $conditions[] = Database::quote("FIND_IN_SET(?s, $set)", $val);
        }
    }

    return empty($conditions) ? '' : implode($type, $conditions);    
}

function fn_seo_filters_attach_filter_link($variant, $filter_id) {
    $features_hash = fn_add_filter_to_hash($_REQUEST['features_hash'], $filter_id, $variant);
    $url = fn_query_remove(Registry::get('config.current_url'), 'page', 'features_hash') . '&features_hash=' . $features_hash;
    return $url;
}

function fn_seo_filters_dettach_filter_link($variant, $filter_id) {
    $features_hash = fn_delete_filter_from_hash($_REQUEST['features_hash'], $filter_id, $variant);
    $url = fn_query_remove(Registry::get('config.current_url'), 'page', 'features_hash') . '&features_hash=' . $features_hash;
    return $url;
}

function fn_seo_filters_get_seo_parent_uri($category_id, $lang_code) {
    $url = fn_generate_seo_url_from_schema(array(
        'object_id' => $category_id,
        'type' => 'c',
        'lang_code' => $lang_code
    ), false);
    $seo_var = fn_get_seo_vars($object_type);

    return array(
        'prefix' => $url . '/',
        'suffix' => fn_check_seo_schema_option($seo_var, 'html_options') ? SEO_FILENAME_EXTENSION : ''
    );
}