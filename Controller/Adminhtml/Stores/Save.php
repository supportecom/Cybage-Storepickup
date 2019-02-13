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

use Cybage\Storepickup\Helper\Data as StorepickupHelper;
use Magento\Authorization\Model\RoleFactory;
use Cybage\Storepickup\Model\StoresFactory;
use Cybage\Storepickup\Model\UserFactory as StoreUserFactory;
use Cybage\Storepickup\Model\ProductsFactory;
use Cybage\Storepickup\Model\ResourceModel\Products as ProductResource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\User\Model\UserFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\File\Csv;

class Save extends \Magento\User\Controller\Adminhtml\User {

    /**
     * @var \Magento\Authorization\Model\RoleFactory
     */
    private $roleFactory;

    /**
     * @var \Cybage\Storepickup\Model\StoresFactory
     */
    protected $storesFactory;

    /**
     * @var \Cybage\Storepickup\Model\UserFactory
     */
    protected $storeUserFactory;

    /**
     * @var \Cybage\Storepickup\Model\ProductsFactory
     */
    protected $storeProductFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Zend_File_Transfer
     */
    protected $_zend;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     *
     * @var \Cybage\Storepickup\Model\ResourceModel\Products
     */
    protected $productResource;

    /**
     * @var StorepickupHelper
     */
    protected $storepickupHelper;

    /**
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param UserFactory $userFactory
     * @param RoleFactory $roleFactory
     * @param StoresFactory $storesFactory
     * @param StoreUserFactory $storeUserFactory
     * @param ProductsFactory $storeProductFactory
     * @param ProductRepositoryInterface $productRepository
     * @param \Zend_File_Transfer $zend
     * @param Csv $csvProcessor
     * @param ProductResource $productResource
     * @param StorepickupHelper $storepickupHelper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        UserFactory $userFactory,
        RoleFactory $roleFactory,
        StoresFactory $storesFactory,
        StoreUserFactory $storeUserFactory,
        ProductsFactory $storeProductFactory,
        ProductRepositoryInterface $productRepository,
        \Zend_File_Transfer $zend,
        Csv $csvProcessor,
        ProductResource $productResource,
        StorepickupHelper $storepickupHelper
    ) {
        $this->roleFactory = $roleFactory;
        $this->storesFactory = $storesFactory;
        $this->storeUserFactory = $storeUserFactory;
        $this->productRepository = $productRepository;
        $this->storeProductFactory = $storeProductFactory;
        $this->_zend = $zend;
        $this->csvProcessor = $csvProcessor;
        $this->productResource = $productResource;
        $this->storepickupHelper = $storepickupHelper;
        parent::__construct($context, $coreRegistry, $userFactory);
    }

    /**
     * @return void
     */
    public function execute() {
        $isPost = $this->getRequest()->getPost();
        if ($isPost) {
            $data = $this->getRequest()->getPostValue();

            $storesModel = $this->storesFactory->create();
            $storeId = 0;
            if ($this->getRequest()->getParam('store_id')) {
                $storeId = $this->getRequest()->getParam('store_id');
                $storesModel->load($storeId);
            }
            $formData = $this->getRequest()->getParam('store_form');
            if (isset($formData['store_id'])) {
                $storeId = $formData['store_id'];
                $storesModel->load($storeId);
            }
            $addressArray = [
                'street_address' => $formData['street_address'],
                'city' => $formData['city'],
                'state' => $formData['region'],
                'pincode' => $formData['zip_code'],
                'country'=> $formData['country_id']
            ];
            $formattedAddress = implode(',', $addressArray);
            $latLongData = $this->storepickupHelper->getLatLong($formattedAddress);
            
            if (isset($latLongData['longitude']) && isset($latLongData['latitude'])) {
                $formData['latitude']  = $latLongData['latitude'];
                $formData['longitude'] = $latLongData['longitude'];
            } elseif (isset($latLongData['error'])) {
                $this->messageManager->addError($latLongData['error']);
                $this->_getSession()->setFormData($formData);
                $this->_redirect('storepickup/stores/addrow/id'.$storeId);
            }

            $startTime = $formData['store_start_time'];
            $startTime = $startTime[0].','.$startTime[1].','.$startTime[2];
            $formData['store_start_time'] = $startTime;

            $closeTime = $formData['store_close_time'];
            $closeTime = $closeTime[0].','.$closeTime[1].','.$closeTime[2];
            $formData['store_close_time'] = $closeTime;
            $storesModel->setData($formData);

            try {
                // Save profile
                $storesModel->save();
                //End store profile
                $storeId = $storesModel->getId();

                if ($storeId) {
                    //Create store user
                    $userData = $data['user_form'];
                    $userId = 0;
                    if (isset($userData['user_id'])) {
                        $userId = $userData['user_id'];
                    }

                    $roles = $this->getRoleModel()->create()->getCollection()
                        ->addFieldToFilter('role_name', \Cybage\Storepickup\Helper\Data::STORE_ROLE)
                        ->getFirstItem();

                    $model = $this->_userFactory->create()->load($userId);
                    $model->setData($this->_getAdminUserData($userData));
                    $model->setRoleId($roles['role_id']);
                    try {
                        $model->save();
                    } catch (\Exception $ex) {
                        $this->messageManager->addError($ex->getMessage());
                        $this->_getSession()->setFormData($formData);
                        $this->_redirect('storepickup/stores/addrow/'.$storeId);
                    }
                    //End store user

                    $storeUserId = $model->getId();
                    $storesUserModel = $this->storeUserFactory->create();
                    if (!($userId)) {
                        $storesUserModel->setStoreId($storeId);
                        $storesUserModel->setUserId($storeUserId);
                        $storesUserModel->save();
                        $storeUserId = $storesUserModel->getId();
                    }

                    //Code to remove unchecked mapping
                    if (isset($data['products']) && !empty($data['products'])) {
                        $mappedProducts = explode('&', $data['products']);
                        $storeId = $data['store_form']['store_id'];

                        $storeProductsCollection = $this->storeProductFactory->create()->getCollection();
                        $storeProductsCollection->addFieldToSelect('product_id');
                        $storeProductsCollection->addFieldToFilter('store_id', $storeId);

                        $productIds = [];
                        foreach ($storeProductsCollection as $storeProducts) {
                            $productIds[] = $storeProducts->getProductId();
                        }
                        $removedProducts = array_diff($productIds, $mappedProducts);
                        $result = $this->productResource->deleteUnmappedProduct($removedProducts, $storeId);
                    }
                    //End

                    //Import and map products to store
                    if ($storeUserId) {
                        $mappingData = $this->getRequest()->getParam('product_form');

                        //Map product from textarea data
                        if (isset($mappingData['product_skus']) && !empty($mappingData['product_skus'])) {
                            $productSkus = explode(',', $mappingData['product_skus']);
                            foreach ($productSkus as $key => $filecontent) {
                                $sku = trim($filecontent);
                                $product = $this->productRepository->get($sku);
                                $productId = $product->getId();
                                $data = [$storeId,$productId];
                                $productSkus[$key] = array_merge($data, [$sku]);
                            }
                            $result = $this->productResource->mapStoresProduct($productSkus);
                            if (!empty($result)) {
                                foreach ($result as $err) {
                                    $this->messageManager->addError($err);
                                }
                            }
                        } else {
                            //Import and map product by csv
                            $filesUploadCheck = $this->_zend->__call('getFileInfo', []);
                            if (isset($filesUploadCheck) && !empty($filesUploadCheck['product_form_import_file_']['tmp_name'])) {
                                $fileName = $filesUploadCheck['product_form_import_file_']['name'];
                                $splitDetails = explode('.', $fileName);
                                $extension = $splitDetails[1];
                                if ($extension == 'csv') {
                                    $importCsvRawData = $this->csvProcessor->getData($filesUploadCheck['product_form_import_file_']['tmp_name']);
                                    $filecontents = array_filter($importCsvRawData);
                                    $header = array_shift($filecontents);
                                    foreach ($filecontents as $key => $filecontent) {
                                        $filecontent[0] = trim($filecontent[0]);
                                        $sku = $filecontent[0];
                                        $product = $this->productRepository->get($sku);
                                        $productId = $product->getId();
                                        $data = [$storeId,$productId];
                                        $filecontents[$key] = array_merge($data, $filecontent);
                                    }

                                    $header_column = count($header);
                                    if ($header_column == 1) {
                                        $result = $this->productResource->mapStoresProduct($filecontents);
                                        if (!empty($result)) {
                                            foreach ($result as $err) {
                                                $this->messageManager->addError($err);
                                            }
                                        }
                                    } else {
                                        $this->messageManager->addError(__('File could not be imported mapping failed.'));                                   
                                    }
                                } else {
                                    $this->messageManager->addError(__('File should be csv mapping failed.'));
                                }
                            }
                        }
                    }
                }
                // Display success message
                $this->messageManager->addSuccess(__('The store has been saved.'));
                // Check if 'Save and Continue'
                $isStoreLogin = $this->storepickupHelper->isStoreLogin();
                if ($this->getRequest()->getParam('back') || $isStoreLogin) {
                    $this->_redirect('*/*/addrow', ['id' => $storesModel->getStoreId(), '_current' => true]);
                    return;
                }
                // Go to grid page
                $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/addrow', ['id' => $storesModel->getStoreId()]);
            }
        }
    }

    /**
     * Get Role model object
     * @return \Magento\Authorization\Model\RoleFactory
     */
    public function getRoleModel() {
        return $this->roleFactory;
    }

    /**
     * Access rights checking
     *
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Cybage_Storepickup::add_row');
    }
}
