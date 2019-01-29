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

namespace Cybage\Storepickup\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Cybage\Storepickup\Helper\Data as StorepickupHelper;

class AddHtmlToOrderShippingViewObserver implements ObserverInterface
{
    
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $additionalShippingBlock;
    
    /*
     * @var StorepickupHelper
     */
    protected $storepickupHelper;
    
    /**
    * Observer construct
    *
    * @param Template $additionalShippingBlock
    */
    public function __construct(Template $additionalShippingBlock, StorepickupHelper $storepickupHelper)
    {
        $this->additionalShippingBlock = $additionalShippingBlock;
        $this->storepickupHelper = $storepickupHelper;
    }

    /**
     * Set the vendor details to order shipping view block
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($observer->getElementName() == 'order_shipping_view') {
            $orderShippingViewBlock = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingViewBlock->getOrder();
            if ($order->getShippingMethod() == 'storepickup_storepickup') {
                $details = $this->storepickupHelper->getPickupInterval($order);
                $this->additionalShippingBlock->setPickupFromDate($details['from']);
                $this->additionalShippingBlock->setPickupToDate($details['to']);
                $this->additionalShippingBlock->setTemplate('Cybage_Storepickup::order_info_shipping_info.phtml');
                $html = $observer->getTransport()->getOutput() . $this->additionalShippingBlock->toHtml();
                $observer->getTransport()->setOutput($html);
            }
        }
    }
}
