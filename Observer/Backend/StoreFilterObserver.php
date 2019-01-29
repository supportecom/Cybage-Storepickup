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

namespace Cybage\Storepickup\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Cybage\Storepickup\Helper\Data;

/**
 * Class SellerFilterObserver
 */
class StoreFilterObserver implements ObserverInterface
{

    /**
     * @var Cybage\Marketplace\Helper\Data
     */
    protected $_helperData;

    /**
     * @var Array
     */
    protected $_actions = [
        'stores_order_list'
    ];

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param HelperData $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * Manange sales order according to seller
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        if ($this->_helperData->isStoreLogin()) {
            if (in_array($controller->getRequest()->getParam('namespace'), $this->_actions)) {
                $filetrs = $controller->getRequest()->getParam('filters');
                $filetrs['pickupstore_id'] = $this->_helperData->getStoreId();
                $controller->getRequest()->setParam('filters', $filetrs);
            }
        }
        return $this;
    }
}
