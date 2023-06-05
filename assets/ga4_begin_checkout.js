jQuery(document).ready(function(){
    dataLayer.push({ ecommerce: null });
    dataLayer.push({
        event: "begin_checkout",
        ecommerce: {
        currency: ga4_begin_checkout_data['currency'],
        value: parseFloat(ga4_begin_checkout_data['value']),
        coupon : ga4_begin_checkout_data['coupon_code'],
        items: ga4_begin_checkout_data['items']
}
});
});