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
namespace Cybage\StorePickup\Block\Onepage;

use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Sales\Api\OrderRepositoryInterface;

class StorepickupSuccess extends \Magento\Checkout\Block\Onepage\Success {
    
    /**
     * @var OrderRepositoryInterface 
     */
    protected $orderRepository;   

    /**
     * 
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param Context $httpContext
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
       Context $context, 
       Session $checkoutSession, 
       Config $orderConfig, 
       HttpContext $httpContext, 
       OrderRepositoryInterface $orderRepository, 
       array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct(
            $context, $checkoutSession, $orderConfig, $httpContext, $data
        );
    }
    
    /**
     * 
     * @return object
     */
    public function getOrder() {
        return $this->_checkoutSession->getLastRealOrder();
    }
}