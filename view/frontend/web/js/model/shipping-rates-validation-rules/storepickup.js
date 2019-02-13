/**
 * Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India 
 * http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 */
define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'street': {
                        'required': true
                },
                'city': {
                        'required': true
                },
                'postcode': {
                        'required': true
                },
                'country_id': {
                    'required': true
                },
                'region_id': {
                    'required': true
                },
                'region_id_input': {
                    'required': true
                }
            };
        }
    };
});
