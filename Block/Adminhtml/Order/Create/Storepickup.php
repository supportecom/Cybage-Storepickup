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
namespace Cybage\Storepickup\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;
use Magento\Backend\Model\Session\Quote;
use Magento\Sales\Model\AdminOrder\Create;

class Storepickup extends AbstractCreate
{
    /**
     * @var string
     */
    const API_KEY_CONFIG_PATH = 'stores_section/storepickup_map/api_key';
        
    /**
     * @var int
     */
    const LATITUDE_CONFIG_PATH = 'stores_section/storepickup_map/latitude';
            
    /**
     * @var int
     */
    const LONGITUDE_CONFIG_PATH = 'stores_section/storepickup_map/longitude';
            
    /**
     * @var string
     */
    const RADIUS_CONFIG_PATH = 'stores_section/storepickup_map/radius';
        
    /**
     * @var Country
     */
    public $countryHelper;

    /**
     * 
     * @param Country $countryHelper
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Country $countryHelper,
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->countryHelper = $countryHelper;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Get store identifier
     *
     * @return  string
     */
    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get an array of country codes and country names: IN => India
     *
     * @return array
     */
    public function getCountries() {

        $loadCountries = $this->countryHelper->toOptionArray();
        $countries = [];
        $i = 0;
        foreach ($loadCountries as $country) {
            $i++;
            if ($i == 1) { //remove first element that is a select
                continue;
            }
            $countries[$country["value"]] = $country["label"];
        }
        return $countries;
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get api key settings from configuration
     *
     * @return string
     */
    public function getApiKeySettings() {
        return $this->_scopeConfig->getValue(self::API_KEY_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get latitude settings from configuration
     *
     * @return float
     */
    public function getLatitudeSettings() {
        return (float) $this->_scopeConfig->getValue(self::LATITUDE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get longitude settings from configuration
     *
     * @return float
     */
    public function getLongitudeSettings() {
        return (float) $this->_scopeConfig->getValue(self::LONGITUDE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get radius settings from configuration
     *
     * @return float
     */
    public function getRadiusSettings() {
        return (float) $this->_scopeConfig->getValue(self::RADIUS_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get base image url
     *
     * @return string
     */
    public function getBaseImageUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
