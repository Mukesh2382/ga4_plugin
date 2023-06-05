<?php

/*
Plugin Name: GA4 Data Analytics Plugin
Plugin URI: https://www.mukesh.com
Description: To write all ga4 code
Version: 1.0.0
Author: Mukesh
Author URI: http://www.mukeshc.com
*/

add_action('wp_head', 'add_ga_code_to_head');
function add_ga_code_to_head()
{
    wp_enqueue_script('add_ga_code', plugin_dir_url(__FILE__)  . 'assets/ga4_code_head.js', array(), '1.0', true);
}

add_action('wp_body_open', 'add_ga4_code_to_body');
function add_ga4_code_to_body()
{
    echo '<!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KG3ND4L"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->';
}

add_action('wpforms_frontend_confirmation_message_after', 'track_form_submission', 10, 3);
function track_form_submission($confirmation, $form_data, $fields)
{
    $data = array();
    $data['name'] = $fields[0]['value'];
    $data['email'] = $fields[1]['value'];
    $data['comment'] = $fields[2]['value'];

    wp_enqueue_script('ga4_form_submissions', plugin_dir_url(__FILE__) . 'assets/form_tracking.js', array(), '1.0', false);
    wp_localize_script('ga4_form_submissions', 'form_data', $data);
}

add_action('woocommerce_add_to_cart', 'add_to_cart_event', 1, 3);
function add_to_cart_event($cart_id, $product_id, $request_quantity)
{
    $data = array();

    $product_data = wc_get_product($product_id);
    $data['currency'] = get_woocommerce_currency();
    $data['value'] = floatval($product_data->get_price());
    $data['item']['item_id'] = strval($product_id);
    $data['item']['item_name'] = $product_data->get_name();
    $category_ids = $product_data->get_category_ids();
    $data['item']['item_brand'] = "Organic Store";
    if (!empty($category_ids)) {
        $category_id = $category_ids[0];
        $category = get_term($category_id, 'product_cat');
        $data['item']['item_category'] = $category->name;
    } else {
        $data['item']['item_category'] = "Uncategorized";
    }
    $data['item']['price'] = floatval($product_data->get_price());
    $data['item']['quantity'] = $request_quantity;
   
    wp_enqueue_script('add_to_cart', plugin_dir_url(__FILE__) . 'assets/ga4_add_to_cart.js', array('jquery'), '1.0', false);
    wp_localize_script('add_to_cart', 'item_data', $data);
}

add_action('wp', 'view_cart_data');
function view_cart_data()
{
    if (is_page('cart')) {
        global $woocommerce;
        $data  = array();
        $cart = $woocommerce->cart;
        $data['currency'] = get_woocommerce_currency();
        $applied_coupons = $cart->get_applied_coupons();
        foreach ($applied_coupons as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);

            // Get coupon details
            $coupon_id = $coupon->get_id();
        }
        if (!$coupon_id) {
            $data['coupon_code'] = "Not Applied";
        } else {
            $coupon = get_post($coupon_id);
            if ($coupon && $coupon->post_type === 'shop_coupon') {
                $data['coupon_code'] =  $coupon->post_title;
            }
        }

        $index = 0;
        $total = 0;
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product['item_id'] = strval($cart_item['product_id']);
            //getting product data
            $product_data = wc_get_product($product['item_id']);
            $product['item_name'] = $product_data->get_name();
            $product['index'] = $index;
            //getting discount details for product
            if ($product_data->get_sale_price() !== '') {
                $discount_amount = $product_data->get_regular_price() - $product_data->get_sale_price();
                $product['discount'] = $discount_amount;
            } else {
                $product['discount'] = 0;
            }
            $product['item_brand'] = "Organic Store";
            //getting product category
            $category_ids = $product_data->get_category_ids();
            if (!empty($category_ids)) {
                $category_id = $category_ids[0];
                $category = get_term($category_id, 'product_cat');
                $product['item_category'] = $category->name;
            } else {
                $product['item_category'] = "Uncategorized";
            }
            $product['price'] = floatval($product_data->get_price());
            $total = $total + $product['price'];
            $product['quantity'] = $cart_item['quantity'];
            $data['items'][] = $product;
            $index++;
        }
        $data['value'] = $total;
        //enqueue script file for view_cart
        wp_enqueue_script('ga4_view_cart_event', plugin_dir_url(__FILE__) . 'assets/ga4_view_cart.js', array('jquery'), '1.0', false);
        wp_localize_script('ga4_view_cart_event', 'ga4_view_cart_data', $data);
    }
}

add_action('wp', 'ga4_begin_checkout_data');
function ga4_begin_checkout_data()
{
    if (is_page('checkout')) {
        global $woocommerce;
        $data  = array();
        $cart = $woocommerce->cart->get_cart();
        $data['currency'] = get_woocommerce_currency();
        $applied_coupons = $woocommerce->cart->get_applied_coupons();
        if (!empty($applied_coupons)) {
            $coupon_code = $applied_coupons[0];
            if ($coupon_code) {
                $data['coupon_code'] = $coupon_code;
            }
        } else {
            $data['coupon_code'] = "Not Applied";
        }

        $index = 0;
        $total = 0;
        foreach ($cart as $cart_item_key => $cart_item) {
            $product['item_id'] = strval($cart_item['product_id']);
            //getting product data
            $product_data = wc_get_product($product['item_id']);
            $product['item_name'] = get_the_title($product['item_id']);
            $product['index'] = $index;
            //getting discount details for product
            if ($product_data->get_sale_price() !== '') {
                $discount_amount = $product_data->get_regular_price() - $product_data->get_sale_price();
                $product['discount'] = $discount_amount;
            } else {
                $product['discount'] = 0;
            }
            $product['item_brand'] = "Organic Store";
            //getting product category
            $category_ids = $product_data->get_category_ids();
            if (!empty($category_ids)) {
                $category_id = $category_ids[0];
                $category = get_term($category_id, 'product_cat');
                $product['item_category'] = $category->name;
            } else {
                $product['item_category'] = "Uncategorized";
            }
            $product['price'] = floatval($product_data->get_price());
            $total = $total + $product['price'];
            $product['quantity'] = $cart_item['quantity'];
            $data['items'][] = $product;
            $index++;
        }
        $data['value'] = $total;

        //enqueue script file for begin_checkout
        wp_enqueue_script('ga4_begin_checkout_event', plugin_dir_url(__FILE__) . 'assets/ga4_begin_checkout.js', array('jquery'), '1.0', false);
        wp_localize_script('ga4_begin_checkout_event', 'ga4_begin_checkout_data', $data);
    }
}

add_action('woocommerce_thankyou', 'ga4_purchase_event', 10, 1);
function ga4_purchase_event($order_id)
{
    $data = array();
    $order = wc_get_order($order_id);
    $data['c_code'] = $order->get_currency();
    $data['value']  = $order->get_total();
    $data['tax']    = floatval($order->get_total_tax());
    $data['shipping'] = floatval($order->get_shipping_total());

    if (!empty($order->get_coupon_codes())) {
        $data['coupon'] = $order->get_coupon_codes()[0];
        $coupon = new WC_Coupon($order->get_coupon_codes()[0]);
        $coupon_amount = $coupon->get_amount();
        $data['discount'] = floatval($coupon_amount);
    } else {
        $data['coupon'] = 'Not Applied';
        $data['discount'] = 0.00;
    }
    $items_data = array();
    $i = 0;
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $terms = get_the_terms($product_id, 'product_cat');
        $cat_name = '';
        foreach ($terms as $term) {
            $cat_name = $cat_name . ' ' . $term->name;
        }
        $items_data['item_id']        = strval($item_id);
        $items_data['item_name']      = $item->get_name();
        $items_data['index']          = $i;
        $items_data['item_brand']     = "Organic Store";
        $items_data['item_category']  = $cat_name;
        $items_data['price']          = floatval(($product = wc_get_product($product_id)) ? $product->get_price() : false);
        $items_data['quantity']       = $item->get_quantity();
        $i++;
        $data['items'][] = $items_data;
    }
    wp_enqueue_script('ga4_purchase', plugin_dir_url(__FILE__) . 'assets/ga4_purchase.js', array('jquery'), '1.0', false);
    wp_localize_script('ga4_purchase', 'purchase_data', $data);
}


function shortcode_for_categories($atts) {
    $atts = shortcode_atts( array(
        'param1' => 'default_value'
    ), $atts );

     $atts['param1'];
     $category = get_term_by('name', $atts['param1'], 'product_cat');
     $category_id = $category->term_id;
     die(print_r($category_id,1));
    return '';
}
add_shortcode('custom_shortcode', 'shortcode_for_categories');


add_action('wp', 'view_item_list_for_shop');
function view_item_list_for_shop(){
    if(is_shop()){
        $data = array();
        $data['currency'] = get_woocommerce_currency();
        $data['item_list_id'] =  "12345";
        $data['item_list_name']= "All Products";

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1
        );
        $products = wc_get_products($args);
        $index = 0;
        foreach ($products as $product) {
            $product_id = $product->get_id();
            $product_data = wc_get_product($product_id);
            $item_data['item_id'] = $product_id;
            $item_data['item_name'] = $product_data->get_name();
            $item_data['index'] = $index;
            //getting discount details for product
            if ($product_data->get_sale_price() !== '') {
                $discount_amount = $product_data->get_regular_price() - $product_data->get_sale_price();
                $item_data['discount'] = $discount_amount;
            } else {
                $item_data['discount'] = 0;
            }
            $item_data['item_brand'] = "Organic Store";
            if (!empty($category_ids)) {
                $category_id = $category_ids[0];
                $category = get_term($category_id, 'product_cat');
                $item_data['item_category'] = $category->name;
            } else {
                $item_data['item_category'] = "Uncategorized";
            }
            $item_data['item_list_id'] =  "12345";
            $item_data['item_list_name']= "All Products";
            $item_data['price'] = floatval($product_data->get_price());
            $item_data['quantity'] = 1;
            $data['items'][] = $item_data;
            $index++;
        }
        // error_log(print_r($data,1));
        wp_enqueue_script('ga4_view_item_list_for_shop_event', plugin_dir_url(__FILE__) . 'assets/ga4_view_item_list_for_shop_event', array('jquery'), '1.0', false);
        wp_localize_script('ga4_view_item_list_for_shop_event', 'item_list_data', $data);
    }
    
}