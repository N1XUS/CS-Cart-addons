<?php
    
$schema['promotion_pages'] = array(
    'admin_dispatch' => 'promotions.update',
    'customer_dispatch' => 'promotions.view',
    'key' => 'promotion_id',
    'picker' => 'addons/advanced_promotions/pickers/promotions/picker.tpl',
    'picker_params' => array (
        'type' => 'links',
        'multiple' => true
    ),    
);

return $schema;