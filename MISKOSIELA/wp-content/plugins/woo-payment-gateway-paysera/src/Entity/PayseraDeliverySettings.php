<?php

declare(strict_types=1);

namespace Paysera\Entity;

defined('ABSPATH') || exit;

class PayseraDeliverySettings
{
    public const SETTINGS_NAME = 'paysera_delivery_settings';
    public const EXTRA_SETTINGS_NAME = 'paysera_delivery_extra_settings';
    public const DELIVERY_GATEWAYS_SETTINGS_NAME = 'paysera_delivery_gateways_settings';
    public const DELIVERY_GATEWAYS_TITLES = 'paysera_delivery_gateways_titles';

    public const PROJECT_ID = 'project_id';
    public const RESOLVED_PROJECT_ID = 'resolved_project_id';
    public const PROJECT_PASSWORD = 'project_password';
    public const TEST_MODE = 'test_mode';
    public const HOUSE_NUMBER_FIELD = 'house_number_field';
    public const GRID_VIEW = 'grid_view';
    public const HIDE_SHIPPING_METHODS = 'hide_shipping_methods';
    public const DELIVERY_GATEWAYS = 'delivery_gateways';
    public const SHIPMENT_METHODS = 'shipment_methods';

    public const MINIMUM_WEIGHT = 'minimum_weight';
    public const MAXIMUM_WEIGHT = 'maximum_weight';
    public const SENDER_TYPE = 'sender_type';
    public const RECEIVER_TYPE = 'receiver_type';
    public const FEE = 'fee';
    public const FREE_DELIVERY_LIMIT = 'free_delivery_limit';

    public const DEFAULT_MINIMUM_WEIGHT = 0;
    public const DEFAULT_MAXIMUM_WEIGHT = 30;
    public const DEFAULT_FEE = 0;
    public const DEFAULT_TYPE = self::TYPE_COURIER;
    public const DEFAULT_FREE_DELIVERY_LIMIT = 0;

    public const SHIPMENT_METHOD_COURIER_2_COURIER = 'courier2courier';
    public const SHIPMENT_METHOD_COURIER_2_PARCEL_MACHINE = 'courier2parcel-machine';
    public const SHIPMENT_METHOD_PARCEL_MACHINE_2_COURIER = 'parcel-machine2courier';
    public const SHIPMENT_METHOD_PARCEL_MACHINE_2_PARCEL_MACHINE = 'parcel-machine2parcel-machine';

    public const TYPE_COURIER = 'courier';
    public const TYPE_PARCEL_MACHINE = 'parcel-machine';
    public const TYPE_TERMINALS = 'terminals';

    public const DELIVERY_GATEWAY_TYPE_MAP = [
        self::TYPE_COURIER,
        self::TYPE_TERMINALS,
    ];

    public const READABLE_TYPES = [
        self::TYPE_COURIER => 'Courier',
        self::TYPE_PARCEL_MACHINE => 'Parcel locker',
    ];

    public const DELIVERY_GATEWAY_PREFIX = 'paysera_delivery_';

    /**
     * @var ?int
     */
    private $projectId;

    /**
     * @var ?string
     */
    private $resolvedProjectId;

    /**
     * @var ?string
     */
    private $projectPassword;

    /**
     * @var ?bool
     */
    private $testModeEnabled;

    /**
     * @var ?bool
     */
    private $houseNumberFieldEnabled;

    /**
     * @var ?bool
     */
    private $gridViewEnabled;

    /**
     * @var ?bool
     */
    private $hideShippingMethodsEnabled;

    /**
     * @var array
     */
    private $deliveryGateways;

    /**
     * @var array
     */
    private $deliveryGatewayTitles;

    /**
     * @var array
     */
    private $shipmentMethods;

    public function __construct()
    {
        $this->projectId = null;
        $this->resolvedProjectId = null;
        $this->projectPassword = null;
        $this->testModeEnabled = null;
        $this->houseNumberFieldEnabled = null;
        $this->gridViewEnabled = null;
        $this->hideShippingMethodsEnabled = null;
        $this->deliveryGateways = [];
        $this->deliveryGatewayTitles = [];
        $this->shipmentMethods = [];
    }

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function setProjectId(int $projectId): self
    {
        $this->projectId = $projectId;

        return $this;
    }

    public function getResolvedProjectId(): ?string
    {
        return $this->resolvedProjectId;
    }

    public function setResolvedProjectId(string $resolvedProjectId): self
    {
        $this->resolvedProjectId = $resolvedProjectId;

        return $this;
    }

    public function getProjectPassword(): ?string
    {
        return $this->projectPassword;
    }

    public function setProjectPassword(string $projectPassword): self
    {
        $this->projectPassword = $projectPassword;

        return $this;
    }

    public function isTestModeEnabled(): ?bool
    {
        return $this->testModeEnabled;
    }

    public function setTestModeEnabled(bool $testModeEnabled): self
    {
        $this->testModeEnabled = $testModeEnabled;

        return $this;
    }

    public function isHouseNumberFieldEnabled(): ?bool
    {
        return $this->houseNumberFieldEnabled;
    }

    public function setHouseNumberFieldEnabled(bool $houseNumberFieldEnabled): self
    {
        $this->houseNumberFieldEnabled = $houseNumberFieldEnabled;

        return $this;
    }

    public function isGridViewEnabled(): ?bool
    {
        return $this->gridViewEnabled;
    }

    public function setGridViewEnabled(bool $gridViewEnabled): self
    {
        $this->gridViewEnabled = $gridViewEnabled;

        return $this;
    }

    public function isHideShippingMethodsEnabled(): ?bool
    {
        return $this->hideShippingMethodsEnabled;
    }

    public function setHideShippingMethodsEnabled(bool $hideShippingMethodsEnabled): self
    {
        $this->hideShippingMethodsEnabled = $hideShippingMethodsEnabled;

        return $this;
    }

    public function getDeliveryGateways(): array
    {
        return $this->deliveryGateways;
    }

    public function setDeliveryGateways(array $deliveryGateways): self
    {
        $this->deliveryGateways = $deliveryGateways;

        return $this;
    }

    public function getDeliveryGatewayTitles(): array
    {
        return $this->deliveryGatewayTitles;
    }

    public function setDeliveryGatewayTitles(array $deliveryGatewayTitles): self
    {
        $this->deliveryGatewayTitles = $deliveryGatewayTitles;

        return $this;
    }

    public function getShipmentMethods(): array
    {
        return $this->shipmentMethods;
    }

    public function setShipmentMethods(array $shipmentMethods): self
    {
        $this->shipmentMethods = $shipmentMethods;

        return $this;
    }
}
