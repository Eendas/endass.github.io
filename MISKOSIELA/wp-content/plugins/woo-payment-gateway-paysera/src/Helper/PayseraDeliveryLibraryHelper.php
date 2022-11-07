<?php

declare(strict_types=1);

namespace Paysera\Helper;

defined('ABSPATH') || exit;

use Paysera\DeliveryApi\MerchantClient\ClientFactory;
use Paysera\DeliveryApi\MerchantClient\Entity\Contact;
use Paysera\DeliveryApi\MerchantClient\Entity\CountryFilter;
use Paysera\DeliveryApi\MerchantClient\Entity\GatewaysFilter;
use Paysera\DeliveryApi\MerchantClient\Entity\MethodsFilter;
use Paysera\DeliveryApi\MerchantClient\Entity\ParcelMachineFilter;
use Paysera\DeliveryApi\MerchantClient\Entity\CityFilter;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentMethod;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentPointCreate;
use Paysera\DeliveryApi\MerchantClient\MerchantClient;
use Exception;
use Paysera\Component\RestClientCommon\Entity\Filter;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentGateway;
use WC_Product;
use Paysera\DeliveryApi\MerchantClient\Entity\ShipmentCreate;
use Paysera\Entity\PayseraPaths;
use Paysera\Action\PayseraDeliveryActions;
use Paysera\Provider\PayseraRatesProvider;
use Paysera\Provider\PayseraDeliverySettingsProvider;
use Paysera\Entity\PayseraDeliverySettings;

class PayseraDeliveryLibraryHelper
{
    public const PAYSERA_DELIVERY_EXCEPTION_TEXT = '[Paysera Delivery] Got an exception: ';

    /**
     * @var PayseraDeliverySettings
     */
    private $payseraDeliverySettings;
    private $payseraDeliveryActions;
    private $payseraRatesProvider;

    public function __construct()
    {
        $this->payseraDeliverySettings = (new PayseraDeliverySettingsProvider())->getPayseraDeliverySettings();
        $this->payseraDeliveryActions = new PayseraDeliveryActions();
        $this->payseraRatesProvider = new PayseraRatesProvider();
    }

    /**
     * @return ShipmentGateway[]
     */
    public function getPayseraDeliveryGateways(): array
    {
        $gatewaysFilter = (new GatewaysFilter());

        $resolvedProjectId = $this->payseraDeliverySettings->getResolvedProjectId();

        if ($resolvedProjectId !== null) {
            $gatewaysFilter->setProjectId($resolvedProjectId);
        }

        $merchantClient = $this->getMerchantClient();

        if ($merchantClient === null) {
            return [];
        }

        try {
            $deliveryGateways = $merchantClient->updateGateway($gatewaysFilter)->getList();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return [];
        }

        return $deliveryGateways;
    }

    /**
     * @return ShipmentMethod[]
     */
    public function getPayseraShipmentMethods(): array
    {
        $methodsFilter = (new MethodsFilter());

        $resolvedProjectId = $this->payseraDeliverySettings->getResolvedProjectId();

        if ($resolvedProjectId !== null) {
            $methodsFilter->setProjectId($resolvedProjectId);
        }

        $merchantClient = $this->getMerchantClient();

        if ($merchantClient === null) {
            return [];
        }

        try {
            $shipmentMethods = $merchantClient->updateMethod($methodsFilter)->getList();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return [];
        }

        return $shipmentMethods;
    }

    public function getParcelMachinesLocations(string $country, string $city, string $deliveryGatewayCode): array
    {
        $parcelMachineFilter = (new ParcelMachineFilter())
            ->setCountry($country)
            ->setCity($city)
            ->setShipmentGatewayCode($deliveryGatewayCode)
        ;

        $merchantClient = $this->getMerchantClient();

        if ($merchantClient === null) {
            return [];
        }

        try {
            $parcelMachines = $merchantClient->getParcelMachines($parcelMachineFilter)->getList();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return [];
        }

        $locations = [];

        foreach ($parcelMachines as $parcelMachine) {
            $locationInfo = [];

            $locationInfo[] = $parcelMachine->getAddress()->getStreet() ?? '';
            $locationInfo[] = $parcelMachine->getLocationName() ?? '';

            $locations[$parcelMachine->getId()] = implode(', ', array_filter($locationInfo));
        }

        asort($locations);

        return $locations;
    }

    public function getPayseraCountries(string $deliveryGatewayCode): array
    {
        $countryFilter = (new CountryFilter())->setShipmentGatewayCode($deliveryGatewayCode);

        $merchantClient = $this->getMerchantClient();

        if ($merchantClient === null) {
            return [];
        }

        try {
            $countries = $merchantClient->getCountries($countryFilter)->getItems();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return [];
        }

        $normalizedCountries = [];

        foreach ($countries as $country) {
            $countryCode = $country->getCountryCode();
            $normalizedCountries[$countryCode] = WC()->countries->get_countries()[$countryCode];
        }

        asort($normalizedCountries);

        return $normalizedCountries;
    }

    public function getPayseraCities(string $country, string $deliveryGatewayCode): array
    {
        $cityFilter = (new CityFilter())
            ->setCountry($country)
            ->setGatewayCode($deliveryGatewayCode)
        ;

        $merchantClient = $this->getMerchantClient();

        if ($merchantClient === null) {
            return [];
        }

        try {
            $cities = $merchantClient->getCities($cityFilter)->getItems();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return [];
        }

        $normalizedCities = [];

        foreach ($cities as $city) {
            $normalizedCities[] = $city->getName();
        }

        asort($normalizedCities);

        return $normalizedCities;
    }

    public function getMerchantClient(): ?MerchantClient
    {
        $clientFactory = new ClientFactory([
            'base_url' => 'https://delivery-api.paysera.com/rest/v1/',
            'mac' => [
                'mac_id' => $this->payseraDeliverySettings->getProjectId(),
                'mac_secret' => $this->payseraDeliverySettings->getProjectPassword(),
            ],
        ]);

        try {
            $merchantClient = $clientFactory->getMerchantClient();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return null;
        }

        try {
            $resolvedProjectId = $merchantClient->getProjects(new Filter())->getList()[0]->getId();
        } catch (Exception $exception) {
            error_log(self::PAYSERA_DELIVERY_EXCEPTION_TEXT . $exception);

            return null;
        }

        if (
            $this->payseraDeliverySettings->getResolvedProjectId() === null
            || $this->payseraDeliverySettings->getResolvedProjectId() !== $resolvedProjectId
        ) {
            $this->payseraDeliveryActions->updateResolvedProjectId($resolvedProjectId);
        }

        return $merchantClient;
    }

    public function createOrderParty(
        string $orderPartyMethod,
        string $type,
        ?Contact $contact = null
    ): ShipmentPointCreate {
        $orderParty = (new ShipmentPointCreate())
            ->setType($type)
            ->setSaved(false)
            ->setDefaultContact(false)
        ;

        if ($this->payseraDeliverySettings->getResolvedProjectId() !== null) {
            $orderParty->setProjectId($this->payseraDeliverySettings->getResolvedProjectId());
        }

        if ($contact !== null) {
            $orderParty->setContact($contact);
        }

        if (($orderPartyMethod === PayseraDeliverySettings::TYPE_PARCEL_MACHINE) && $type === 'receiver') {
            $orderParty->setParcelMachineId(WC()->session->get('terminal'));
        }

        return $orderParty;
    }

    public function createShipment(WC_Product $product): ShipmentCreate
    {
        $weightRate = $this->payseraRatesProvider->getRateByKey(get_option('woocommerce_weight_unit'));
        $dimensionRate = $this->payseraRatesProvider->getRateByKey(get_option('woocommerce_dimension_unit'));

        $weight = $product->get_weight() ?? '0';
        $length = $product->get_length() ?? '0';
        $width = $product->get_width() ?? '0';
        $height = $product->get_height() ?? '0';

        return (new ShipmentCreate())
            ->setWeight((int) ($weight * $weightRate))
            ->setLength((int) ($length * $dimensionRate))
            ->setWidth((int) ($width * $dimensionRate))
            ->setHeight((int) ($height * $dimensionRate))
        ;
    }

    public function formatSelectedTerminalNote(
        string $countryCode,
        string $city,
        string $deliveryGatewayCode,
        string $selectedTerminal
    ): string {
        $countryName = WC()->countries->get_countries()[$countryCode];
        $terminals = $this->getParcelMachinesLocations($countryCode, $city, $deliveryGatewayCode);

        return sprintf(
            __(
                PayseraPaths::PAYSERA_MESSAGE . 'Chosen terminal location - %s, %s, %s',
                PayseraPaths::PAYSERA_TRANSLATIONS
            ),
            $countryName,
            $city,
            $terminals[$selectedTerminal]
        );
    }
}
