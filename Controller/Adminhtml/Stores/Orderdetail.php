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

namespace Cybage\Storepickup\Controller\Adminhtml\Stores;

use Magento\Sales\Model\Order;

class Orderdetail extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
    
    /**
     * Core Registry
     * @var \Magento\Framework\Registry 
     */
    protected $_coreRegistry;
    
    /**
     * Sales order
     * 
     * @var \Magento\Sales\Model\Order 
     */
    protected $salesOrder;
    
    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Order $salesOrder
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Order $salesOrder,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->salesOrder = $salesOrder;
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');        
        $this->_coreRegistry->register('store_view_order_id', $orderId);
        
        $orderData = $this->salesOrder->load($orderId);
        
        $this->_coreRegistry->register('order', $orderData);
        $this->_coreRegistry->register('current_order', $orderData);
        $this->_coreRegistry->register('sales_order', $orderData);
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cybage_Storepickup::orders');
        $resultPage->getConfig()->getTitle()->prepend(__('Orders Detail'));
        return $resultPage;
    }

    /**
     * Access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cybage_Storepickup::orders');
    }
}
