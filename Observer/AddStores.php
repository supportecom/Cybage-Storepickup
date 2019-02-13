<?php

/**
 * Cybage Store Pickup Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_ecom@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Adminhtml Orders storepickup store selector
 * @package    Cybage_Storepickup
 * @copyright  Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India
 * http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Storepickup\Observer;

use Psr\Log\LoggerInterface as Logger;
//use Cybage\Storepickup\Helper\Data as HelperData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Cybage\Storepickup\Model\StoresFactory;
use Magento\Quote\Model\Quote\AddressFactory;

class AddStores implements \Magento\Framework\Event\ObserverInterface {

   

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     *
     * @var CheckoutSession
     */
    protected $checkoutSession;
    
    /**
     *
     * @var StoresFactory
     */
    protected $storeProfiles;
    
    /**
     *
     * @var AddressFactory
     */
    protected $quoteAddressFactory;
    
    /**
     *
     * @param HelperData $helperData
     * @param StoresFactory $storeProfiles
     * @param CheckoutSession $checkoutSession
     * @param AddressFactory $quoteAddressFactory
     * @param Logger $logger
     */
    public function __construct(
        StoresFactory $storeProfiles,
        CheckoutSession $checkoutSession,
        AddressFactory $quoteAddressFactory,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->storeProfiles = $storeProfiles;
        $this->checkoutSession = $checkoutSession;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $addr = [];
        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getShippingAddress()->getShippingMethod() == 'storepickup_storepickup') {
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
                    $quoteShippingAddress = $this->quoteAddressFactory->create();
                    $quoteShippingAddress->setData($addr);
                    $quote->setShippingAddress($quoteShippingAddress);
                    $quote->setShippingMethod($quote->getShippingAddress()->getShippingMethod());
                    $quote->save();
                    $order = $observer->getEvent()->getOrder();
                    $order->getShippingAddress()->addData($addr);
                }
            }
        }
    }
}
