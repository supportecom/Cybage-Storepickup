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

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class User
 */
class User extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{

    const CURRENT_USER_PASSWORD_FIELD = 'current_password';

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_LocaleLists;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        $this->_LocaleLists = $localeLists;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('user_data');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('users_');
        $form->setFieldNameSuffix('user_form');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Account Information')]);
        if ($model->getUserId()) {
            $baseFieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
            
            $baseFieldset->addField(
                'username',
                'text',
                [
                'name' => 'username',
                'label' => __('User Name'),
                'id' => 'username',
                'title' => __('User Name'),
                'required' => true,
                'readonly' => true
                    ]
            );
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
            $baseFieldset->addField(
                'username',
                'text',
                [
                'name' => 'username',
                'label' => __('User Name'),
                'id' => 'username',
                'title' => __('User Name'),
                'required' => true
                    ]
            );
        }
        
        $baseFieldset->addField(
            'firstname',
            'text',
            [
            'name' => 'firstname',
            'label' => __('First Name'),
            'id' => 'firstname',
            'title' => __('First Name'),
            'required' => true
                ]
        );
        $baseFieldset->addField(
            'lastname',
            'text',
            [
            'name' => 'lastname',
            'label' => __('Last Name'),
            'id' => 'lastname',
            'title' => __('Last Name'),
            'required' => true
                ]
        );
        $baseFieldset->addField(
            'email',
            'text',
            [
            'name' => 'email',
            'label' => __('Email'),
            'id' => 'customer_email',
            'title' => __('User Email'),
            'class' => 'required-entry validate-email',
            'required' => true
                ]
        );
        $isNewObject = $model->isObjectNew();
        if ($isNewObject) {
            $passwordLabel = __('Password');
        } else {
            $passwordLabel = __('New Password');
        }
        $confirmationLabel = __('Password Confirmation');
        $this->_addPasswordFields($baseFieldset, $passwordLabel, $confirmationLabel, $isNewObject);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authSession = $objectManager->create('Magento\Backend\Model\Auth\Session');
        if ($authSession->getUser()->getId() != $model->getUserId()) {
            $baseFieldset->addField(
                'is_active',
                'select',
                [
                'name' => 'is_active',
                'label' => __('This account is'),
                'id' => 'is_active',
                'title' => __('Account Status'),
                'class' => 'input-select',
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
                    ]
            );
        }
        $baseFieldset->addField('user_roles', 'hidden', ['name' => 'user_roles', 'id' => '_user_roles']);
        $data = $model->getData();
        unset($data['password']);
        unset($data[self::CURRENT_USER_PASSWORD_FIELD]);
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Add password input fields
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $passwordLabel
     * @param string $confirmationLabel
     * @param bool $isRequired
     * @return void
     */
    protected function _addPasswordFields(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        $passwordLabel,
        $confirmationLabel,
        $isRequired = false
    ) {
        $requiredFieldClass = $isRequired ? ' required-entry' : '';
        $fieldset->addField(
            'password',
            'password',
            [
            'name' => 'password',
            'label' => $passwordLabel,
            'id' => 'customer_pass',
            'title' => $passwordLabel,
            'class' => 'input-text validate-admin-password' . $requiredFieldClass,
            'required' => $isRequired
                ]
        );
        $fieldset->addField(
            'confirmation',
            'password',
            [
            'name' => 'password_confirmation',
            'label' => $confirmationLabel,
            'id' => 'confirmation',
            'title' => $confirmationLabel,
            'class' => 'input-text validate-cpassword' . $requiredFieldClass,
            'required' => $isRequired
                ]
        );
    }
    
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('My Profile');
    }
 
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('My Profile');
    }
 
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
