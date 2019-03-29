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

class ProcessOrder extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;
 
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
 
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;
    
    /**
     * The ShipmentFactory is used to create a new Shipment.
     *
     * @var Order\ShipmentFactory
     */
    protected $shipmentFactory;
    
    /**
     * The ShipmentRepository is used to load, save and delete shipments.
     *
     * @var Order\ShipmentRepository
     */
    protected $shipmentRepository;
    
    /**
     * The ShipmentNotifier class is used to send a notification email to the customer.
     *
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /*
     * @var $_convertOrder
     */
    protected $_convertOrder;
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Sales\Model\Convert\Order $convertOrder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Convert\Order $convertOrder
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->_convertOrder = $convertOrder;
        parent::__construct($context);
    }
    /**
     * Order invoice controller.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id'); //order id for which want to create invoice
        $order = $this->_orderRepository->get($orderId);
        
        //Set registry to provide data to core templates
        $orderReceiverName = $this->getRequest()->getParam('receiver_name');
        $orderDocumentName = $this->getRequest()->getParam('document_name');
        $orderDocumentNo = $this->getRequest()->getParam('document_no');
        
        //Request for creating shipment
        $this->createShipment($order);

        //Add delivery details to comments
        $order->addStatusHistoryComment(
                __('Order delivered to - '.$orderReceiverName. ' | Document Name - '.$orderDocumentName .' | Document No - '.$orderDocumentNo)
            )
        ->save();
        $this->messageManager->addSuccess(__('Order updated successfully.'));
        $this->_redirect('storepickup/stores/orderdetail', ['id' => $orderId, '_current' => true]);
        return;
    }
    
    /**
     * Creates a new shipment for the specified order.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function createShipment($order)
    {
        // Check if order can be shipped or has already shipped
        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('You can\'t create an shipment.')
            );
        }

        // Initialize the order shipment object
        $shipment = $this->_convertOrder->toShipment($order);

        // Loop through order items
        foreach ($order->getAllItems() AS $orderItem) {
            // Check if order item has qty to ship or is virtual
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            $qtyShipped = $orderItem->getQtyToShip();
            // Create shipment item with qty
            $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            // Add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }
        // Register shipment
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        try {
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();
            // Send email
            $this->shipmentNotifier->notify($shipment);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __($e->getMessage())
            );
        }
    }
}
