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

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Authorization\Model\Acl\Role\User;
use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Setup\Module\Setup;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * class InstallData
 * @package Cybage\Marketplace\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * Setup
     *
     * @var Setup
     */
    private $setup;
    
    /*
     *
     * Data keys
     */

    const KEY_USER = 'cybage_stores';
    
    /**
     *
     * @param \Cybage\Marketplace\Setup\SellerSetupFactory $sellerSetupFactory
     * @param Setup $setup
     * @param \Magento\Authorization\Model\RulesFactory $rulesFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        Setup $setup,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->_rulesFactory = $rulesFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();
        
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'pickupstore_id',
            [
            'group' => 'General',
            'type' => 'varchar',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'frontend' => '',
            'label' => 'Pickup Store Name',
            'input' => 'multiselect',
            'class' => '',
            'source' => 'Cybage\Storepickup\Model\Config\Source\Options',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_used_in_grid' => true,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'is_filterable_in_grid' => true,
            'apply_to' => ''
                ]
        );
        
        
        /**
         * Admin 'store' role creation
         */
        $adminRoleData = [
            'tree_level' => 1,
            'role_type' => Group::ROLE_TYPE,
            'user_type' => UserContextInterface::USER_TYPE_ADMIN,
            'role_name' => self::KEY_USER,
        ];
        $this->setup->getConnection()->insert($this->setup->getTable('authorization_role'), $adminRoleData);

        $roleId = $this->setup->getConnection()->lastInsertId();

        if ($roleId) {
            $resource = "Magento_Backend::myaccount,"
            . "Cybage_Storepickup::add_row,"
            . "Cybage_Storepickup::orders";
            $resources = explode(',', $resource);

            $this->_rulesFactory->create()->setRoleId($roleId)->setResources($resources)->saveRel();
        }
        $setup->endSetup();
    }
}
