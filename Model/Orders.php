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

namespace Cybage\Storepickup\Model;

use Cybage\Storepickup\Api\Data\OrderGridInterface;

class Orders extends \Magento\Framework\Model\AbstractModel implements OrderGridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'cybage_stores_orders';

    /**
     * @var string
     */
    protected $_cacheTag = 'cybage_stores_orders';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'cybage_stores_orders';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Cybage\Storepickup\Model\ResourceModel\Orders');
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get IncrementId.
     *
     * @return varchar
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * Set IncrementId.
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * Get CreatedAt.
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get BillingName.
     *
     * @return varchar
     */
    public function getBillingName()
    {
        return $this->getData(self::BILLING_NAME);
    }

    /**
     * Set BillingName.
     */
    public function setBillingName($billingName)
    {
        return $this->setData(self::BILLING_NAME, $billingName);
    }
    
    /**
     * Get ShippingName.
     *
     * @return varchar
     */
    public function getShippingName()
    {
        return $this->getData(self::SHIPPING_NAME);
    }

    /**
     * Set ShippingName.
     */
    public function setShippingName($shippingName)
    {
        return $this->setData(self::SHIPPING_NAME, $shippingName);
    }
    
    /**
     * Get BaseGrandTotal.
     *
     * @return double
     */
    public function getBaseGrandTotal()
    {
        return $this->getData(self::BASE_GRAND_TOTAL);
    }

    /**
     * Set BaseGrandTotal.
     */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        return $this->setData(self::BASE_GRAND_TOTAL, $baseGrandTotal);
    }

    /**
     * Get GrandTotal.
     *
     * @return double
     */
    public function getGrandTotal()
    {
        return $this->getData(self::GRAND_TOTAL);
    }

    /**
     * Set GrandTotal.
     */
    public function setGrandTotal($grandTotal)
    {
        return $this->setData(self::GRAND_TOTAL, $grandTotal);
    }
    
    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
