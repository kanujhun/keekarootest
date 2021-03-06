/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiComponent',
        'ko',
        'jquery',
        'Magento_Checkout/js/model/sidebar'
    ],
    function(Component, ko, $, sidebarModel) {
        'use strict';
        $('#sidebar-buttons .primary button span span').text('asdf');
        return Component.extend({
            setModalElement: function(element) {
                sidebarModel.setPopup($(element));
            }
        });
    }
);
