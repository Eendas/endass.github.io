
## paysera/lib-delivery-api-merchant-client

Provides methods to manipulate `MerchantClient` API.
It automatically authenticates all requests and maps required data structure for you.

#### Usage

This library provides `ClientFactory` class, which you should use to get the API client itself:

```php
use Paysera\DeliveryApi\MerchantClient\ClientFactory;

$clientFactory = new ClientFactory([
    'base_url' => 'https://delivery-api.paysera.com/rest/v1/', // optional, in case you need a custom one.
    'basic' => [                                        // use this, it API requires Basic authentication.
        'username' => 'username',
        'password' => 'password',
    ],
    'oauth' => [                                        // use this, it API requires OAuth v2 authentication.
        'token' => [
            'access_token' => 'my-access-token',
            'refresh_token' => 'my-refresh-token',
        ],
    ],
    // other configuration options, if needed
]);

$merchantClient = $clientFactory->getMerchantClient();
```

Please use only one authentication mechanism, provided by `Vendor`.

Now, that you have instance of `MerchantClient`, you can use following methods
### Methods

    
Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$filter = new \Paysera\Component\RestClientCommon\Entity\Filter();

$filter->setLimit($limit);
$filter->setOffset($offset);
$filter->setOrderBy($orderBy);
$filter->setOrderDirection($orderDirection);
$filter->setAfter($after);
$filter->setBefore($before);
    
$result = $merchantClient->getDefaultPackageSizes($filter);
```
---

    
Get shipment method by id


```php

$result = $merchantClient->getMethod($id);
```
---


Get shipment methods


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$methodsFilter = new Entities\MethodsFilter();

$methodsFilter->setProjectId($projectId);
$methodsFilter->setFromCountryCode($fromCountryCode);
$methodsFilter->setToCountryCode($toCountryCode);
$methodsFilter->setShipments($shipments);
    
$result = $merchantClient->updateMethod($methodsFilter);
```
---

    
Get shipment gateway by id


```php

$result = $merchantClient->getGateway($id);
```
---


Get shipment gateways


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$gatewaysFilter = new Entities\GatewaysFilter();

$gatewaysFilter->setProjectId($projectId);
$gatewaysFilter->setFromCountryCode($fromCountryCode);
$gatewaysFilter->setToCountryCode($toCountryCode);
$gatewaysFilter->setShipments($shipments);
$gatewaysFilter->setShipmentMethodCode($shipmentMethodCode);
    
$result = $merchantClient->updateGateway($gatewaysFilter);
```
---

    
Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$postOfficeFilter = new Entities\PostOfficeFilter();

$postOfficeFilter->setCity($city);
$postOfficeFilter->setCountry($country);
$postOfficeFilter->setShipmentGatewayCode($shipmentGatewayCode);
    
$result = $merchantClient->getPostOffices($postOfficeFilter);
```
---

    
Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$parcelMachineFilter = new Entities\ParcelMachineFilter();

$parcelMachineFilter->setCity($city);
$parcelMachineFilter->setCountry($country);
$parcelMachineFilter->setShipmentGatewayCode($shipmentGatewayCode);
    
$result = $merchantClient->getParcelMachines($parcelMachineFilter);
```
---

    
Import shipment points from file


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$file = new \Paysera\Component\RestClientCommon\Entity\File();

$file->setName($name);
$file->setContent($content);
$file->setMimeType($mimeType);
$file->setSize($size);
    
$result = $merchantClient->createShipmentPointsImport($file);
```
---

    
Get shipment point by id


```php

$result = $merchantClient->getShipmentPoint($id);
```
---

Update shipment-point


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentPointCreate = new Entities\ShipmentPointCreate();

$shipmentPointCreate->setProjectId($projectId);
$shipmentPointCreate->setType($type);
$shipmentPointCreate->setSaved($saved);
$shipmentPointCreate->setContact($contact);
$shipmentPointCreate->setPostOfficeId($postOfficeId);
$shipmentPointCreate->setParcelMachineId($parcelMachineId);
$shipmentPointCreate->setAdditionalInfo($additionalInfo);
$shipmentPointCreate->setDefaultContact($defaultContact);
    
$result = $merchantClient->updateShipmentPoint($id, $shipmentPointCreate);
```
---

Delete shipment point


```php

$merchantClient->deleteShipmentPoint($id);
```
---


Add a new shipment point


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentPointCreate = new Entities\ShipmentPointCreate();

$shipmentPointCreate->setProjectId($projectId);
$shipmentPointCreate->setType($type);
$shipmentPointCreate->setSaved($saved);
$shipmentPointCreate->setContact($contact);
$shipmentPointCreate->setPostOfficeId($postOfficeId);
$shipmentPointCreate->setParcelMachineId($parcelMachineId);
$shipmentPointCreate->setAdditionalInfo($additionalInfo);
$shipmentPointCreate->setDefaultContact($defaultContact);
    
$result = $merchantClient->createShipmentPoint($shipmentPointCreate);
```
---

Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentPointFilter = new Entities\ShipmentPointFilter();

$shipmentPointFilter->setType($type);
$shipmentPointFilter->setTitlePart($titlePart);
$shipmentPointFilter->setDefaultContact($defaultContact);
$shipmentPointFilter->setProjects($projects);
    
$result = $merchantClient->getShipmentPoints($shipmentPointFilter);
```
---

    
Import orders from file


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$file = new \Paysera\Component\RestClientCommon\Entity\File();

$file->setName($name);
$file->setContent($content);
$file->setMimeType($mimeType);
$file->setSize($size);
    
$result = $merchantClient->createOrdersImport($file);
```
---

    
Get current default package size


```php

$result = $merchantClient->getProjectDefaultPackageSize($projectId);
```
---

Update default package size


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$defaultPackageSizeCode = new Entities\DefaultPackageSizeCode();

$defaultPackageSizeCode->setCode($code);
    
$result = $merchantClient->updateProjectDefaultPackageSize($projectId, $defaultPackageSizeCode);
```
---

Remove default package size


```php

$merchantClient->deleteProjectDefaultPackageSize($projectId);
```
---


Update project shipment gateway


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentGatewayCreate = new Entities\ShipmentGatewayCreate();

$shipmentGatewayCreate->setEnabled($enabled);
    
$result = $merchantClient->updateProjectGateway($projectId, $gatewayId, $shipmentGatewayCreate);
```
---


Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$filter = new \Paysera\Component\RestClientCommon\Entity\Filter();

$filter->setLimit($limit);
$filter->setOffset($offset);
$filter->setOrderBy($orderBy);
$filter->setOrderDirection($orderDirection);
$filter->setAfter($after);
$filter->setBefore($before);
    
$result = $merchantClient->getProjectGateways($projectId, $filter);
```
---

Update project gateways


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentGatewayCreateCollection = new Entities\ShipmentGatewayCreateCollection();

$shipmentGatewayCreateCollection->setList($list);
    
$result = $merchantClient->updateProjectGateways($projectId, $shipmentGatewayCreateCollection);
```
---


Update project shipment method


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentMethodCreate = new Entities\ShipmentMethodCreate();

$shipmentMethodCreate->setEnabled($enabled);
    
$result = $merchantClient->updateProjectMethod($projectId, $methodId, $shipmentMethodCreate);
```
---


Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$filter = new \Paysera\Component\RestClientCommon\Entity\Filter();

$filter->setLimit($limit);
$filter->setOffset($offset);
$filter->setOrderBy($orderBy);
$filter->setOrderDirection($orderDirection);
$filter->setAfter($after);
$filter->setBefore($before);
    
$result = $merchantClient->getProjectMethods($projectId, $filter);
```
---

Update project methods


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$shipmentMethodCreateCollection = new Entities\ShipmentMethodCreateCollection();

$shipmentMethodCreateCollection->setList($list);
    
$result = $merchantClient->updateProjectMethods($projectId, $shipmentMethodCreateCollection);
```
---



Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$filter = new \Paysera\Component\RestClientCommon\Entity\Filter();

$filter->setLimit($limit);
$filter->setOffset($offset);
$filter->setOrderBy($orderBy);
$filter->setOrderDirection($orderDirection);
$filter->setAfter($after);
$filter->setBefore($before);
    
$result = $merchantClient->getProjects($filter);
```
---

    
Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderFilter = new Entities\OrderFilter();

$orderFilter->setProjects($projects);
$orderFilter->setOrderStatuses($orderStatuses);
$orderFilter->setReceiverCountryCode($receiverCountryCode);
$orderFilter->setReceiverNamePart($receiverNamePart);
$orderFilter->setReceiverPhonePart($receiverPhonePart);
$orderFilter->setReceiverStreetPart($receiverStreetPart);
$orderFilter->setCreatedDateFrom($createdDateFrom);
$orderFilter->setCreatedDateTill($createdDateTill);
$orderFilter->setNumber($number);
$orderFilter->setTrackingCode($trackingCode);
$orderFilter->setShippingGatewayCode($shippingGatewayCode);
$orderFilter->setWithManifest($withManifest);
$orderFilter->setWithLabel($withLabel);
$orderFilter->setEshopOrderId($eshopOrderId);
    
$result = $merchantClient->getOrdersExport($orderFilter);
```
---

    
Confirm orders


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderIdsList = new Entities\OrderIdsList();

$orderIdsList->setOrderIds($orderIds);
    
$result = $merchantClient->createOrdersConfirm($orderIdsList);
```
---

    
Generate manifest and call courier for &quot;label_generated&quot; order


```php

$result = $merchantClient->createOrderManifest($id);
```
---

Get manifest file


```php

$result = $merchantClient->getOrderManifest($id);
```
---


Generate labels for &quot;in progress&quot; order


```php

$result = $merchantClient->createOrderLabel($id);
```
---

Get label file


```php

$result = $merchantClient->getOrderLabel($id);
```
---


reset order to draft state


```php

$result = $merchantClient->resetOrderToDraft($id);
```
---


Get order by id


```php

$result = $merchantClient->getOrder($id);
```
---

Update order


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderCreate = new Entities\OrderCreate();

$orderCreate->setProjectId($projectId);
$orderCreate->setShipmentGatewayCode($shipmentGatewayCode);
$orderCreate->setShipmentMethodCode($shipmentMethodCode);
$orderCreate->setShipments($shipments);
$orderCreate->setSenderId($senderId);
$orderCreate->setSender($sender);
$orderCreate->setReceiverId($receiverId);
$orderCreate->setReceiver($receiver);
$orderCreate->setNotes($notes);
$orderCreate->setEshopOrderId($eshopOrderId);
    
$result = $merchantClient->updateOrder($id, $orderCreate);
```
---

Delete order


```php

$merchantClient->deleteOrder($id);
```
---


Add a new order


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderCreate = new Entities\OrderCreate();

$orderCreate->setProjectId($projectId);
$orderCreate->setShipmentGatewayCode($shipmentGatewayCode);
$orderCreate->setShipmentMethodCode($shipmentMethodCode);
$orderCreate->setShipments($shipments);
$orderCreate->setSenderId($senderId);
$orderCreate->setSender($sender);
$orderCreate->setReceiverId($receiverId);
$orderCreate->setReceiver($receiver);
$orderCreate->setNotes($notes);
$orderCreate->setEshopOrderId($eshopOrderId);
    
$result = $merchantClient->createOrder($orderCreate);
```
---

Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderFilter = new Entities\OrderFilter();

$orderFilter->setProjects($projects);
$orderFilter->setOrderStatuses($orderStatuses);
$orderFilter->setReceiverCountryCode($receiverCountryCode);
$orderFilter->setReceiverNamePart($receiverNamePart);
$orderFilter->setReceiverPhonePart($receiverPhonePart);
$orderFilter->setReceiverStreetPart($receiverStreetPart);
$orderFilter->setCreatedDateFrom($createdDateFrom);
$orderFilter->setCreatedDateTill($createdDateTill);
$orderFilter->setNumber($number);
$orderFilter->setTrackingCode($trackingCode);
$orderFilter->setShippingGatewayCode($shippingGatewayCode);
$orderFilter->setWithManifest($withManifest);
$orderFilter->setWithLabel($withLabel);
$orderFilter->setEshopOrderId($eshopOrderId);
    
$result = $merchantClient->getOrders($orderFilter);
```
---

    
List order prices


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderPriceFilter = new Entities\OrderPriceFilter();

$orderPriceFilter->setProjectId($projectId);
$orderPriceFilter->setSenderId($senderId);
$orderPriceFilter->setSender($sender);
$orderPriceFilter->setReceiverId($receiverId);
$orderPriceFilter->setReceiver($receiver);
$orderPriceFilter->setShipments($shipments);
    
$result = $merchantClient->updateOrderPrice($orderPriceFilter);
```
---

    
Activity filter


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$activityFilter = new Entities\ActivityFilter();

$activityFilter->setProjects($projects);
$activityFilter->setDateFrom($dateFrom);
$activityFilter->setDateTill($dateTill);
    
$result = $merchantClient->getStatisticExport($activityFilter);
```
---


Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$lastActivityFilter = new Entities\LastActivityFilter();

$lastActivityFilter->setProjects($projects);
    
$result = $merchantClient->getStatisticLastActivity($lastActivityFilter);
```
---


Activity filter


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$activityFilter = new Entities\ActivityFilter();

$activityFilter->setProjects($projects);
$activityFilter->setDateFrom($dateFrom);
$activityFilter->setDateTill($dateTill);
    
$result = $merchantClient->getStatistics($activityFilter);
```
---

    
Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$countryFilter = new Entities\CountryFilter();

$countryFilter->setShipmentGatewayCode($shipmentGatewayCode);
    
$result = $merchantClient->getCountries($countryFilter);
```
---

    
Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$cityFilter = new Entities\CityFilter();

$cityFilter->setCountry($country);
$cityFilter->setGatewayCode($gatewayCode);
    
$result = $merchantClient->getCities($cityFilter);
```
---

    
List credentials


```php

$result = $merchantClient->getCourierApiCredential($projectId);
```
---


Update credentials


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$courierApiCredentialsCreate = new Entities\CourierApiCredentialsCreate();

$courierApiCredentialsCreate->setProject($project);
$courierApiCredentialsCreate->setGateway($gateway);
$courierApiCredentialsCreate->setUsername($username);
$courierApiCredentialsCreate->setPassword($password);
$courierApiCredentialsCreate->setCountry($country);
$courierApiCredentialsCreate->setClientId($clientId);
    
$result = $merchantClient->updateCourierApiCredential($id, $courierApiCredentialsCreate);
```
---

Delete credentials


```php

$merchantClient->deleteCourierApiCredential($id);
```
---


Create new credentials


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$courierApiCredentialsCreate = new Entities\CourierApiCredentialsCreate();

$courierApiCredentialsCreate->setProject($project);
$courierApiCredentialsCreate->setGateway($gateway);
$courierApiCredentialsCreate->setUsername($username);
$courierApiCredentialsCreate->setPassword($password);
$courierApiCredentialsCreate->setCountry($country);
$courierApiCredentialsCreate->setClientId($clientId);
    
$result = $merchantClient->createCourierApiCredential($courierApiCredentialsCreate);
```
---

Standard SQL-style Result filtering


```php
use Paysera\DeliveryApi\MerchantClient\Entity as Entities;

$orderFilter = new Entities\OrderFilter();

$orderFilter->setProjects($projects);
$orderFilter->setOrderStatuses($orderStatuses);
$orderFilter->setReceiverCountryCode($receiverCountryCode);
$orderFilter->setReceiverNamePart($receiverNamePart);
$orderFilter->setReceiverPhonePart($receiverPhonePart);
$orderFilter->setReceiverStreetPart($receiverStreetPart);
$orderFilter->setCreatedDateFrom($createdDateFrom);
$orderFilter->setCreatedDateTill($createdDateTill);
$orderFilter->setNumber($number);
$orderFilter->setTrackingCode($trackingCode);
$orderFilter->setShippingGatewayCode($shippingGatewayCode);
$orderFilter->setWithManifest($withManifest);
$orderFilter->setWithLabel($withLabel);
$orderFilter->setEshopOrderId($eshopOrderId);
    
$result = $merchantClient->getOrdersCount($orderFilter);
```
---
