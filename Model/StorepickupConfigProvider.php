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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * class StorepickupConfigProvider
 */
class StorepickupConfigProvider implements ConfigProviderInterface {

    /**
     * const for google map api key
     */
    const XML_PATH_GOOGLE_URL_API = 'stores_section/storepickup_map/api_key';

    /**
     * const for google map zoom
     */
    const XML_PATH_GOOGLE_MAP_ZOOM = 'stores_section/storepickup_map/zoom';

    /**
     * const for google map radius
     */
    const XML_PATH_GOOGLE_MAP_REDIUS = 'stores_section/storepickup_map/radius';

    /**
     * const for google map radius
     */
    const XML_PATH_GOOGLE_MAP_LATITUDE = 'stores_section/storepickup_map/latitude';

    /**
     * const for google map radius
     */
    const XML_PATH_GOOGLE_MAP_LONGITUDE = 'stores_section/storepickup_map/longitude';

    /**
     * const for google map store zoom level
     */
    const XML_PATH_GOOGLE_MAP_ZOOM_INDIVIDUAL = 'stores_section/storepickup_individual/zoom_individual';

    /**
     * Error message for address
     */
    const XML_ERROE_MESSAGE_FOR_ADDRESS = 'stores_section/storepickup_error_message/method_not_available_for_address';

    /**
     *
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $config = [
            'storepickup' => [
                'selected_shipping_method' => 'storepickup_storepickup',
                'google' => [
                    'apiUrl' => $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_URL_API, $storeScope),
                ],
                'center' => [
                    'lat' => $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_MAP_LATITUDE, $storeScope),
                    'lng' => $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_MAP_LONGITUDE, $storeScope),
                ],
                'radius' => $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_MAP_REDIUS, $storeScope),
                'zoom' => $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_MAP_ZOOM, $storeScope),
                'markerzoom' => $this->scopeConfig->getValue(self::XML_PATH_GOOGLE_MAP_ZOOM_INDIVIDUAL, $storeScope),
                'addressError' => $this->scopeConfig->getValue(self::XML_ERROE_MESSAGE_FOR_ADDRESS, $storeScope),
            ]
        ];
        return $config;
    }
}
