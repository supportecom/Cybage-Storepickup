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


namespace Cybage\Storepickup\Plugin\Backend\Block;

class Menu
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;
    
    /**
     * Menu plugin constructor
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_authSession = $authSession;
    }
    
    /**
     * Plugin to remove my order node if admin user
     * @param \Magento\Backend\Block\Menu $subject
     * @param type $menu
     * @param type $level
     * @param type $limit
     * @param type $colBrakes
     * @return type
     */
    public function beforeRenderNavigation(\Magento\Backend\Block\Menu $subject, $menu, $level = 0, $limit = 0, $colBrakes = [])
    {
        $storeRole = \Cybage\Storepickup\Helper\Data::STORE_ROLE;
        $storeOrderAcl = \Cybage\Storepickup\Helper\Data::STORE_ORDER_ACL;
        $storeProfileAcl = \Cybage\Storepickup\Helper\Data::STORE_PROFILE_ACL;
        
        $userRole = $this->_authSession->getUser()->getRole()->getRoleName();
        
        foreach ($menu as $key => $menuItem) {
            if ($userRole != $storeRole && $menuItem->getId() == $storeOrderAcl) {
                unset($menu[$key]);
            }
        }
        
        foreach ($menu as $key => $menuItem) {
            if ($userRole != $storeRole && $menuItem->getId() == $storeProfileAcl) {
                unset($menu[$key]);
            }
        }
        return [$menu, $level, $limit, $colBrakes];
    }
}