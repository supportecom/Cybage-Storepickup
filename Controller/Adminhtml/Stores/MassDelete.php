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
use Magento\Ui\Component\MassAction\Filter;
use Cybage\Storepickup\Model\ResourceModel\Stores\CollectionFactory;
use Cybage\Storepickup\Model\StoresFactory;
use Cybage\Storepickup\Helper\Data;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     * @var Filter
     */
    protected $_filter;

    /**
     * @var \Cybage\Storepickup\Model\ResourceModel\Stores\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * Stores Model
     * @var \Cybage\Storepickup\Model\StoresFactory 
     */
    protected $storesModel;

    /**
     * Store helper
     * 
     * @var \Cybage\Storepickup\Helper\Data 
     */
    protected $helper;
    
    /**
     * 
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param StoresFactory $storesModel
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        StoresFactory $storesModel,
        Data $helper
    ) {

        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->storesModel = $storesModel;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $recordDeleted = 0;
        foreach ($collection as $record) {
            $storeId = $record->getStoreId();
            //Delete store user
                $userId = $this->helper->getUserId($storeId);
                $this->helper->deleteStoreUser($userId);
            //End
          
            $storesModel = $this->storesModel->create()->load($storeId);
            $storesModel->delete();
            $recordDeleted++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Access rights checking
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cybage_Storepickup::row_data_delete');
    }
}
