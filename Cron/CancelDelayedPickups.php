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
namespace Cybage\Storepickup\Cron;

use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Cybage\Storepickup\Model\StoresFactory;
use Cybage\Storepickup\Model\UserFactory;
use Cybage\Storepickup\Helper\Data as StorepickupData;
use Psr\Log\LoggerInterface;

class CancelDelayedPickups {
 
    /**
     * @var LoggerInterface 
     */
    protected $_logger;
    
    /**
     * @var DateTime
     */
    protected $datetime;
    
    /**
     * OrderManagementInterface
     */
    protected $orderManagementInterface;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;
       
    /**
     * @var StateInterface 
     */
    protected $inlineTranslation;
    
    /**
     * @var TemplateInterface
     */
    protected $templateInterface;
    
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var StoresFactory
     */
    protected $storeProfiles;

    /**
     * @var array 
     */
    protected $status = [Order::STATE_COMPLETE, Order::STATE_CANCELED, Order::STATE_CLOSED];
    
    /**
     * Order Hold Time
     */
    const XML_PATH_HOLD_TIME = 'stores_section/stores_order_settings/order_hold_time';
    
    /**
     * Module Enable
     */
    const XML_PATH_MODULE_ENABLE = 'stores_section/stores_group/enable';
    
    /**
     * Order Auto Cancel
     */
    const XML_PATH_AUTO_CANCEL = 'stores_section/stores_order_settings/order_enable_autocancel';
    
     /**
     * Sender email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'sales_email/order/identity';
    
    protected $comment = "Order canceled due to unavailability of customer to pickup the order during stipulated time";
    
    /**
     * 
     * @param DateTime $datetime
     * @param OrderManagementInterface $orderManagementInterface
     * @param LoggerInterface $logger
     * @param OrderFactory $orderFactory
     * @param StateInterface $inlineTranslation
     * @param TemplateInterface $templateInterface
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StoresFactory $storeProfiles
     * @param StorepickupData $storePickupHelper
     * @param UserFactory $userFactory
     */
    public function __construct(
        DateTime $datetime, 
        OrderManagementInterface $orderManagementInterface, 
        LoggerInterface $logger, 
        OrderFactory $orderFactory, 
        StateInterface $inlineTranslation,
        TemplateInterface $templateInterface,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StoresFactory $storeProfiles,
        StorepickupData $storePickupHelper,
        UserFactory $userFactory
    ) {
        $this->_logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->order = $orderManagementInterface;
        $this->datetime = $datetime;
        $this->templateInterface =  $templateInterface;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->userFactory = $userFactory;
        $this->storeProfiles = $storeProfiles;
        $this->storePickupHelper = $storePickupHelper;
    }
    
    /**
     *
     * @return \Cybage\Storepickup\Controller\Index\Cancel
     */
    public function runCron() {
        if(!$this->isAutoCancelEnabled()){
            return;
        }
        $this->_logger->info('Running Cron for Delayed storepickup orders');
        $this->getDelayedOrders();
        return $this;
    }
    
    /**
     * Get delayed orders which are not yet pickup by customer and exceeds the hold time
     */
    public function getDelayedOrders() {
        $orderCollection = $this->orderFactory->create()->getCollection();
        $orderCollection->addFieldToSelect('entity_id');
        $orderCollection->addFieldToSelect('increment_id');
        $orderCollection->addFieldToSelect('created_at');
        $orderCollection->addFieldToSelect('pickupstore_id');
        $orderCollection->addFieldToFilter('shipping_method', ['eq' => 'storepickup_storepickup']);
        $orderCollection->addFieldToFilter('status', ['nin' => $this->status]);
        $delayedOrders = [];
        foreach ($orderCollection->getData() as $data) {
            if ($data['created_at']) {
                if ($this->getDateDifference($data['created_at']) > $this->getMaxHoldTime()) {
                    $delayedOrders[] = [
                        'id' => $data['entity_id'],
                        'increment_id' => $data['increment_id'],
                        'pickupstore_id' => $data['pickupstore_id']
                    ];
                }
            }
        }
        if (count($delayedOrders) > 0) {
            $this->cancelOrdersAndSendMail($delayedOrders);
        }
    }

    /**
     * Get difference between dates
     *
     * @param mixed $created
     * @return false|int
     */
    public function getDateDifference($created) {
        $now = $this->datetime->gmtDate();

        $datediff = strtotime($now) - strtotime($created);
        return round($datediff / (60 * 60 * 24));
    }

    /**
     * Cancel Delayed orders and send email
     *
     * @param array $delayedOrdersArr
     * @throws Exception
     */
    public function cancelOrdersAndSendMail($delayedOrdersArr) {
        try {
            foreach ($delayedOrdersArr as $order) {
                $orderId = $order['id'];
                $order = $this->orderFactory->create()->load($orderId);
                $this->order->cancel($orderId);
                $order->addStatusToHistory(Order::STATE_CANCELED, $this->comment);
                $order->save();
                $templateVariable = [
                    'store' => $this->_storeManager->getStore(),
                    'order' => $order
                ];
                $store = $this->getPickupStoreData($order['pickupstore_id']);
                $customerName = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
                $customerInfo   = ['name'=> $customerName, 'email'=>$order->getCustomerEmail()];
                $storeOwnerInfo = ['name'=>$store['name'], 'email'=>$store['email']];
                $receivers = [$customerInfo, $storeOwnerInfo];
                $this->sendMail($templateVariable, $receivers);
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
    
    /**
     * Send email to customer and store contact
     *
     * @param array $emailTemplateVariables
     * @param array $receivers
     * @throws Exception
     */
    public function sendMail($emailTemplateVariables, $receivers) {
        $this->_inlineTranslation->suspend();
        try {
            foreach ($receivers as $receiverInfo) {
                $this->generateTemplate($emailTemplateVariables, $receiverInfo);
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_logger->info('Successfully sent email');
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }
    
    /**
     * Generate email template
     *
     * @param array $emailTemplateVariables
     * @param array $receiverInfo
     * @throws Exception
     */
    public function generateTemplate($emailTemplateVariables, $receiverInfo) {
        try {
            $this->_transportBuilder
                    ->setTemplateIdentifier('storepickup_order_cancel_to_storeowner')
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom($this->helper->getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT))
                    ->addTo($receiverInfo['email'], $receiverInfo['name']);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
    
    /**
     * Get store contact person name and email address
     *
     * @param int $pickupStoreId
     * @return array
     */
    protected function getPickupStoreData($pickupStoreId) {
        $storeModel = $this->storeProfiles->create()->getCollection()
                    ->addFieldToFilter('store_id', $pickupStoreId)
                    ->getFirstItem();
        $storeContactName = $storeModel->getContactPerson();
        $storeEmailId = $this->getStoreEmailId($storeModel->getStoreId());
        return ['name'=>$storeContactName, 'email'=>$storeEmailId];
    }
    
    /**
     * Get store user email address
     * 
     * @param int $storeId
     * @return string
     */
    protected function getStoreEmailId($storeId) {
        $user = $this->userFactory->create()->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->getFirstItem();
        return $user->getEmail();
    }

    /**
     * Is auto cancel allowed for orders exceeding hold time
     * @return bool
     */
    protected function isAutoCancelEnabled() {
        if ($this->storePickupHelper->getStoreConfig(SELF::XML_PATH_MODULE_ENABLE)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get max hold time to cancel delayed orders
     * @return type
     */
    protected function getMaxHoldTime() {
        return $this->storePickupHelper->getStoreConfig(self::XML_PATH_HOLD_TIME);
    }
}
