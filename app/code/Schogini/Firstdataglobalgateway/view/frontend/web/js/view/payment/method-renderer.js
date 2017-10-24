/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'firstdataglobalgateway',
                component: 'Schogini_Firstdataglobalgateway/js/view/payment/method-renderer/firstdataglobalgateway'
            }
        );
        /** Add view logic here if needed */
        // console.log('I am in the method-renderer');
        return Component.extend({});
    }
);