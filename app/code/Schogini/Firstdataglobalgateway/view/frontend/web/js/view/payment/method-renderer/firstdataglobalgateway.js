/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form'
    ],
    function (Component) {
        'use strict';
        console.log('I am in the firstdataglobalgateway.js file');
        return Component.extend({
            defaults: {
                template: 'Schogini_Firstdataglobalgateway/payment/cc-form',
            },
             getCode: function() {
                return 'firstdataglobalgateway';
            },
            isActive: function() {
                return true;
            }
        });
    }
);

