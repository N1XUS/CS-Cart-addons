<?php
    
use Tygh\Registry;
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_route',
    'seo_url_before_pager',
    'sitemap_item'
);

Registry::registerCache('spfilters_static', array('spfilters'), Registry::cacheLevel('static'), true);