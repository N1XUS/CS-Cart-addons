<?php
    
use Tygh\Registry;
$schema['top']['addons']['items']['nova_poshta_renew_cities'] = array(
    'href' => 'novaposhta.renew_cities?cron_password=' . Registry::get('addons.novaposhta.cron_password') . '&redirect_url=%CURRENT_URL',
    'position' => 200
);

$schema['top']['addons']['items']['nova_poshta_renew_regions'] = array(
    'href' => 'novaposhta.renew_regions?redirect_url=%CURRENT_URL',
    'position' => 100
);

return $schema;
