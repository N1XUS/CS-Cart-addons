<?php
    
$schema['promotion_info'] = array(
    'templates' => 'addons/advanced_promotions/blocks/promotions',
    'wrappers' => 'blocks/wrappers',
    'cache' => false,
);

$schema['main']['cache_overrides_by_dispatch']['products.view']['update_handlers'][] = 'promotions';
$schema['main']['cache_overrides_by_dispatch']['products.view']['update_handlers'][] = 'promotion_descriptions';
    
return $schema;