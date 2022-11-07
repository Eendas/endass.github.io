<?php

declare(strict_types=1);

namespace Paysera;

defined('ABSPATH') || exit;

use WC_Shipping_Rate;
use Paysera\Provider\PayseraPaymentSettingsProvider;
use Paysera\Provider\PayseraDeliverySettingsProvider;
use Paysera\Helper\PayseraDeliveryHelper;
use Paysera\Helper\PayseraDeliveryLibraryHelper;
use Paysera\Admin\PayseraDeliveryAdminHtml;
use Paysera\Entity\PayseraPaths;
use Paysera\Entity\PayseraDeliverySettings;

class PayseraInit
{
    /**
     * @var Entity\PayseraPaymentSettings
     */
    private $payseraPaymentSettings;
    private $payseraDeliveryHelper;
    private $payseraDeliverySettingsProvider;
    private $payseraDeliveryLibraryHelper;
    private $payseraDeliveryAdminHtml;
    private $notices;
    private $errors;

    public function __construct()
    {
        $this->payseraPaymentSettings = (new PayseraPaymentSettingsProvider())->getPayseraPaymentSettings();
        $this->payseraDeliveryHelper = new PayseraDeliveryHelper();
        $this->payseraDeliverySettingsProvider = new PayseraDeliverySettingsProvider();
        $this->payseraDeliveryLibraryHelper = new PayseraDeliveryLibraryHelper();
        $this->payseraDeliveryAdminHtml = new PayseraDeliveryAdminHtml();
        $this->notices = [];
        $this->errors = [];
    }

    public function build()
    {
        add_action('plugins_loaded', [$this, 'loadPayseraPlugin']);
        add_action('admin_notices', [$this, 'displayAdminErrors']);
        add_action('admin_notices', [$this, 'displayAdminNotices']);
        add_filter('woocommerce_payment_gateways', [$this, 'registerPaymentGateway']);
        add_filter('plugin_action_links_' . PayseraPluginPath . '/paysera.php', [$this, 'addPayseraPluginActionLinks']);
        add_action('wp_head', [$this, 'addMetaTags']);
        add_action('wp_head', [$this, 'addQualitySign']);
        add_filter('woocommerce_shipping_methods', [$this, 'registerDeliveryGateways']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'deliveryGatewayLogos'], PHP_INT_MAX, 2);
        add_action('admin_notices', [$this, 'payseraDeliveryPluginNotice']);
        add_action('admin_init', [$this, 'payseraDeliveryPluginNoticeDismiss']);
    }

    public function loadPayseraPlugin(): bool
    {
        load_plugin_textdomain(PayseraPaths::PAYSERA_TRANSLATIONS, false, PayseraPluginPath . '/languages/');

        if (class_exists('woocommerce') === false) {
            $this->addError(__('WooCommerce is not active', PayseraPaths::PAYSERA_TRANSLATIONS));

            return false;
        }

        $activePayseraPlugins = $this->getActivePayseraPlugins();

        if (empty($activePayseraPlugins) === false) {
            if (count($activePayseraPlugins) > 1) {
                $this->addNotice(__('More than 1 Paysera plugin active', PayseraPaths::PAYSERA_TRANSLATIONS));
            }
        }

        return true;
    }

    public function payseraDeliveryPluginNotice(): void
    {
        wp_enqueue_style('paysera-payment-css', PayseraPaths::PAYSERA_PAYMENT_CSS);

        $notice = sprintf(__(
            'NEW! With the latest version, you can integrate delivery options into your e-shop. More about the %s ',
            PayseraPaths::PAYSERA_TRANSLATIONS
        ),
            '<a href="' . admin_url(PayseraPaths::PAYSERA_ADMIN_SETTINGS_LINK) . ' "> '
            . __('Plugin & Services.', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>'
        );

        if (!get_user_meta(wp_get_current_user()->ID, 'paysera_new_delivery_notice')) {
            echo wp_kses(
                '<div class="notice notice-info"><p><b>'
                . __('Paysera Payment And Delivery', PayseraPaths::PAYSERA_TRANSLATIONS) . ': </b>' . $notice
                . '<a href="?paysera-new-delivery-notice-dismiss" class="notice-dismiss paysera-notice-dismiss"></a></p></div>',
                [
                    'div' => ['class' => []],
                    'p' => [],
                    'b' => [],
                    'br' => [],
                    'a' => ['href' => [], 'class' => []],
                ]
            );
        }
    }

    public function payseraDeliveryPluginNoticeDismiss(): void
    {
        if (isset($_GET['paysera-new-delivery-notice-dismiss'])) {
            add_user_meta(wp_get_current_user()->ID, 'paysera_new_delivery_notice', 'true', true);

            wp_redirect('admin.php?page=paysera');
            exit();
        }
    }

    public function displayAdminErrors(): void
    {
        if (empty($this->errors) === false) {
            foreach ($this->errors as $error) {
                echo wp_kses(
                    '<div class="error"><p><b>'
                    . __('Paysera Payment And Delivery', PayseraPaths::PAYSERA_TRANSLATIONS) . ': </b><br>'
                    . $error . '</p></div>',
                    ['div' => ['class' => []], 'p' => [], 'b' => [], 'br' => [], 'a' => ['href' => []]]
                );
            }
        }
    }

    public function displayAdminNotices(): void
    {
        if (empty($this->notices) === false) {
            foreach ($this->notices as $notice) {
                echo wp_kses(
                    '<div class="notice notice-info is-dismissible"><p><b>'
                    . __('Paysera Payment And Delivery', PayseraPaths::PAYSERA_TRANSLATIONS) . ': </b><br>'
                    . $notice . '</p></div>',
                    ['div' => ['class' => []], 'p' => [], 'b' => [], 'br' => [], 'a' => ['href' => []]]
                );
            }
        }
    }

    public function registerPaymentGateway(array $methods): array
    {
        require_once 'Entity/class-paysera-payment-gateway.php';

        $methods[] = 'Paysera_Payment_Gateway';

        return $methods;
    }

    public function addPayseraPluginActionLinks(array $links): array
    {
        wp_enqueue_style('paysera-payment-css', PayseraPaths::PAYSERA_PAYMENT_CSS);

        $documentationLink = '<a href="' . PayseraPaths::PAYSERA_DOCUMENTATION_LINK . '" target="_blank">'
            . __('Documentation', PayseraPaths::PAYSERA_TRANSLATIONS) .'</a>'
        ;

        $settingsLink = '<a class="paysera-delivery-error-link" ">'
            . __('WooCommerce is not active', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>'
        ;

        if (class_exists('woocommerce') === true) {
            $settingsLink = '<a href="' . admin_url(PayseraPaths::PAYSERA_ADMIN_SETTINGS_LINK) . '">'
                . __('Settings', PayseraPaths::PAYSERA_TRANSLATIONS) . '</a>'
            ;
        }

        array_unshift($links, $settingsLink, $documentationLink);

        return $links;
    }

    public function addMetaTags(): void
    {
        if (
            $this->payseraPaymentSettings->isOwnershipCodeEnabled() === true
            && (
                $this->payseraPaymentSettings->getOwnershipCode() !== null
                && $this->payseraPaymentSettings->getOwnershipCode() !== ''
            )
        ) {
            echo wp_kses(
                '<meta name="verify-paysera" content="' . $this->payseraPaymentSettings->getOwnershipCode() . '">',
                ['meta' => ['name' => [], 'content' => []]]
            );
        }
    }

    public function addQualitySign(): void
    {
        if (
            $this->payseraPaymentSettings->isQualitySignEnabled() === true
            && $this->payseraPaymentSettings->getProjectId() !== null
        ) {
            $this->addQualitySignScript($this->payseraPaymentSettings->getProjectId());
        }
    }

    public function registerDeliveryGateways(array $methods): array
    {
        $payseraDeliverySettings = $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings();

        foreach ($payseraDeliverySettings->getDeliveryGateways() as $deliveryGateway => $isEnabled) {
            if ($isEnabled === false) {
                continue;
            }

            foreach (PayseraDeliverySettings::DELIVERY_GATEWAY_TYPE_MAP as $deliveryGatewayType) {
                if (
                    $this->isDeliveryGatewayShippingMethodAllowed(
                        $payseraDeliverySettings->getShipmentMethods(),
                        $deliveryGateway,
                        $deliveryGatewayType
                    )
                ) {
                    require_once 'Entity/class-paysera-' . $deliveryGateway . '-' . $deliveryGatewayType . '-delivery.php';

                    $methods['paysera_delivery_' . $deliveryGateway . '_' . $deliveryGatewayType] =
                        'Paysera_' . ucfirst($deliveryGateway) . '_' . ucfirst($deliveryGatewayType) . '_Delivery'
                    ;
                }
            }
        }

        return $methods;
    }

    public function enqueueScripts(): void
    {
        wp_enqueue_style('paysera-select-2-css', PayseraPaths::PAYSERA_SELECT_2_CSS);
        wp_enqueue_script('paysera-select-2-js', PayseraPaths::PAYSERA_SELECT_2_JS, ['jquery']);
        wp_enqueue_script('paysera-delivery-frontend-js', PayseraPaths::PAYSERA_DELIVERY_FRONTEND_JS, ['jquery']);
        wp_register_script(
            'paysera-delivery-frontend-ajax-js',
            PayseraPaths::PAYSERA_DELIVERY_FRONTEND_AJAX_JS,
            [],
            false,
            true
        );
        wp_enqueue_script('paysera-delivery-frontend-ajax-js');
        wp_localize_script(
            'paysera-delivery-frontend-ajax-js',
            'ajax_object',
            ['ajaxurl' => admin_url('admin-ajax.php')]
        );
    }

    public function deliveryGatewayLogos(string $label, WC_Shipping_Rate $shippingRate): string
    {
        wp_enqueue_style('paysera-delivery-css', PayseraPaths::PAYSERA_DELIVERY_CSS);

        if (
            empty($this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()->getDeliveryGateways())
            === true ||
            $this->payseraDeliveryHelper->isPayseraDeliveryGateway($shippingRate->get_method_id()) === false
        ) {
            return $label;
        }

        $totalWeight = 0;

        foreach (WC()->cart->cart_contents as $item) {
            $product = wc_get_product($item['product_id']);

            $totalWeight += (float) ($product->get_weight() ?? 0) * (float) $item['quantity'];
        }

        if (get_option('woocommerce_weight_unit') === 'g') {
            $totalWeight /= 1000;
        }

        $payseraDeliveryGatewaySettings = $this->payseraDeliverySettingsProvider->getPayseraDeliveryGatewaySettings(
            $shippingRate->get_id()
        );

        $maximumWeight = $payseraDeliveryGatewaySettings->getMaximumWeight();
        $minimumWeight = $payseraDeliveryGatewaySettings->getMinimumWeight();

        $error = null;

        if ($totalWeight > $maximumWeight || $totalWeight < $minimumWeight) {
            $error = __('Cart weight is not sufficient', PayseraPaths::PAYSERA_TRANSLATIONS);

            if ($totalWeight > $maximumWeight) {
                $error = __('Cart weight is exceeded', PayseraPaths::PAYSERA_TRANSLATIONS);
            }

            $label .= '<p class="paysera-delivery-error">' . $error . '</p>';
        }

        foreach ($this->payseraDeliveryLibraryHelper->getPayseraDeliveryGateways() as $deliveryGateway) {
            if (
                $shippingRate->get_method_id() === PayseraDeliverySettings::DELIVERY_GATEWAY_PREFIX
                . $deliveryGateway->getCode() . '_courier:' . $shippingRate->get_instance_id()
                ||
                $shippingRate->get_method_id() === PayseraDeliverySettings::DELIVERY_GATEWAY_PREFIX
                . $deliveryGateway->getCode() . '_terminals:' . $shippingRate->get_instance_id()
            ) {

                if ($error === null) {
                    $label .= '<br>';
                }

                $label .= $this->payseraDeliveryAdminHtml->generateDeliveryGatewayLogoHtml($deliveryGateway, true);
            }
        }

        return $label;
    }

    private function getActivePayseraPlugins(): array
    {
        $plugins = [];

        foreach (get_option('active_plugins') as $activePlugin) {
            if (strpos($activePlugin, 'paysera') !== false) {
                $plugins[] = $activePlugin;
            }
        }

        return $plugins;
    }

    private function addError(string $errorText): void
    {
        $this->errors[] = __($errorText, PayseraPaths::PAYSERA_TRANSLATIONS);
    }

    private function addNotice(string $noticeText): void
    {
        $this->notices[] = __($noticeText, PayseraPaths::PAYSERA_TRANSLATIONS);
    }

    private function addQualitySignScript(int $projectId): void
    {
        wp_enqueue_script(
            'paysera-payment-quality-sign-js',
            PayseraPaths::PAYSERA_PAYMENT_QUALITY_SIGN_JS,
            ['jquery']
        );
        wp_localize_script(
            'paysera-payment-quality-sign-js',
            'data',
            [
                'project_id' => $projectId,
                'language'=> explode('_', get_locale())[0],
            ]
        );
    }

    private function isDeliveryGatewayShippingMethodAllowed(
        array $shipmentMethods,
        string $deliveryGateway,
        string $deliveryGatewayType
    ): bool {
        if (
            (
                (
                    $shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_COURIER_2_COURIER] === true
                    || $shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_PARCEL_MACHINE_2_COURIER] === true
                ) && $deliveryGatewayType === PayseraDeliverySettings::TYPE_COURIER
            )
            ||
            (
                (
                    $shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_COURIER_2_PARCEL_MACHINE] === true
                    || $shipmentMethods[PayseraDeliverySettings::SHIPMENT_METHOD_PARCEL_MACHINE_2_PARCEL_MACHINE] === true
                ) && $deliveryGatewayType === PayseraDeliverySettings::TYPE_TERMINALS
            )
        ) {
            if (
                $this->payseraDeliveryHelper->deliveryGatewayClassExists(
                    $deliveryGateway,
                    $deliveryGatewayType
                ) === false
            ) {
                $this->createDeliveryEntity($deliveryGateway, $deliveryGatewayType);
            }

            return true;
        }

        return false;
    }

    private function createDeliveryEntity(string $deliveryGateway, string $deliveryGatewayType): void
    {
        $deliveryEntity = 'Paysera_' . ucfirst($deliveryGateway) . '_' . ucfirst($deliveryGatewayType) . '_Delivery';

        $description = '%s courier will deliver the parcel to the selected parcel terminal for customer to pickup any time.';

        if ($deliveryGatewayType === PayseraDeliverySettings::TYPE_COURIER) {
            $description = "%s courier will deliver the parcel right to the customer\'s hands.";
        }

        $receiverType = $deliveryGatewayType === PayseraDeliverySettings::TYPE_COURIER ?
            PayseraDeliverySettings::TYPE_COURIER : PayseraDeliverySettings::TYPE_PARCEL_MACHINE
        ;

        $deliveryGatewayTitles = $this->payseraDeliverySettingsProvider->getPayseraDeliverySettings()
            ->getDeliveryGatewayTitles()
        ;
        $deliveryGatewayTitle = $deliveryGatewayTitles[$deliveryGateway] . ' '
            . __(ucfirst($deliveryGatewayType), PayseraPaths::PAYSERA_TRANSLATIONS)
        ;

        $entityContent = '<?php

declare(strict_types=1);

defined(\'ABSPATH\') || exit;

if (class_exists(\'Paysera_Delivery_Gateway\') === false) {
    require_once \'abstract-paysera-delivery-gateway.php\';
}

class ' . $deliveryEntity . ' extends Paysera_Delivery_Gateway
{
    public $deliveryGatewayCode = \'' . $deliveryGateway . '_' . $deliveryGatewayType  . '\';
    public $defaultTitle = \'' . $deliveryGatewayTitle . '\';
    public $receiverType = \'' . $receiverType . '\';
    public $defaultDescription = \'' . $description . '\'; 
}
';

        file_put_contents(
            plugin_dir_path(__FILE__) . 'Entity/class-paysera-' . $deliveryGateway . '-'
            . $deliveryGatewayType . '-delivery.php',
            $entityContent
        );
    }
}
