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

namespace Cybage\Storepickup\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Sales\Model\Order;

class Details extends Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * Constant for pending order status
     */
    const ORDER_PENDING_STATUS = 'pending';
    
    /**
     * Constant for processing order status
     */
    const ORDER_PROCESSING_STATUS = 'processing';
    
    /**
     * Details constructor
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Template\Context $context, 
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()) 
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    /**
     * Return buttom url
     * @return string
     */
    public function getMarkAsDeliverUrl()
    {
        $orderId = $this->_coreRegistry->registry('store_view_order_id');
        return $this->getUrl('storepickup/stores/processOrder/order_id/'.$orderId);
    }
    
    /**
     * Check order status to maintain button visibility
     * @return boolean
     */
    public function isAvailableForCompletion() {
        $orderStatus = $this->getOrderStatus();
        $allowedStatus = [
                            self::ORDER_PENDING_STATUS, 
                            self::ORDER_PROCESSING_STATUS
                        ];
        if (in_array($orderStatus, $allowedStatus)) {
            return true;
        }
        return false;
    }
    
    /**
     * Return order status
     * 
     * @return string
     */
    public function getOrderStatus() {
        return $this->getOrder()->getStatus();
    }
    
    /**
     * Return order
     * 
     * @return obj
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('order');
    }
    
    /**
     * Return receiver name for delivery
     * @return string
     */
    public function getReceiverName() {
        $firstName =  $this->getOrder()->getShippingAddress()->getFirstname();   
        $lastName  =  $this->getOrder()->getShippingAddress()->getLastname(); 
        return $firstName .' '.$lastName;
    }
    
    /**
     * Return buttom url
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('storepickup/stores/orders');
    }
}
