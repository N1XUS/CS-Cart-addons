<?php

namespace Tygh\UpgradeCenter\Connectors\SmsgatewayMv;

use Tygh\Addons\SchemesManager;
use Tygh\Registry;
use Tygh\UpgradeCenter\Connectors\IConnector as UCInterface;
/**
 * Core upgrade connector interface
 */
class Connector implements UCInterface
{
    /**
     * Add-on connector settings
     *
     * @var array $settings
     */
    protected $settings = array();
    /**
     * Prepares request data for request to Upgrade server (Check for the new upgrades)
     *
     * @return array Prepared request information
     */
    public function getConnectionData()
    {
        $request_data = array(
            'method' => 'get',
            'url' => $this->settings['upgrade_server'],
            'data' => array(
                'dispatch' => 'updates.check',
                'product_version' => PRODUCT_VERSION,
                'edition' => PRODUCT_EDITION,
                'addon' => $this->settings['addon_id'],
                'lang_code' => CART_LANGUAGE,
                'license_number' => $this->settings['license_number'],
                'addon_version' => $this->settings['addon_version'],
            ),
            'headers' => array(
                'Content-type: text/xml'
            )
        );
        return $request_data;
    }
    /**
     * Processes the response from the Upgrade server.
     *
     * @param  string $response            server response
     * @param  bool   $show_upgrade_notice internal flag, that allows/disallows Connector displays upgrade notice (A new version of [product] available)
     * @return array  Upgrade package information or empty array if upgrade is not available
     */
    public function processServerResponse($response, $show_upgrade_notice)
    {
        
        $parsed_data = array();
        $data = simplexml_load_string($response);
        if ((string) $data->available == 'Y') {
            $parsed_data = array(
                'file' => (string) $data->package->file,
                'name' => (string) $data->package->name,
                'package_id' => (string) $data->package->package_id,
                'description' => (string) $data->package->description,
                'from_version' => (string) $data->package->from_version,
                'to_version' => (string) $data->package->to_version,
                'timestamp' => (int) $data->package->timestamp,
                'size' => (int) $data->package->size,
                'md5' => (string) $data->package->md5,
            );
            if ($show_upgrade_notice) {
                fn_set_notification('W', __('notice'), __('text_upgrade_available', array(
                    '[product]' => 'Upgade add-on',
                    '[link]' => fn_url('upgrade_center.manage')
                )), 'S');
            }
        }
        
        return $parsed_data;
    }
    /**
     * Downloads upgrade package from the Upgade server
     *
     * @param  array  $schema       Package schema
     * @param  string $package_path Path where the upgrade pack must be saved
     * @return bool   True if upgrade package was successfully downloaded, false otherwise
     */
    public function downloadPackage($schema, $package_path)
    {
        
        $data = fn_get_contents($this->settings['upgrade_server'] . '?dispatch=updates.get_package&package_id=' . $schema['package_id'] . '&edition=' . PRODUCT_EDITION . '&license_number=' . $this->settings['license_number']);
        
        if (!empty($data)) {
            fn_put_contents($package_path, $data);
        } else {
            array(false, __('text_uc_cant_download_package'));
        }

        // Make some checking
        if ($schema['md5'] != md5_file($package_path)) {
            return array(false, __('text_uc_broken_package'));
            
        } else {
            return array(true, '');
        }
    }
    public function __construct()
    {
        
        // Initial settings
        $addon_scheme = SchemesManager::getScheme('smsgateway_mv');
        $this->settings = array(
            'upgrade_server' => 'http://cs.pervolo.com/index.php',
            'addon_id' => $addon_scheme->getId(),
            'license_number' => Registry::get('addons.smsgateway_mv.license_key'),
            'addon_name' => $addon_scheme->getName(),
            'addon_version' => $addon_scheme->getVersion()
        );
    }
}