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

namespace Cybage\Storepickup\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Cybage\Storepickup\Model\StoresFactory;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\Auth;
use Magento\User\Model\UserFactory;
use Cybage\Storepickup\Model\UserFactory as StoreUserFactory;

class Data extends AbstractHelper {

    /**
     * Constant for store user role
     */
    const STORE_ROLE = 'cybage_stores';

    /**
     * Constant for order acl node of store
     */
    const STORE_ORDER_ACL = 'Cybage_Storepickup::orders';

    /**
     * Constant for profile acl node of store
     */
    const STORE_PROFILE_ACL = 'Cybage_Storepickup::profile';
    
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Magento\Authorization\Model\RoleFactory
     */
    protected $_roleFactory;

    /**
     * @var \Cybage\Storepickup\Model\UserFactory 
     */
    protected $storeUserFactory;

    /**
     * @var Context 
     */
    protected $context;

    /**
     * @var StoresFactory 
     */
    protected $storeProfiles;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * Order Hold Time
     */
    const XML_PATH_HOLD_TIME = 'stores_section/stores_order_settings/order_hold_time';

    /**
     * Maximum store limit to display in storepickup selection 
     */
    const XML_MAX_STORES_LIMIT = 'stores_section/stores_group/max_stores_limit';

    /**
     * @var string
     */
    const RADIUS_CONFIG_PATH = 'stores_section/storepickup_map/radius';

    /**
     * @var string
     */
    const GEOCODE_API_URL_CONFIG_PATH = 'stores_section/storepickup_map/geocodeapi_url';

    /**
     * Google maps API KEY
     */
    const API_KEY = 'stores_section/storepickup_map/api_key';

    /**
     * 
     * @param Context $context
     * @param UrlInterface $backendUrl
     * @param Auth $auth
     * @param UserFactory $userFactory
     * @param RoleFactory $roleFactory
     * @param StoreUserFactory $storeUserFactory
     */
    public function __construct(
        Context $context,
        UrlInterface $backendUrl,
        StoresFactory $storeProfiles,
        TimezoneInterface $timezoneInterface,
        Auth $auth,
        UserFactory $userFactory,
        RoleFactory $roleFactory,
        StoreUserFactory $storeUserFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeProfiles = $storeProfiles;
        $this->_timezoneInterface = $timezoneInterface;
        $this->backendUrl = $backendUrl;
        $this->_auth = $auth;
        $this->_userFactory = $userFactory;
        $this->_roleFactory = $roleFactory;
        $this->storeUserFactory = $storeUserFactory;
        parent::__construct($context);
    }

    /**
     * Get Any Store Configuration
     * 
     * @param string $storePath Full path of any configuration
     * @return string $storeConfig
     */
    public function getStoreConfig($storePath) {
        $storeConfig = $this->scopeConfig->getValue($storePath, ScopeInterface::SCOPE_STORE);
        return $storeConfig;
    }

    /**
     * Get the pickup details for an order
     * 
     * @param Magento/Sales/Model/Order $order
     * @return array
     */
    public function getPickupInterval($order) {
        $orderCreatedAt = $this->_timezoneInterface->date($order->getCreatedAt())->format('d M Y H:i');
        $pickupStoreId = $order->getPickupstoreId();
        $storePickupInterval = $this->getPickupStoreData($pickupStoreId)->getPickupInterval();
        $storeCloseTime = str_replace(',', ':', $this->getPickupStoreData($pickupStoreId)->getStoreCloseTime());
        $storeOpenTime = str_replace(',', ':', $this->getPickupStoreData($pickupStoreId)->getStoreStartTime());
        $orderTime = strtotime($orderCreatedAt);
        $orderPickuptime = $this->addTime($storePickupInterval, $orderCreatedAt);
        if (strtotime(date('H:i', $orderTime)) >= strtotime($storeOpenTime) && strtotime(date('H:i', $orderTime)) <= strtotime($storeCloseTime)) {
            if (date('H:i', strtotime($orderPickuptime)) >= $storeCloseTime) {
                $orderPickuptime = $this->getNextDayPickuptTime($orderTime, $storeOpenTime, $storePickupInterval);
            }
        } else {
            $beforeMidnight = strtotime(date('d M Y 23:59:59'));
            if ($orderTime < $beforeMidnight) {
                if ($orderTime < strtotime(date('d M Y '.$storeOpenTime))) {
                    $sameDay = date('d M Y', $orderTime);
                    $hrs =  (int)$storeOpenTime + $storePickupInterval;
                    $orderPickuptime =  $this->addTime($hrs, $sameDay);
                } else {
                    $orderPickuptime = $this->getNextDayPickuptTime($orderTime, $storeOpenTime, $storePickupInterval);
                }
            }
        }
        $orderHoldDate = $this->getHoldDate($orderPickuptime);
        $pickupDetails = [
            'from' => date('d M Y h:i A', strtotime($orderPickuptime)),
            'to' => $orderHoldDate,
            'open_time' => date('h:i A', strtotime($storeOpenTime)),
            'close_time' => date('h:i A', strtotime($storeCloseTime))
        ];
        return $pickupDetails;
    }
    
    /**
     * Get Next Day pickup time
     * 
     * @param date $orderTime
     * @param time $storeOpenTime
     * @param int $storePickupInterval
     * @return date $orderPickuptime
     */
    protected function getNextDayPickuptTime($orderTime, $storeOpenTime, $storePickupInterval) {
        $nextDay = date('d M Y', strtotime(sprintf('+%d day', 1), $orderTime));
        $hrs =  (int)$storeOpenTime + $storePickupInterval;    
        $orderPickuptime =  $this->addTime($hrs, $nextDay);
        return $orderPickuptime;
    }
    
    protected function addTime($hours, $toDays){
        return date('d M Y H:i', strtotime(sprintf('+%d hours', $hours), strtotime($toDays)));
    }

    /**
     * Get Hold date from hold days
     * 
     * @param string $orderPickuptime
     * @return string
     */
    public function getHoldDate($orderPickuptime) {
        $orderHoldDays = $this->getStoreConfig(self::XML_PATH_HOLD_TIME);
        return $orderHoldtime = date('d M Y ', strtotime(sprintf("+%d days", $orderHoldDays), strtotime($orderPickuptime)));
    }

    /**
     * Get pickup store Data
     * 
     * @param int $pickupStoreId
     * @return array
     */
    public function getPickupStoreData($pickupStoreId) {
        $storeModel = $this->storeProfiles->create()->getCollection()
                ->addFieldToFilter('store_id', $pickupStoreId)
                ->getFirstItem();
        return $storeModel;
    }

    /**
     * Get limit of number of stores allowed
     * 
     * @return int
     */
    public function getMaxStoresLimit() {
        return $this->getStoreConfig(self::XML_MAX_STORES_LIMIT);
    }

    /**
     * Product grid url
     * @return string
     */
    public function getProductsGridUrl() {
        return $this->backendUrl->getUrl('storepickup/stores/products', ['_current' => true]);
    }

    /**
     * Return User model object
     * @return string
     */
    public function getUserModel() {
        return $this->_userFactory;
    }

    /**
     * Return Role model object
     * @return string
     */
    public function getRoleModel() {
        return $this->_roleFactory;
    }

    /**
     * Returns store user model
     * 
     * @return obj
     */
    public function getStoreUserModel() {
        return $this->storeUserFactory;
    }

    /**
     * Chcek if login user is admin or store
     * @return int|bool
     */
    public function isStoreLogin() {
        if ($this->_auth->getUser()) {
            $roles = $this->getRoleModel()->create()->getCollection()
                            ->addFieldToFilter('role_name', self::STORE_ROLE)->getFirstItem();
            $defaultRole = $roles['role_id'];
            $userId = $this->_auth->getUser()->getId();
            $user = $this->_userFactory->create()->load($userId);
            $role = $user->getRole();
            $data = $role->getData();
            if ($data['role_id'] == $defaultRole) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Store Id
     *
     * @return int|bool
     */
    public function getStoreId() {
        if ($this->_auth->getUser()) {
            $userId = $this->_auth->getUser()->getId();
            $storeUserData = $this->getStoreUserModel()->create()->getCollection()
                            ->addFieldToFilter('user_id', $userId)->getFirstItem();
            return $storeUserData['store_id'];
        }
        return false;
    }

    /**
     * Returns all stores
     * 
     * @return obj
     */
    public function getAllStores() {
        $storeProfileModel = $this->storeProfiles->create()->getCollection();
        if ($this->isStoreLogin() && $this->getStoreId()) {
            $storeProfileModel->addFieldToFilter('store_id', $this->getStoreId());
        }
        return $storeProfileModel;
    }

    /**
     * get radius settings from configuration
     *
     * @return float
     */
    public function getRadiusSettings() {
        return (float) $this->getStoreConfig(self::RADIUS_CONFIG_PATH);
    }

    /**
     * get geocode api url to get lat long
     * @return string
     */
    public function getGeocodeApiUrl() {
        return $this->getStoreConfig(self::GEOCODE_API_URL_CONFIG_PATH);
    }

    /**
     * Get the latitude and longitude from the google maps
     * 
     * @param string $address
     * @return array | boolean
     */
    public function getLatLong($address, $regionName = '') {
        $error_msg = '';
        if (!empty($address)) {
            $address = str_replace(" ", "+", $address);
            $address =  preg_replace('~[\r\n\t]+~', '', $address);
            $url = $this->getStoreConfig(self::GEOCODE_API_URL_CONFIG_PATH);
            $apiKey = $this->getStoreConfig(self::API_KEY);

            $url = $url . $address . "&sensor=false";
            if ($regionName)
                $url .= "&region=" . $regionName;
            $url = $url . '&key=' . $apiKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
            }
            curl_close($ch);
            $jsonArray = json_decode($response, 1);
            if (isset($jsonArray) && !empty($jsonArray['results'])) {
                $data['latitude'] = $jsonArray['results'][0]['geometry']['location']['lat'];
                $data['longitude'] = $jsonArray['results'][0]['geometry']['location']['lng'];
            } else {
                $data['error'] =$error_msg;
            }
            return $data;
        }
        return false;
    }

    /**
     * Get User Id
     *
     * @return int|bool
     */
    public function getUserId($storeId) {
        if ($storeId) {
            $storeUserData = $this->getStoreUserModel()->create()->getCollection()
                            ->addFieldToFilter('store_id', $storeId)->getFirstItem();
            return $storeUserData['user_id'];
        }
        return false;
    }

    /**
     * Delete store user
     * @param type $userId
     */
    public function deleteStoreUser($userId) {
        if ($userId) {
            $userModel = $this->_userFactory->create()->load($userId);
            $userModel->delete();
        }
    }

}
