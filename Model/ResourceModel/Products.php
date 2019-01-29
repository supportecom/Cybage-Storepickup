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

namespace Cybage\Storepickup\Model\ResourceModel;

class Products extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('cybage_stores_product_map', 'id');
    }
    
    /**
     * Map stores product 
     * @param type $filecontents
     * @return string
     */
    public function mapStoresProduct($filecontents)
    {
        $errors = [];
        if (!empty($filecontents)) {
            $columns = [
                'store_id',
                'product_id',
                'sku'
            ];
            foreach ($filecontents as $key => $filecontent) {
                try {
                    $this->getConnection()->insertArray('cybage_stores_product_map', $columns, [$filecontent]);
                } catch (\Exception $e) {
                    $row = $key + 1;
                    $errors[] = 'Duplicate entry for row : ' . $row . '-------Sku : ' . $filecontent[2];
                    continue;
                }
            }
        }
        return $errors;
    }
    
    /**
     * Delete unmapped data from table
     * @param type $productIds
     * @param type $storeId
     * @return array
     */
    public function deleteUnmappedProduct($productIds,$storeId)
    {
        $errors = [];
        if (!empty($productIds)) {
            $this->getConnection()->delete(
                'cybage_stores_product_map',
                'product_id IN (' . implode(',', $productIds) . ') AND store_id = '.$storeId
            );
        }
        return $errors;
    }
}
