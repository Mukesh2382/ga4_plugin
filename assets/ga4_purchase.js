jQuery(document).ready(function(){
    dataLayer.push({ ecommerce: null });
    dataLayer.push({
        event: "purchase",
        ecommerce: {
        currency: purchase_data['currency'],
        value: parseFloat(purchase_data['value']),
        tax : purchase_data['tax'],
        shipping : purchase_data['shipping'],
        discount : purchase_data['discount'],
        coupon : purchase_data['coupon'],
        items: purchase_data['items']
}
});
});