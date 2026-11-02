(function () {
    'use strict';
    if (!window.wc || !window.wc.wcBlocksRegistry || !window.wc.wcSettings ||
        !window.wp || !window.wp.element) { return; }
    var config = window.wc.wcSettings.getSetting('mmvnpay_data', {});
    var create = window.wp.element.createElement;
    var decode = (window.wp.htmlEntities && window.wp.htmlEntities.decodeEntities) || function (text) { return text; };
    var label = decode(config.title || 'Thanh toán online qua VNPAY');
    var content = function () {
        return create('p', null, decode(config.description || 'Thanh toán qua VNPAY Sandbox.'));
    };
    window.wc.wcBlocksRegistry.registerPaymentMethod({
        name: 'mmvnpay',
        paymentMethodId: 'mmvnpay',
        label: label,
        ariaLabel: label,
        content: create(content),
        edit: create(content),
        canMakePayment: function () { return true; },
        supports: { features: config.supports || ['products'] }
    });
})();
