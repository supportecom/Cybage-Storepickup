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
 * @category   Store Pickup Plugin
 * @package    Cybage_Storepickup
 * @copyright  Copyright (c) 2019 Cybage Software Pvt. Ltd., India
 * http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Storepickup\Controller\Adminhtml\Stores;

use Cybage\Storepickup\Controller\Adminhtml\Stores;

class Delete extends Stores
{
   /**
    * @return void
    */
   public function execute()
   {
      $storeId = (int) $this->getRequest()->getParam('id');

      if ($storeId) {
          //Delete store user
          $userId = $this->helper->getUserId($storeId);
          $this->helper->deleteStoreUser($userId);
          //End
         /** @var $storesModel \Cybage\Storepickup\Model\StoresFactory */
         $storesModel = $this->storesFactory->create();
         $storesModel->load($storeId);
                        
         // Check this store exists or not
         if (!$storesModel->getStoreId()) {
            $this->messageManager->addError(__('This store no longer exists.'));
         } else {
               try {
                  // Delete store
                  $storesModel->delete();
                  $this->messageManager->addSuccess(__('The store has been deleted.'));

                  // Redirect to grid page
                  $this->_redirect('*/*/');
                  return;
               } catch (\Exception $e) {
                   $this->messageManager->addError($e->getMessage());
                   $this->_redirect('*/*/addrow', ['id' => $storesModel->getStoreId()]);
               }
            }
      }
   }
   
   /**
    * Access rights checking
    * 
    * @return bool
    */
   protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cybage_Storepickup::row_data_delete');
    }
}