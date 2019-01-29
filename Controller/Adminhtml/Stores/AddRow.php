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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Cybage\Storepickup\Model\StoresFactory;
use Cybage\Storepickup\Model\UserFactory as LocalUserFactory;
use Magento\User\Model\UserFactory;
use Cybage\Storepickup\Helper\Data;

class AddRow extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Cybage\Storepickup\Model\StoresFactory
     */
    private $storesFactory;
    
    /**
     * @var \Cybage\Storepickup\Model\UserFactory 
     */
    private $userFactory;
    
    /**
     * @var Magento\User\Model\UserFactory
     */
    private $adminUserFactory;
    
    /**
     * @var \Cybage\Storepickup\Helper\Data 
     */
    protected $helper;
    /**
     * 
     * @param Context $context
     * @param Registry $coreRegistry
     * @param StoresFactory $storesFactory
     * @param LocalUserFactory $userFactory
     * @param UserFactory $adminUserFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        StoresFactory $storesFactory,
        LocalUserFactory $userFactory,
        UserFactory $adminUserFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->storesFactory = $storesFactory;
        $this->userFactory = $userFactory;
        $this->adminUserFactory = $adminUserFactory;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('id');
        
        //Assign login store id in case of store user
        if ($rowId == 0) {
            $isStoreUser = $this->helper->isStoreLogin();
            if ($isStoreUser) {
                $rowId = $this->helper->getStoreId();
            }
        }
        //End

        $rowData = $this->storesFactory->create();
        $userData = $this->userFactory->create();
        $adminUserData = $this->adminUserFactory->create();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($rowId) {
            $rowData = $rowData->load($rowId);
            $userData = $userData->load($rowId, 'store_id');
            $userId = $userData->getUserId();
            $adminUserData = $adminUserData->load($userId);
            
            $rowTitle = $rowData->getstoreName();
            if (!$rowData->getStoreId()) {
                $this->messageManager->addError(__('store data no longer exist.'));
                $this->_redirect('storepickup/stores/rowdata');
                return;
            }
        }
        $this->coreRegistry->register('row_data', $rowData);
        $this->coreRegistry->register('user_data', $adminUserData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $rowId ? __('Edit - ').$rowTitle : __('Add Store');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
    
    /**
     * Access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cybage_Storepickup::add_row');
    }
}
