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
namespace Cybage\Storepickup\Plugin\Sales\Block\Adminhtml\Order\View;
 
class Info
{
    /**
     * Change the template for renaming shipping address to storepickup address
     *
     * @param \Temando\Shipping\Block\Adminhtml\Sales\Order\View\Info $subject
     */
    public function beforeToHtml(\Temando\Shipping\Block\Adminhtml\Sales\Order\View\Info $subject)
    {
        if ($subject->getTemplate() === 'Temando_Shipping::sales/order/view/info.phtml') {
            $subject->setTemplate('Cybage_Storepickup::sales/order/view/info.phtml');
        }
    }
}
