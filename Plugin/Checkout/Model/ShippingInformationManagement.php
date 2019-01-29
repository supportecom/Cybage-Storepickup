<?php

/**
 * Cybage Storepickup Plugin
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_ecom@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Cybage Storepickup Plugin
 * @package    Cybage_Storepickup
 * @copyright  Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India
 * http://www.cybage.com/coe/e-commerce
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Storepickup\Plugin\Checkout\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;

class ShippingInformationManagement {

    const METHODCODE = 'storepickup';

    /**
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     *
     * @var Session
     */
    protected $customerSession;

    /**
     *
     * @var Logger
     */
    protected $logger;

    /**
     * 
     * @param QuoteRepository $quoteRepository
     * @param Session $customerSession
     * @param Logger $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Session $customerSession,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param \Closure $proceed
     * @param type $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return type
     * @throws InputException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extensionAttr = $addressInformation->getExtensionAttributes();
        $quote = $this->quoteRepository->getActive($cartId);
        //Set storepickup id in quote
        $methodCode = $addressInformation->getShippingMethodCode();
        $pickupStoreId = $extensionAttr->getPickupstoreId();
        if ($methodCode == self::METHODCODE) {
            if ($pickupStoreId !=0) {
                $quote->setPickupstoreId($extensionAttr->getPickupstoreId());
            }
        }
    }
}
