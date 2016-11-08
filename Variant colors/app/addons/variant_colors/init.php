<?php
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_product_features',
    'get_product_feature_data_before_select',
    'get_product_features_list_before_select',
    'get_product_features_list_post',
    'get_product_feature_variants',
    'get_filters_products_count_before_select_filters'
);