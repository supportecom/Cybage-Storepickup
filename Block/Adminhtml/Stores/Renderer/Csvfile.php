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

namespace Cybage\Storepickup\Block\Adminhtml\Stores\Renderer;

use Magento\Framework\DataObject;

class Csvfile extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $_assetRepo;

    public function __construct( 
         \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
         $this->_assetRepo = $assetRepo;
    }

    public function getElementHtml()
    {
         $csvFile = $this->_assetRepo->getUrl('Cybage_Storepickup::csv/Sample-Import.csv');
         $csvLink = "<a href=".$csvFile." target='_blank'>Download Sample File</a>";
        return $csvLink;
    }

}