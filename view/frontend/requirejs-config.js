/**
 * Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India 
 * http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 */

var config = {
    "map": {
        "*": {
            'Magento_Checkout/js/model/shipping-save-processor/default': 
            'Cybage_Storepickup/js/model/shipping-save-processor/default'
            
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
            'Cybage_Storepickup/js/view/shipping': true
            }
        }
    }
};

