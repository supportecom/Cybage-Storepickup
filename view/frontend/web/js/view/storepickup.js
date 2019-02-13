/**
 * Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India 
 * http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Cybage_Storepickup/js/model/shipping-rates-validator/storepickup',
    'Cybage_Storepickup/js/model/shipping-rates-validation-rules/storepickup'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    storepickupShippingRatesValidator,
    storepickupShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('storepickup', storepickupShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('storepickup', storepickupShippingRatesValidationRules);
    return Component;
});
