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

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter.
     */
    const STORE_ID = 'store_id';
    const STORE_NAME = 'store_name';
    const CITY = 'city';
    const ZIP_CODE = 'zip_code';
    const CONTACT_PERSON = 'contact_person';
    const CONTACT_NO = 'contact_no';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';

   /**
    * Get StoreId.
    *
    * @return int
    */
    public function getStoreId();

   /**
    * Set StoreId.
    */
    public function setStoreId($storeId);

   /**
    * Get StoreName.
    *
    * @return varchar
    */
    public function getStoreName();

   /**
    * Set StoreName.
    */
    public function setStoreName($storeName);

   /**
    * Get City.
    *
    * @return varchar
    */
    public function getCity();

   /**
    * Set City.
    */
    public function setCity($city);
    
    /**
    * Get ZipCode.
    *
    * @return varchar
    */
    public function getZipCode();

   /**
    * Set ZipCode.
    */
    public function setZipCode($zipCode);
        
    /**
    * Get ZipCode.
    *
    * @return varchar
    */
    public function getContactPerson();

   /**
    * Set ZipCode.
    */
    public function setContactPerson($contactPerson);
    
    /**
    * Get ZipCode.
    *
    * @return varchar
    */
    public function getContactNo();

   /**
    * Set ZipCode.
    */
    public function setContactNo($contactNo);
    
   /**
    * Get IsActive.
    *
    * @return varchar
    */
    public function getIsActive();

   /**
    * Set IsActive.
    */
    public function setIsActive($isActive);
}
