<?php
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
fn_register_hooks(
    'update_product_post',
    'settings_variants_image_verification_use_for'
);
