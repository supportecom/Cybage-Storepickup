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

namespace Cybage\Storepickup\Block\Adminhtml\Stores;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Cybage\Storepickup\Helper\Data as StorepickupHelper;

/**
 * Class Edit
 */
class Edit extends Container
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var StorepickupHelper 
     */
    protected $storepickupHelper;
    
    /**
     * 
     * @param Context $context
     * @param Registry $registry
     * @param StorepickupHelper $storepickupHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StorepickupHelper $storepickupHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->storepickupHelper = $storepickupHelper;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Cybage_Storepickup';
        $this->_controller = 'adminhtml_Stores';

        parent::_construct();
        
        $isStoreLogin = $this->storepickupHelper->isStoreLogin();
        
        if (!$isStoreLogin) {
            $this->buttonList->update('save', 'label', __('Save'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndContinueEdit',
                                'target' => '#edit_form'
                            ]
                        ]
                    ]
                ],
                -100
            );
        }
        
        $this->buttonList->update('delete', 'label', __('Delete'));
        
        if ($isStoreLogin) {
            $this->removeButton('delete');
            $this->removeButton('back');
        }
    }

    /**
     * Retrieve text for header element depending on loaded news
     * 
     * @return string
     */
    public function getHeaderText()
    {
        $storesRegistry = $this->_coreRegistry->registry('seller_data');
        if ($storesRegistry->getId()) {
            $storesTitle = $this->escapeHtml($storesRegistry->getStoreName());
            return __("Edit - '%1'", $storesTitle);
        } else {
            return __('Add Store');
        }
    }
 
    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";

        return parent::_prepareLayout();
    }
}
