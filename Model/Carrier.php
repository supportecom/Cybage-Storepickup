<?php

/**
 * Cybage Storepickup Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_ecom@cybage.com.  We will send you a copy of the source file.
 *
 * @category  Class
 * @package    Cybage_Storepickup
 * @copyright  Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India
 * http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Storepickup\Model;

use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\Country;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;
use Cybage\Storepickup\Model\ProductsFactory;
use Cybage\Storepickup\Model\StoresFactory;
use Cybage\Storepickup\Helper\Data as HelperData;
use Magento\Backend\Model\Session\Quote as BackendSession;
use Magento\Backend\Model\Auth\Session as AuthSession;

/**
 * Class Carrier Sore Pickup shipping model
 */
class Carrier extends AbstractCarrier implements CarrierInterface {

    /**
     * System variable for module enable
     */
    const XML_MODULE_ENABLE = 'stores_section/stores_group/enable';
    
    /**
     * Error message for address
     */
    const XML_ERROE_MESSAGE_FOR_ADDRESS = 'stores_section/storepickup_error_message/method_not_available_for_address';
    
    /**
     * Error message for method
     */
    const XML_ERROE_MESSAGE_FOR_METHOD = 'stores_section/storepickup_error_message/method_not_available';
    
    
    /**
     * Radious for google map
     */
    const XML_GOOGLE_MAP_RADIUS = 'stores_section/storepickup_map/radius';
    
    /**
     * Google api url
     */
    const XML_GOOGLE_API_URL = 'stores_section/storepickup_map/geocodeapi_url';
    
    /**
     * Google API Key
     */
    const XML_GOOGLE_API_KEY = 'stores_section/storepickup_map/api_key';
    
    /**
     * Constant for store enable status
     */
    const STORE_SATUS = 1;

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'storepickup';

        /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var Region
     */
    protected $region;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var Session 
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Products 
     */
    protected $productsFactory;

    /**
     * @var StoresFactory
     */
    protected $storesFactory;
    
     /**
     *
     * @var type 
     */
    protected $helperData;
    
    /**
     * @var AuthSession 
     */
    protected $authSession;
    
    /**
     * @var BackendSession 
     */
    protected $backendQuoteSession;

    /**
     * 
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Region $region
     * @param BackendSession $backendQuoteSession
     * @param AuthSession $authSession
     * @param Country $country
     * @param Session $session
     * @param ProductsFactory $productsFactory
     * @param StoresFactory $storesFactory
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig, 
        ErrorFactory $rateErrorFactory, 
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Region $region, 
        BackendSession $backendQuoteSession,
        AuthSession $authSession,
        Country $country, 
        Session $session, 
        ProductsFactory $productsFactory, 
        StoresFactory $storesFactory, 
        HelperData $helperData, 
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->logger = $logger;
        $this->rateErrorFactory = $rateErrorFactory;
        $this->region = $region;
        $this->country = $country;
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->storesFactory = $storesFactory;
        $this->productsFactory = $productsFactory;
        $this->helperData = $helperData;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->authSession = $authSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods() {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('price');
        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }

    /**
     * 
     * @param DataObject $request
     * @return $this
     */
    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request) {
        $availableStores = [];
        $setError = 0;
        $regionName = '';
        $country = '';
        // Check for module enable
        $isModuleEnable = $this->helperData->getStoreConfig(self::XML_MODULE_ENABLE);

        if ($isModuleEnable == 0) {
            return false;
        }
        if ($request->getAllItems()) {
            if ($request->getDestCity() != "" && $request->getDestCountryId() != "" && $request->getDestPostcode() != '' && ($request->getDestRegionId() != "" || $request->getDestRegionCode() != "" )) {
                if ($request->getDestRegionId()) {
                    //get region from ID
                    $regionModel = $this->region->load($request->getDestRegionId());
                    $regionName = $regionModel->getName();
                }
                if ($request->getDestCountryId()) {
                    //get country from Id
                    $countryModel = $this->country->loadByCode($request->getDestCountryId());
                    $country = $countryModel->getName();
                }
                $street = trim(str_replace(PHP_EOL, ' ', $request->getDestStreet()));
                $addressArray = [
                'street_address' => $street,
                'city' => $request->getDestCity(),
                'state' => $regionName,
                'pincode' => $request->getDestPostcode(),
                'country'=> $country
                ];
                $formattedAddress = implode(',', $addressArray);
                $latLng = $this->helperData->getLatLong($formattedAddress);
                if (!empty($latLng)) {
                    //check for stores
                    $availableStores = $this->getStores($latLng);
                    if (empty($availableStores)) {
                        $erMsg = $this->helperData->getStoreConfig(self::XML_ERROE_MESSAGE_FOR_ADDRESS);
                        return $this->setError($erMsg);
                    }
                } else {
                    $erMsg = $this->helperData->getStoreConfig(self::XML_ERROE_MESSAGE_FOR_ADDRESS);
                    return $this->setError($erMsg);
                }
            } else {
                $setError = true;
            }
        }
        if ($setError == 1) {
            $erMsg = $this->helperData->getStoreConfig(self::XML_ERROE_MESSAGE_FOR_METHOD);
            return $this->setError($erMsg);
        }
        return $this;
    }

    /**
     * Function to return error
     * @param type $errMsg
     * @return type
     */
    public function setError($errMsg) {
        $error = $this->rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage(__($errMsg));
        return $error;
    }

    /**
     * Fetch stores as per latitude and longitude
     * @param type $latLng
     * @return type
     */
    public function getStores($latLng) {
        if(isset($latLng['latitude']) && isset($latLng['longitude'])) {
        $lat = $latLng['latitude'];
        $lng = $latLng['longitude'];
        if(!$this->authSession->isLoggedIn()){
            $items = $this->session->getQuote()->getAllVisibleItems();
            $itemsCount = $this->session->getQuote()->getItemsCount();
        }else{
            $items = $this->backendQuoteSession->getQuote()->getAllVisibleItems();
            $itemsCount = $this->backendQuoteSession->getQuote()->getItemsCount();
        }
        $radius = $this->helperData->getStoreConfig(self::XML_GOOGLE_MAP_RADIUS);
        $result = [];

        $quoteItems = [];
        foreach ($items as $item) {
            $quoteItems[] = $item->getProductId();
        }

        if (!empty($quoteItems)) {
            /** Get stores mapped with all quote items */
            $availableStoresSql = $this->productsFactory->create()->getCollection()
                    ->addFieldToSelect('store_id')
                    ->addFieldToFilter('product_id', ['in' => $quoteItems])
                    ->getSelect()
                    ->group('store_id')
                    ->having('COUNT(*) =? ', $itemsCount);

            /** Get the nearest stores based on lat, long and radius */
            $storesDataSql = $this->storesFactory->create()->getCollection()
                    ->addFieldToSelect(['store_name', 'country_id', 'region', 'region_id', 'street_address', 'city', 'zip_code', 'locality', 'latitude', 'longitude'])
                    ->addFieldToSelect(new \Zend_Db_Expr(
                            "( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude )" .
                            " - radians($lng) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) )"), 'distance')
                    ->addFilterToMap('main_store_id', 'main_table.store_id')
                    ->addFieldToFilter('main_store_id', ['in' => $availableStoresSql])
                    ->addFilterToMap('main_store_status', 'main_table.status')
                    ->addFieldToFilter('main_store_status', ['eq' => self::STORE_SATUS])
                    ->setOrder('distance');

            $storesDataSql->getSelect()
                    ->having('distance <= ? ', $radius);
            /** Prepare json data for ajax call response */
            $data = $storesDataSql->load()->getData();
            if (!empty($data)) {
                foreach ($data as $stores) {
                    $result[] = $stores;
                }
                return json_encode($result);
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
    }
}
