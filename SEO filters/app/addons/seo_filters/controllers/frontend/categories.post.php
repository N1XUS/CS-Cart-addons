<?php
    
use Tygh\Registry;
    
if ($mode == 'view') {
    $combination_id = Registry::get('runtime.current_filter_combination');
    if (!empty($combination_id)) {
        $category_data = Tygh::$app['view']->getTemplateVars('category_data');
        if (!empty($category_data)) {
            // Get combination data
            $combination_data = fn_get_filter_combination_data($combination_id);
            
            if (!empty($combination_data)) {
                $override_seo_canonical = array();
                $search = Tygh::$app['view']->getTemplateVars('search');
            
                if ($search['total_items'] > $search['items_per_page']) {
                    $pagination = fn_generate_pagination($search);
            
                    if (!empty($pagination['prev_page'])) {
                        $override_seo_canonical['prev'] = fn_url('categories.view?category_id=' . $_REQUEST['category_id'] . '&features_hash=' . $_REQUEST['features_hash'] . '&page=' . $pagination['prev_page']);
                    }
                    if (!empty($pagination['next_page'])) {
                        $override_seo_canonical['next'] = fn_url('categories.view?category_id=' . $_REQUEST['category_id'] . '&features_hash=' . $_REQUEST['features_hash'] . '&page=' . $pagination['next_page']);
                    }
                }
            
                $override_seo_canonical['current'] = fn_url('categories.view?category_id=' . $_REQUEST['category_id'] . '&features_hash=' . $_REQUEST['features_hash']);
                Tygh::$app['view']->assign('override_seo_canonical', $override_seo_canonical);
            }
            
            $combination_name = (!empty($combination_data['combination_seo_name'])) ? $combination_data['combination_seo_name'] : $combination_data['combination_name'];
            
            $replacements = array(
                '%category%' => $category_data['category'],
                '%variant%' => $combination_name
            );
            if (!empty($combination_data['category_display_name'])) {
                $category_data['category'] = str_replace(array_keys($replacements), $replacements, $combination_data['category_display_name']);
            }
            if (!empty($combination_data['description'])) {
                $category_data['description'] = str_replace(array_keys($replacements), $replacements, $combination_data['description']);
            } else {
                $category_data['description'] = '';
            }
            
            Tygh::$app['view']->assign('category_data', $category_data);

            if (!empty($combination_name)) {
                fn_add_breadcrumb($category_data['category'], (empty($_REQUEST['features_hash'])) ? '' : 'categories.view?category_id=' . $category_data['category_id'] . '&features_hash=' . $_REQUEST['features_hash']);
            }

            // If page title for this combination is exist than assign it to template
            if (!empty($combination_data['page_title'])) {
                Tygh::$app['view']->assign('page_title', str_replace(array_keys($replacements), $replacements, $combination_data['page_title']));
            } else {
                Tygh::$app['view']->assign('page_title', '');
            }
            if (!empty($combination_data['meta_description'])) {
                Tygh::$app['view']->assign('meta_description', str_replace(array_keys($replacements), $replacements, $combination_data['meta_description']));
            } else {
                Tygh::$app['view']->assign('meta_description', '');
            }
            if (!empty($combination_data['meta_keywords'])) {
                Tygh::$app['view']->assign('meta_keywords', str_replace(array_keys($replacements), $replacements, $combination_data['meta_keywords']));
            } else {
                Tygh::$app['view']->assign('meta_keywords', '');
            }
        }        
    }
}