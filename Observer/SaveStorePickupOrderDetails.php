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
 * @category   Cybage Storepickup Plugin
 * @package    Cybage_Storepickup
 * @copyright  Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India
 * http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Storepickup\Observer;

use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\TransactionFactory;
use Psr\Log\LoggerInterface;
use Cybage\Storepickup\Model\StoresFactory;

/**
 * Class CheckoutSubmitAfter
 *
 */
class SaveStorePickupOrderDetails implements ObserverInterface {

    /**
     *
     * @var InvoiceService 
     */
    protected $_invoiceService;

    /**
     *
     * @var TransactionFactory 
     */
    protected $_transactionFactory;

    /**
     *
     * @var StoresFactory 
     */
    protected $storeProfiles;

    /**
     *
     * @var LoggerInterface 
     */
    protected $logger;
    
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * 
     * @param QuoteRepository $quoteRepository
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param StoresFactory $storeProfiles
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        StoresFactory $storeProfiles,
        LoggerInterface $logger
    ) {
        $this->_invoiceService = $invoiceService;
        $this->_transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->storeProfiles = $storeProfiles;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $order->setPickupstoreId($quote->getPickupstoreId());
        if ($order->getShippingMethod() == 'storepickup_storepickup') {
            $pickupStoreId = $quote->getPickupstoreId();
            if ($pickupStoreId != 0 && $pickupStoreId != "") {
                //Get store details
                $storeModel = $this->storeProfiles->create()->getCollection();
                $storeModel->addFilterToMap('main_store_id', 'main_table.store_id');
                $storeModel->addFieldToFilter('main_store_id', $pickupStoreId)->getFirstItem();
                $storeDetails = $storeModel->getData();
                if (!empty($storeDetails)) {
                    $storeStreet = $storeDetails[0]['street_address'];
                    $storeCountryId = $storeDetails[0]['country_id'];
                    $storeRegion = $storeDetails[0]['region'];
                    $storeRegionId = $storeDetails[0]['region_id'];
                    $storePostcode = $storeDetails[0]['zip_code'];
                    $storeCity = $storeDetails[0]['city'];
                    $storeContactPerson = $storeDetails[0]['contact_person'];
                    $storeContactNumber = $storeDetails[0]['contact_no'];
                    $addr = [
                        'street' => $storeStreet,
                        'city' => $storeCity,
                        'country_id' => $storeCountryId,
                        'region' => $storeRegion,
                        'region_id' => $storeRegionId,
                        'postcode' => $storePostcode,
                        'telephone' => $storeContactNumber,
                        'firstname' => $storeContactPerson,
                        'lastname' => ''
                    ];
                    $order->getShippingAddress()->addData($addr);
                    $order->save();
                }
            }
            try {
                if (!$order->canInvoice()) {
                    return null;
                }
                if (!$order->getState() == 'new') {
                    return null;
                }
                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);
                $transaction = $this->_transactionFactory->create()
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                $transaction->save();
            } catch (\Exception $e) {
                $order->addStatusHistoryComment('Exception message: ' . $e->getMessage(), false);
                $order->save();
                return null;
            }
        }
    }
}
