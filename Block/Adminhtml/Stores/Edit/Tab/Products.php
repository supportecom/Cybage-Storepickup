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

namespace Cybage\Storepickup\Block\Adminhtml\Stores\Edit\Tab;

use Cybage\Storepickup\Model\StoresFactory;
use Cybage\Storepickup\Model\ProductsFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Cybage\Storepickup\Model\Stores;
use Cybage\Storepickup\Helper\Data as StoreHelper;

/**
 * Class Products
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Cybage\Storepickup\Model\Stores
     */
    private $storesModel;

    /**
     * @var \Cybage\Storepickup\Model\StoresFactory
     */
    private $storesFactory;
    
    /**
     *
     * @var \Cybage\Storepickup\Model\ProductsFactory
     */
    private $storeProducts;
    
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var \Cybage\Storepickup\Helper\Data
     */
    protected $helper;
    
    /**
     * 
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param StoresFactory $storesFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Stores $storesModel
     * @param ProductsFactory $storeProducts
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        StoresFactory $storesFactory,
        CollectionFactory $productCollectionFactory,
        Stores $storesModel,
        ProductsFactory $storeProducts,
        StoreHelper $helper,
        array $data = []
    ) {
        $this->storesFactory = $storesFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry = $registry;
        $this->storesModel = $storesModel;
        $this->storeProducts = $storeProducts;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('productattach_id')) {
            $this->setDefaultFilter(['in_product' => 1]);
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_product') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    public function _prepareCollection()
    {
        $productIds = $this->getSelectedProducts();
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');
        $collection->addFieldToFilter('entity_id', array('in'=>$productIds));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    public function _prepareColumns()
    {

        $model = $this->storesModel;

        $this->addColumn(
            'in_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->getSelectedProducts(),
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'names',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
                'width' => '50px',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Retrieve selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $storeId = $this->getRequest()->getParam('id');
        //Assign login store id in case of store user
        if ($storeId == 0 || empty($storeId)) {
            $isStoreUser = $this->helper->isStoreLogin();
            if ($isStoreUser) {
                $storeId = $this->helper->getStoreId();
            }
        }
        //End
        $storeProductsCollection = $this->storeProducts->create()->getCollection();
        $storeProductsCollection->addFieldToSelect('product_id');
        $storeProductsCollection->addFieldToFilter('store_id', $storeId);
        $productIds = [];
        foreach ($storeProductsCollection as $storeProducts) {
            $productIds[] = $storeProducts->getProductId();
        }
        return $productIds;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
