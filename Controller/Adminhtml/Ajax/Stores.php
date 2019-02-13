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
namespace Cybage\Storepickup\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Action\Action;
use Magento\Backend\Model\Session\Quote;
use Cybage\Storepickup\Block\Adminhtml\Order\Create\Storepickup;
use Cybage\Storepickup\Model\ProductsFactory;
use Cybage\Storepickup\Model\StoresFactory;

class Stores extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var Http
     */
    protected $request;
    
    /**
     * @var Quote
     */
    protected $backendQuoteSession;
    
    /**
     * @var Storepickup
     */
    protected $storepickup;
    
    /**
     * @var StoresFactory
     */
    protected $pickupStores;
    
    /**
     * @var StoresFactory
     */
    protected $storeProducts;

    /**
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Http $request
     * @param Quote $backendQuoteSession
     * @param Storepickup $storepickup
     * @param ProductsFactory $storeProducts
     * @param StoresFactory $pickupStores
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Http $request,
        Quote $backendQuoteSession,
        Storepickup $storepickup,
        ProductsFactory $storeProducts,
        StoresFactory $pickupStores
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->storepickup = $storepickup;
        $this->storeProducts = $storeProducts;
        $this->pickupStores = $pickupStores;
        parent::__construct($context);
    }
    
    /**
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $items = $this->backendQuoteSession->getQuote()->getAllVisibleItems();
        $itemsCount = $this->backendQuoteSession->getQuote()->getItemsCount();
        $lat = $this->request->getParam('lat');
        $lng = $this->request->getParam('lng');
        $radius = $this->storepickup->getRadiusSettings();
        $json = [];
        if (!empty($lat) && !empty($lng)) {
            $quoteItems = [];
            foreach ($items as $item) {
                $quoteItems[] = $item->getProductId();
            }
            if (!empty($quoteItems) && $itemsCount > 0) {

                /** Get stores mapped with all quote items */
                $availableStoresSql = $this->storeProducts->create()->getCollection()
                        ->addFieldToSelect('store_id')
                        ->addFieldToFilter('product_id', ['in' => $quoteItems])
                        ->getSelect()
                        ->group('store_id')
                        ->having('COUNT(*) =? ', $itemsCount);

                /** Get the nearest stores based on lat, long and radius */
                $storesDataSql = $this->pickupStores->create()->getCollection()
                        ->addFieldToSelect(['store_name', 'country_id', 'region', 'region_id', 'street_address', 'city', 'zip_code', 'locality', 'latitude', 'longitude'])
                        ->addFieldToSelect(new \Zend_Db_Expr(
                            "( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude )" .
                            " - radians($lng) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) )"), 'distance')
                        ->addFieldToFilter('store_id', ['in' => $availableStoresSql])
                        ->addFieldToFilter('status', 1)
                        ->setOrder('distance');
                $storesDataSql->getSelect()
                        ->having('distance <= ? ', $radius);
                /** Prepare json data for ajax call response */
                $data = $storesDataSql->load()->getData();
                foreach ($data as $stores) {
                    $json[] = $stores;
                }
            }
        }
        return $this->resultJsonFactory->create()->setData($json);
    }
}
