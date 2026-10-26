(function () {
    'use strict';
    if (!window.wc || !window.wc.wcBlocksRegistry || !window.wc.wcSettings || !window.wp || !window.wp.element) { return; }
    var settings = window.wc.wcSettings.getSetting('mucmay_vnpay_data', {});
    var create = window.wp.element.createElement;
    var decode = (window.wp.htmlEntities && window.wp.htmlEntities.decodeEntities) || function (v) { return v; };
    var label = decode(settings.title || 'VNPAY QR (thử nghiệm)');
    var Description = function () {
        return create('p', null, decode(settings.description || 'Thanh toán thử nghiệm qua VNPAY Sandbox.'));
    };
    window.wc.wcBlocksRegistry.registerPaymentMethod({
        name: 'mucmay_vnpay',
        paymentMethodId: 'mucmay_vnpay',
        label: label,
        ariaLabel: label,
        content: create(Description),
        edit: create(Description),
        canMakePayment: function () { return true; },
        supports: { features: settings.supports || ['products'] }
    });
})();
