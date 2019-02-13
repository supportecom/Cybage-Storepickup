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

namespace Cybage\Storepickup\Model\Config\Source;

use Cybage\Storepickup\Helper\Data;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Cybage\Storepickup\Helper\Data
     */
    protected $helper;
    
    /**
     * @param Data $helper
     */
    public function __construct(Data $helper) {
        $this->helper = $helper;
    }
    
    /**
     * Return all stores
     * @return array
     */
    public function getAllOptions()
    {
        $storesProfiles = $this->helper->getAllStores();
        $storesOptions = [];
        foreach ($storesProfiles as $key => $storeData) {
             $storesOptions[$key]['label'] = $storeData->getStoreName();
             $storesOptions[$key]['value'] = $storeData->getStoreId();
        }
        return $storesOptions;
    }
}
