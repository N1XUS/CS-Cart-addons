<?php
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_promotions',
    'get_product_data_post'
);
