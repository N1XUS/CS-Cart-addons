<?php
    
use Tygh\Registry;

function fn_abandoned_cart_get_cron_url_info() {
    $cron_password = Registry::get('addons.abandoned_cart_reminder.cron_password');
    $replacements = array(
        '[dir_root]' => Registry::get('config.dir.root') . '/' . Registry::get('config.admin_index'),
        '[cron_password]' => Registry::get('addons.abandoned_cart_reminder.cron_password'),
        '[url_path]' => fn_url('cart_reminder.send?cron_password=' . $cron_password, 'A', 'current')
    );
    return __("addons.abandoned_cart_reminder_cron_url", $replacements);
}

?>