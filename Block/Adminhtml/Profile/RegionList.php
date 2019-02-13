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

namespace Cybage\Storepickup\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template;

class RegionList extends Template
{
    /**
    * @var \Magento\Directory\Model\CountryFactory
    */
    protected $_countryFactory;
    
    /**
     * @var \Magento\Framework\Registry 
     */
    protected $_coreRegistry;
    
    /**
     * RegionList constructor
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $data);
    }
    
    /**
     * Return state list of country
     * @return json
     */
    public function getStates()
    {
        $countrycode = $this->_coreRegistry->registry('store_profile_country_code');
        $regionId = $this->_coreRegistry->registry('store_profile_region_code');
        $state = "<option value=''>--Please Select--</option>";
        $stateCount = 0;
        if ($countrycode != '') {
            $statearray =$this->_countryFactory->create()->setId(
                $countrycode
            )->getLoadedRegionCollection()->toOptionArray();
            $stateCount = count($statearray);
            foreach ($statearray as $_state) {
                if ($_state['value']) {
                    $selected = '';
                    if ($regionId == $_state['value']) {
                        $selected = 'selected';
                    }
                    $state .= "<option value='".$_state['value']."' ".$selected.">" . $_state['label'] . "</option>";
                }
            }
        }
        if ($stateCount) {
            $result['htmlconent']= $state;
        } else {
            $result['htmlconent']= $stateCount;
        }
        return json_encode($result);
    }
}
