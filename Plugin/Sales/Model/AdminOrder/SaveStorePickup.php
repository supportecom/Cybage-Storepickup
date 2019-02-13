<?php
 /**
 * Cybage
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
namespace Cybage\Storepickup\Plugin\Sales\Model\AdminOrder;

use Magento\Sales\Model\AdminOrder\Create;
use Cybage\Storepickup\Model\StoresFactory;

class SaveStorepickup
{
    /**
     * @param StoresFactory
     */
    public function __construct(StoresFactory $storeProfilesFactory){
        $this->storeProfilesFactory = $storeProfilesFactory;
    }
    /**
     * Save pickup storeid and pickup store address as shipping address
     * 
     * @param Create $subject
     * @param Create $result
     * @return Create
     */
    public function afterCreateOrder (
        Create $subject,
        $result
    ) {
        $pickupStoreId = $subject->getData('pickupstore_id');
        if ($pickupStoreId) {
            $storeAddress = $this->getStoreAddressAsShipping($pickupStoreId);
            if (count($storeAddress) > 0) {
                $result->getShippingAddress()->addData($storeAddress);
            }
            $result->setData('pickupstore_id', $pickupStoreId);
            if ($result->save()) {
                $this->createInvoice($result);
            }
        }
        return $result;
    }
    
    /**
     * Get the store address to save it as shipping address
     *
     * @param int $pickupStoreId
     * @return array $storeAddress
     */
    protected function getStoreAddressAsShipping($pickupStoreId){
        $storeProfile = $this->storeProfilesFactory->create()->load($pickupStoreId)->getData();
        $storeAddress = [];
        if (count($storeProfile) > 0) {
            $storeAddress = [
                'street' => $storeProfile['street_address'],
                'city' => $storeProfile['city'],
                'country_id' => $storeProfile['country_id'],
                'region' => $storeProfile['region'],
                'postcode' => $storeProfile['zip_code'],
                'region_id' => $storeProfile['region_id']
            ];
        }
        return $storeAddress;
    }
    
    /**
     * Create invoice for storpickup order
     * 
     * @param Create $order
     * @throws LocalizedException
     */
    protected function createInvoice($order) {
        try {
            if ($order->canInvoice()) {
                $invoice = $this->_invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);
                $transaction = $this->_transactionFactory->create()
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());

                $transaction->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new $e('Something went worng, Invoice not created.');
        }
    }
}
