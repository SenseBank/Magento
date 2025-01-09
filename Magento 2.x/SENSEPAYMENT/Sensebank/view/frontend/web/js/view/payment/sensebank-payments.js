/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(

            {
                type: 'sensebank',
                component: 'SENSEPAYMENT_Sensebank/js/view/payment/method-renderer/sensebank-method'
            }

        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
