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

namespace Cybage\Storepickup\Block\Adminhtml\Stores\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Cybage\Storepickup\Model\Status;
use Magento\Directory\Model\Config\Source\Country;

class Main extends Generic implements TabInterface {

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Cybage\Storepickup\Model\Status
     */
    protected $_storeStatus;
    
    /**
     *
     * @var \Magento\Directory\Model\Config\Source\Country 
     */
    protected $_country;

    /**
     * 
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Status $storeStatu
     * @param Country $country
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $storeStatu,
        Country $country,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_storeStatus = $storeStatu;
        $this->_country = $country;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm() {
        /** @var $model \Cybage\Storepickup\Model\Stores */
        $model = $this->_coreRegistry->registry('row_data');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('stores_');
        $form->setFieldNameSuffix('store_form');

        $fieldset = $form->addFieldset(
            'base_fieldset', ['legend' => __('Basic Details')]
        );

        if ($model->getStoreId()) {
            $fieldset->addField(
                'store_id', 'hidden', ['name' => 'store_id']
            );
        }
        $fieldset->addField(
            'store_name', 'text', [
            'name' => 'store_name',
            'label' => __('Store name'),
            'required' => true
             ]
        );
        $optionsc = $this->_country->toOptionArray();
        $country = $fieldset->addField(
            'country_id', 'select', [
            'name' => 'country_id',
            'label' => __('Country'),
            'title' => __('Country'),
            'values' => $optionsc,
            'required' => true
                ]
        );

        $state = $fieldset->addField(
            'region_dropdown', 'select', [
            'name' => 'region_dropdown',
            'label' => __('Region'),
            'title' => __('Region'),
            'values' => ['--Please Select Country--'],
            'required' => false
                ]
        );
        
        $fieldset->addField(
            'region_text_box', 'text', [
            'name' => 'city',
            'label' => __('Region'),
            'required' => false
                ]
        );
        
        $fieldset->addField(
            'region_id', 'hidden', ['name' => 'region_id']
        );

        $fieldset->addField(
            'region', 'hidden', ['name' => 'region']
        );

        $fieldset->addField(
            'street_address', 'textarea', [
            'name' => 'street_address',
            'label' => __('Address'),
            'required' => true,
            'style' => 'height: 0em; width: 25em;'
                ]
        );

        $fieldset->addField(
            'city', 'text', [
            'name' => 'city',
            'label' => __('City'),
            'required' => true
                ]
        );

        $fieldset->addField(
            'zip_code', 'text', [
            'name' => 'zip_code',
            'label' => __('Zip Code'),
            'required' => true
                ]
        );

        $fieldset->addField(
            'locality', 'text', [
            'name' => 'locality',
            'label' => __('Locality'),
            'required' => true
                ]
        );

        $fieldset->addField(
            'contact_person', 'text', [
            'name' => 'contact_person',
            'label' => __('Contact Person'),
            'required' => false
                ]
        );

        $fieldset->addField(
            'contact_no', 'text', [
            'name' => 'contact_no',
            'label' => __('Contact No'),
            'required' => true,
            'class' => 'validate-number'
                ]
        );

        $fieldset->addField(
            'store_start_time', 'time', [
            'name' => 'store_start_time',
            'label' => __('Store Start Time'),
            'required' => true
                ]
        );

        $fieldset->addField(
            'store_close_time',
            'time', [
                'name' => 'store_close_time',
                'label' => __('Store Close Time'),
                'required' => true
            ]
        );
        
        $fieldset->addField(
            'status', 'select', [
            'label' => __('Status'),
            'name' => 'status',
            'title' => __('Status'),
            'required' => true,
            'options' => ['1' => __('Enable'), '0' => __('Disable')]
                ]
        );

        $fieldset->addField(
            'pickup_interval', 'select', [
            'label' => __('Pickup Time Interval'),
            'name' => 'pickup_interval',
            'title' => __('Pickup Time Interval'),
            'required' => true,
            'values' => $this->getPickupOptionArray()
                ]
        );

        /*
         * Add Ajax to the Country select box html output
         */
        $country->setAfterElementHtml("
            <script type=\"text/javascript\">
                    require([
                    'jquery',
                    'mage/template',
                    'jquery/ui',
                    'mage/translate'
                ],
                function($, mageTemplate) {
                   $('#edit_form').on('change', '#stores_country_id', function(event){
                        $.ajax({
                               url : '" . $this->getUrl('storepickup/*/regionlist') . "region/'+  $('#stores_region_id').val()+'/country/' +  $('#stores_country_id').val(),
                                type: 'get',
                                dataType: 'json',
                               showLoader:true,
                               success: function(data){
                                    if (data.htmlconent == 0) {
                                        $('.field-region_dropdown').hide();
                                        var regionVal = $('#stores_region').val();
                                        $('#stores_region_text_box').val(regionVal);
                                        $('.field-region_text_box').addClass('required-entry _required');
                                        $('#stores_region_text_box').addClass('required-entry _required');
                                        $('.field-region_dropdown').removeClass('required-entry _required');
                                        $('#stores_region_dropdown').removeClass('required-entry _required');
                                        $('.field-region_text_box').show();
                                    } else {
                                        $('#stores_region_dropdown').empty();
                                        $('#stores_region_dropdown').append(data.htmlconent);
                                        $('.field-region_dropdown').show();
                                        $('.field-region_text_box').hide();
                                        $('.field-region_text_box').removeClass('required-entry _required');
                                        $('#stores_region_text_box').removeClass('required-entry _required');
                                        $('.field-region_dropdown').addClass('required-entry _required');
                                        $('#stores_region_dropdown').addClass('required-entry _required');
                                    }
                               }
                            });
                   })
                   
                   $('#edit_form').on('change', '#stores_region_dropdown', function(event){
                        var region = $('#stores_region_dropdown option:selected').text();
                        var region_id = $('#stores_region_dropdown').val();
                        $('#stores_region_id').val(region_id);
                        $('#stores_region').val(region);
                    });
                    
                    $('#edit_form').on('change', '#stores_region_text_box', function(event){
                        var region = $('#stores_region_text_box').val();
                        $('#stores_region').val(region);
                    });
                    
                    $(document).ready(function(){
                        $('.field-region_text_box').hide();
                        var interval = 0;
                        setInterval(function(){
                            var stores_store_id = $('#stores_store_id').val();
                            if(interval == 0 && stores_store_id > 0) { 
                                var countryVal = $('#stores_country_id').val();
                                if (countryVal != '') {
                                    interval++;
                                    $('#stores_country_id').trigger('change');
                                }
                            }
                        }, 1000);
                    });
                }
            );
            </script>"
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return __('Store Profile');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return __('Store Profile');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden() {
        return false;
    }
    
    /**
     * Prepare select options for pickup dropdown
     * @return array
     */
    public function getPickupOptionArray() {
        return [
            [
                'label' => __('------- Please choose option -------'),
                'value' => '',
            ],
            ['value' => '1', 'label' => __('1 Hour')],
            ['value' => '2', 'label' => __('2 Hour')],
            ['value' => '3', 'label' => __('3 Hour')],
            ['value' => '4', 'label' => __('4 Hour')],
            ['value' => '5', 'label' => __('5 Hour')],
            ['value' => '6', 'label' => __('6 Hour')],
            ['value' => '7', 'label' => __('7 Hour')],
            ['value' => '8', 'label' => __('8 Hour')],
            ['value' => '9', 'label' => __('9 Hour')],
            ['value' => '10', 'label' => __('10 Hour')],
            ['value' => '11', 'label' => __('11 Hour')],
            ['value' => '12', 'label' => __('12 Hour')],
        ];
    }
}
