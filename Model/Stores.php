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

namespace Cybage\Storepickup\Model;

use Cybage\Storepickup\Api\Data\GridInterface;

class Stores extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'cybage_stores_profile';

    /**
     * @var string
     */
    protected $_cacheTag = 'cybage_stores_profile';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'cybage_stores_profile';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Cybage\Storepickup\Model\ResourceModel\Stores');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set EntityId.
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getStoreName()
    {
        return $this->getData(self::STORE_NAME);
    }

    /**
     * Set Title.
     */
    public function setStoreName($storeName)
    {
        return $this->setData(self::STORE_NAME, $storeName);
    }

    /**
     * Get getContent.
     *
     * @return varchar
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Set Content.
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get PublishDate.
     *
     * @return varchar
     */
    public function getZipCode()
    {
        return $this->getData(self::ZIP_CODE);
    }

    /**
     * Set PublishDate.
     */
    public function setZipCode($zipCode)
    {
        return $this->setData(self::ZIP_CODE, $zipCode);
    }
    
    /**
     * Get PublishDate.
     *
     * @return varchar
     */
    public function getContactPerson()
    {
        return $this->getData(self::CONTACT_PERSON);
    }

    /**
     * Set PublishDate.
     */
    public function setContactPerson($contactPerson)
    {
        return $this->setData(self::CONTACT_PERSON, $contactPerson);
    }
    
    /**
     * Get PublishDate.
     *
     * @return varchar
     */
    public function getContactNo()
    {
        return $this->getData(self::CONTACT_NO);
    }

    /**
     * Set PublishDate.
     */
    public function setContactNo($contactNo)
    {
        return $this->setData(self::CONTACT_NO, $contactNo);
    }

    /**
     * Get IsActive.
     *
     * @return varchar
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set IsActive.
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }
}
