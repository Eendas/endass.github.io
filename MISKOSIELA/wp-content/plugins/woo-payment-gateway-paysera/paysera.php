<?php
/*
  Plugin Name: WooCommerce Payment Gateway - Paysera
  Plugin URI: https://www.paysera.com
  Text Domain: paysera
  Description: Paysera offers payment and delivery gateway services for your e-shops
  Version: 3.2.4
  Requires PHP: 7.1
  Author: Paysera
  Author URI: https://www.paysera.com
  License: GPL version 3 or later - http://www.gnu.org/licenses/gpl-3.0.html

  WC requires at least: 3.0.0
  WC tested up to: 6.8.0

  @package WordPress
  @author Paysera (https://www.paysera.com)
  @since 2.0.0
 */

defined('ABSPATH') || exit;

define('PayseraPluginUrl', plugin_dir_url(__FILE__));
define('PayseraPluginPath', basename(dirname( __FILE__ )));

use Paysera\Admin\PayseraDeliveryAdmin;
use Paysera\Action\PayseraDeliveryActions;
use Paysera\Front\PayseraDeliveryFrontHtml;
use Paysera\Entity\PayseraDeliverySettings;
use Paysera\PayseraInit;
use Paysera\Admin\PayseraAdmin;
use Paysera\Admin\PayseraPaymentAdmin;
use Paysera\Entity\PayseraPaymentSettings;
use Paysera\Entity\PayseraPaths;
use Paysera\Action\PayseraPaymentActions;

require_once 'vendor/autoload.php';

const PAYSERA_MIN_REQUIRED_PHP_VERSION = '7.1';

if (version_compare(PHP_VERSION, PAYSERA_MIN_REQUIRED_PHP_VERSION, '>=')) {
    (new PayseraInit())->build();
    (new PayseraAdmin())->build();
    (new PayseraDeliveryAdmin())->build();
    (new PayseraPaymentAdmin())->build();
    (new PayseraDeliveryActions())->build();
    (new PayseraDeliveryFrontHtml())->build();
    (new PayseraPaymentActions())->build();
    register_deactivation_hook(__FILE__, 'removePayseraSettings');
    register_uninstall_hook(__FILE__, 'removePayseraSettings');
} else {
    add_action('admin_notices', 'payseraPhpNotice');
}

function removePayseraSettings()
{
    delete_option(PayseraPaymentSettings::MAIN_SETTINGS_NAME);
    delete_option(PayseraPaymentSettings::EXTRA_SETTINGS_NAME);
    delete_option(PayseraPaymentSettings::STATUS_SETTINGS_NAME);
    delete_option(PayseraPaymentSettings::PROJECT_ADDITIONS_SETTINGS_NAME);
    delete_option(PayseraDeliverySettings::SETTINGS_NAME);
    delete_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_SETTINGS_NAME);
    delete_option(PayseraDeliverySettings::DELIVERY_GATEWAYS_TITLES);
}

function payseraPhpNotice()
{
    print_r(
        '<div class="error"><p>' . __('Paysera plugin requires at least PHP ', PayseraPaths::PAYSERA_TRANSLATIONS)
        . PAYSERA_MIN_REQUIRED_PHP_VERSION . '</p></div>'
    );
}
