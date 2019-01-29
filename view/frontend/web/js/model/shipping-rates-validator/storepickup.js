/**
 * Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India 
 * http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 */

define([
    'jquery',
    'mageUtils',
    '../shipping-rates-validation-rules/storepickup',
    'mage/translate'
], function ($, utils, validationRules, $t) {
    'use strict';
    return {
        validationErrors: [],
        validate: function (address) {
            var self = this;
            this.validationErrors = [];
            $.each(validationRules.getRules(), function (field, rule) {
                if (rule.required && utils.isEmpty(address[field])) {
                    var message = $t('Field ') + field + $t(' is required.');
                    var regionFields = ['region', 'region_id', 'region_id_input'];
                    if (
                            $.inArray(field, regionFields) === -1
                            || utils.isEmpty(address['region']) && utils.isEmpty(address['region_id'])
                            ) {
                        self.validationErrors.push(message);
                    }
                }
            });
            return !Boolean(this.validationErrors.length);
        }
    };
}
);
