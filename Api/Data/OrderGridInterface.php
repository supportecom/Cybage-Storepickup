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
namespace Cybage\Storepickup\Api\Data;

interface OrderGridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter.
     */
    const ENTITY_ID = 'entity_id';
    const INCREMENT_ID = 'increment_id';
    const CREATED_AT = 'created_at';
    const BILLING_NAME = 'billing_name';
    const SHIPPING_NAME = 'shipping_name';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const GRAND_TOTAL = 'grand_total';
    const STATUS = 'status';
    

   /**
    * Get EntityId.
    *
    * @return int
    */
    public function getEntityId();

   /**
    * Set EntityId.
    */
    public function setEntityId($entityId);

   /**
    * Get IncrementId.
    *
    * @return varchar
    */
    public function getIncrementId();

   /**
    * Set IncrementId.
    */
    public function setIncrementId($incrementId);
    
    /**
    * Get CreatedAt.
    *
    * @return datetime
    */
    public function getCreatedAt();

   /**
    * Set CreatedAt.
    */
    public function setCreatedAt($createdAt);

   /**
    * Get BillingName.
    *
    * @return varchar
    */
    public function getBillingName();

   /**
    * Set BillingName.
    */
    public function setBillingName($billingName);
    
    /**
    * Get ShippingName.
    *
    * @return varchar
    */
    public function getShippingName();

   /**
    * Set ShippingName.
    */
    public function setShippingName($shippingName);
        
    /**
    * Get BaseGrandTotal.
    *
    * @return double
    */
    public function getBaseGrandTotal();

   /**
    * Set BaseGrandTotal.
    */
    public function setBaseGrandTotal($baseGrandTotal);
    
    /**
    * Get GrandTotal.
    *
    * @return double
    */
    public function getGrandTotal();

   /**
    * Set GrandTotal.
    */
    public function setGrandTotal($grandTotal);
    
   /**
    * Get Status.
    *
    * @return varchar
    */
    public function getStatus();

   /**
    * Set Status.
    */
    public function setStatus($status);
}
