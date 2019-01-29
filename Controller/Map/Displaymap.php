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

namespace Cybage\Storepickup\Controller\Map;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Session;
use Cybage\Storepickup\Model\ProductsFactory;
use Cybage\Storepickup\Model\StoresFactory;
use Magento\Framework\App\ResourceConnection;
use Cybage\Storepickup\Helper\Data as HelperData;
use Psr\Log\LoggerInterface as Logger;

class Displaymap extends \Magento\Framework\App\Action\Action
{
    /**
     * Constant to store enable status
     */
    const STORE_SATUS = 1;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Http 
     */
    protected $request;

    /**
     * @var Session 
     */
    protected $session;

    /**
     * @var Products 
     */
    protected $productsFactory;

    /**
     * @var StoresFactory
     */
    protected $storesFactory;

    /**
     * @var ResourceConnection 
     */
    protected $connection;

    /**
     *
     * @var HelperData
     */
    protected $helperData;

    /**
     * 
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonFactory $resultJsonFactory
     * @param Http $request
     * @param Session $session
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        Http $request,
        Session $session,
        ProductsFactory $productsFactory,
        StoresFactory $storesFactory,
        Logger $logger,
        HelperData $helperData
    ) {
        $this->request         = $request;
        $this->session         = $session;
        $this->storesFactory   = $storesFactory;
        $this->productsFactory = $productsFactory;
        $this->helperData      = $helperData;
        $this->logger          = $logger;
        parent::__construct($context);
    }

    /**
     * Get stores as per address entered
     */
    public function execute()
    {
        $json       = [];
        $lat        = $this->request->getParam('lat');
        $lng        = $this->request->getParam('lng');
        $items      = $this->session->getQuote()->getAllVisibleItems();
        $itemsCount = $this->session->getQuote()->getItemsCount();
        $radius     = $this->helperData->getRadiusSettings();

        $quoteItems = [];
        foreach ($items as $item) {
            $quoteItems[] = $item->getProductId();
        }

        if (!empty($quoteItems)) {
            /** Get stores mapped with all quote items */
            $availableStoresSql = $this->productsFactory->create()->getCollection()
                ->addFieldToSelect('store_id')
                ->addFieldToFilter('product_id', ['in' => $quoteItems])
                ->getSelect()
                ->group('store_id')
                ->having('COUNT(*) =? ', $itemsCount);

            /** Get the nearest stores based on lat, long and radius */
            $storesDataSql = $this->storesFactory->create()->getCollection()
                ->addFieldToSelect(['store_name', 'country_id', 'region', 'region_id',
                    'street_address', 'city', 'zip_code', 'locality', 'latitude',
                    'longitude'])
                ->addFieldToSelect(new \Zend_Db_Expr(
                    "( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude )".
                    " - radians($lng) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) )"),
                    'distance')
                ->addFilterToMap('main_store_id', 'main_table.store_id')
                ->addFieldToFilter('main_store_id',
                    ['in' => $availableStoresSql])
                ->addFilterToMap('main_store_status', 'main_table.status')
                ->addFieldToFilter('main_store_status',
                    ['eq' => self::STORE_SATUS])
                ->setOrder('distance');

            $storesDataSql->getSelect()
                ->having('distance <= ? ', $radius);
            /** Prepare json data for ajax call response */
            $data       = $storesDataSql->load()->getData();
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

            if (!empty($data)) {
                foreach ($data as $stores) {
                    $json[] = $stores;
                }
                $resultJson->setData($json);
            } else {
                $resultJson->setData($json);
            }
            return $resultJson;
        }
    }
}
