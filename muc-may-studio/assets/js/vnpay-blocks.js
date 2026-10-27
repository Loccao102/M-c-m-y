(function () {
    'use strict';
    if (!window.wc || !window.wc.wcBlocksRegistry || !window.wc.wcSettings ||
        !window.wp || !window.wp.element) { return; }
    var settings = window.wc.wcSettings.getSetting('mucmay_theme_vnpay_data', {});
    var el = window.wp.element.createElement;
    var decode = (window.wp.htmlEntities && window.wp.htmlEntities.decodeEntities) || function (v) { return v; };
    var label = decode(settings.title || 'Thanh toán online qua VNPAY');
    var details = function () { return el('p', null, decode(settings.description || 'Thanh toán qua VNPAY Sandbox.')); };
    window.wc.wcBlocksRegistry.registerPaymentMethod({
        name: 'mucmay_theme_vnpay',
        paymentMethodId: 'mucmay_theme_vnpay',
        label: label,
        ariaLabel: label,
        content: el(details),
        edit: el(details),
        canMakePayment: function () { return true; },
        supports: { features: settings.supports || ['products'] }
    });
})();
