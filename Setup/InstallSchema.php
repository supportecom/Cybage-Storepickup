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
 * @category   Class
 * @package    Cybage_Storepickup
 * @copyright  Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India
 * http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */


namespace Cybage\Storepickup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    /**
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        // store profile tale creation
        $tableStoresProfile = $installer->getTable('cybage_stores_profile');
        if ($installer->getConnection()->isTableExists($tableStoresProfile) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableStoresProfile)
                    ->addColumn(
                        'store_id', Table::TYPE_INTEGER, 10, [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ], 'Store ID'
                    )
                    ->addColumn(
                        'store_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Store Name'
                    )
                    ->addColumn(
                        'country_id', Table::TYPE_TEXT, 2, ['nullable' => false], 'Country'
                    )
                    ->addColumn(
                        'region', Table::TYPE_TEXT, 100, ['nullable' => false], 'State'
                    )
                    ->addColumn(
                        'region_id', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => true], 'Country'
                    )
                    ->addColumn(
                        'street_address', Table::TYPE_TEXT, null, ['nullable' => false], 'Street Address'
                    )
                    ->addColumn(
                        'city', Table::TYPE_TEXT, 150, ['nullable' => false], 'City'
                    )
                    ->addColumn(
                        'zip_code', Table::TYPE_TEXT, 100, ['nullable' => false], 'Zip Code'
                    )
                    ->addColumn(
                        'locality', Table::TYPE_TEXT, 150, ['nullable' => true], 'Locality'
                    )
                    ->addColumn(
                        'latitude', Table::TYPE_TEXT, 20, ['nullable' => false], 'Latitude'
                    )
                    ->addColumn(
                        'longitude', Table::TYPE_TEXT, 20, ['nullable' => false], 'Longitude'
                    )
                    ->addColumn(
                        'contact_person', Table::TYPE_TEXT, 255, ['nullable' => true], 'Contact Person'
                    )
                    ->addColumn(
                        'contact_no', Table::TYPE_TEXT, 255, ['nullable' => false], 'Contact No'
                    )
                    ->addColumn(
                        'store_start_time', Table::TYPE_TEXT, 100, ['nullable' => false], 'Store Start Time'
                    )
                    ->addColumn(
                        'store_close_time', Table::TYPE_TEXT, 100, ['nullable' => false], 'Store Close Time'
                    )
                    ->addColumn(
                        'pickup_interval', Table::TYPE_INTEGER, null, ['nullable' => false], 'Pickup Interval'
                    )
                    ->addColumn(
                        'status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 1], 'Status'
                    )
                    ->addColumn(
                        'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Created At'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresProfile, 'country_id', 'directory_country', 'country_id'
                        ), 'country_id', $installer->getTable('directory_country'), 'country_id', \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresProfile, 'region_id', 'directory_country_region', 'region_id'
                        ), 'region_id', $installer->getTable('directory_country_region'), 'region_id', \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
                    )
                    ->setComment('Stores Profile Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
            //Store profile table End
        }
        
        //Store Users Table Creation
        $tableStoresUsers = $installer->getTable('cybage_stores_users');
        if ($installer->getConnection()->isTableExists($tableStoresUsers) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableStoresUsers)
                    ->addColumn(
                        'id', Table::TYPE_INTEGER, 10, [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ], 'ID'
                    )
                    ->addColumn(
                        'store_id', Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => true], 'Store Id'
                    )
                    ->addColumn(
                        'user_id', Table::TYPE_INTEGER, 20, ['unsigned' => true, 'nullable' => false], 'User Id'
                    )
                    ->addColumn(
                        'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Created At'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresUsers, 'store_id', $tableStoresProfile, 'store_id'
                        ), 'store_id', $installer->getTable($tableStoresProfile), 'store_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresUsers, 'user_id', 'admin_user', 'user_id'
                        ), 'user_id', $installer->getTable('admin_user'), 'user_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableStoresUsers,
                            ['store_id', 'user_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['store_id', 'user_id'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Stores Users Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        
        //Store Product Mapping Table
        $tableStoresProductMap = $installer->getTable('cybage_stores_product_map');
        if ($installer->getConnection()->isTableExists($tableStoresProductMap) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableStoresProductMap)
                    ->addColumn(
                        'id', Table::TYPE_INTEGER, 10, [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ], 'ID'
                    )
                    ->addColumn(
                        'store_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => true], 'Store Id'
                    )
                    ->addColumn(
                        'product_id', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'Product Id'
                    )
                    ->addColumn(
                        'sku', Table::TYPE_TEXT, 255, ['unsigned' => true, 'nullable' => false], 'Sku'
                    )
                    ->addColumn(
                        'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Created At'
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresProductMap, 'product_id', 'catalog_product_entity', 'entity_id'
                        ), 'product_id', $installer->getTable('catalog_product_entity'), 'entity_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresProductMap, 'sku', 'catalog_product_entity', 'sku'
                        ), 'sku', $installer->getTable('catalog_product_entity'), 'sku', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            $tableStoresProductMap, 'store_id', $tableStoresProfile, 'store_id'
                        ), 'store_id', $installer->getTable($tableStoresProfile), 'store_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            $tableStoresProductMap,
                            ['store_id', 'product_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['store_id', 'product_id'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Stores Product Map Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        
		/** Add pickupstore_id column in quote and store table*/
        if ($installer->getConnection()->isTableExists($installer->getTable('quote')) == true) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote'),
                'pickupstore_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Pickup Store Id',
                ]
            );
        }
        
        if ($installer->getConnection()->isTableExists($installer->getTable('sales_order')) == true) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'pickupstore_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Pickup Store Id',
                ]
            );
        }    
        
        if ($installer->getConnection()->isTableExists($installer->getTable('sales_order_grid')) == true) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_grid'),
                'pickupstore_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Pickup Store Id',
                ]
            );        
        }
        $installer->endSetup();
    }
}

