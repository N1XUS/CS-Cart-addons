<?php
    
use Tygh\Registry;
    
if ($mode == 'view') {
    $sitemap = Tygh::$app['view']->getTemplateVars('sitemap');
    $seo_filters = array();
    if (!empty($sitemap['categories_tree'])) {
        $category_ids = array();
        $category_names = array();
        foreach($sitemap['categories_tree'] as $k => $category) {
            $category_ids[] = $category['category_id'];
            $category_names[$category['category_id']] = $category['category'];
        }
        $_seo_filters = db_get_array("SELECT combination_id, category_ids FROM ?:seo_filters WHERE category_ids IN(?n)", $category_ids);
        if (!empty($_seo_filters)) {
            foreach($_seo_filters as $combination) {
                $combination_data = fn_get_filter_combination_data($combination['combination_id']);
                $replacements = array(
                    '%category%' => $category_names[$combination['category_ids']],
                    '%variant%' => (!empty($combination_data['combination_seo_name'])) ? $combination_data['combination_seo_name'] : $combination_data['combination_name']
                );
                $combination_data['features_hash'] = fn_generate_filter_hash($combination_data['combinations']);
                $combination_data['category_display_name'] = str_replace(array_keys($replacements), $replacements, $combination_data['category_display_name']);
                $seo_filters[$combination['category_ids']][$combination['combination_id']] = $combination_data;
            }
        }
        Tygh::$app['view']->assign('seo_filters', $seo_filters);
    }
}