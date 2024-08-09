<?php
/*
Plugin Name: Custom Payment Methods Based on Category
Description: Altera os métodos de pagamento disponíveis com base nas categorias dos produtos no carrinho e adiciona um alerta para evitar combinações de pagamento incompatíveis.
Author: Mateus Cardoso
Version: 1.0
*/

function custom_payment_methods_based_on_category( $available_gateways ) {
    if ( ! WC()->cart ) {
        return $available_gateways; 
    }

    $asaas_categories = array('Novos', 'Semi-Novos', 'Peças DBS');
    $asaas_category_ids = get_term_ids_by_names($asaas_categories);

    $has_asaas_category = false;
    $has_non_asaas_category = false;
    $product_names_asaas = array();
    $product_names_non_asaas = array();

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product_name = get_the_title( $product_id );
        $product_categories = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'ids') );

        if ( has_asaas_category( $product_categories, $asaas_category_ids ) ) {
            $has_asaas_category = true;
            $product_names_asaas[] = $product_name;
        } else {
            $has_non_asaas_category = true;
            $product_names_non_asaas[] = $product_name;
        }
    }


if ( $has_asaas_category && $has_non_asaas_category ) {
    if (!empty($product_names_asaas)) {
        $error_message = 'O(s) produto(s) ' . implode(', ', $product_names_asaas) . ' não podem ser comprados junto com peças. Por favor, faça pedidos separados.';
        
        if ( ! isset( WC()->session->custom_payment_error ) || ! WC()->session->custom_payment_error ) {
            wc_add_notice($error_message, 'error');
            WC()->session->custom_payment_error = true; 
        }
        return array(); 
    }
}



    if ( $has_asaas_category ) {
        foreach ( $available_gateways as $gateway_id => $gateway ) {
            if ( !in_array( $gateway_id, array('asaas-pix', 'asaas-credit-card', 'asaas-ticket') ) ) {
                unset( $available_gateways[$gateway_id] );
            }
        }
    } else {
        foreach ( $available_gateways as $gateway_id => $gateway ) {
            if ( !in_array( $gateway_id, array('woo-mercado-pago-basic', 'woo-mercado-pago-pix', 'woo-mercado-pago-ticket', 'woo-mercado-pago-custom') ) ) {
                unset( $available_gateways[$gateway_id] );
            }
        }
    }

    if ( isset( WC()->session->custom_payment_error ) && WC()->session->custom_payment_error ) {
        unset( WC()->session->custom_payment_error );
    }

    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'custom_payment_methods_based_on_category' );

function get_term_ids_by_names($category_names) {
    $category_ids = array();
    foreach ($category_names as $name) {
        $term = get_term_by('name', $name, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $category_ids[] = $term->term_id;
        }
    }
    return $category_ids;
}

function has_asaas_category($product_categories, $asaas_category_ids) {
    foreach ($product_categories as $cat_id) {
        if (in_array($cat_id, $asaas_category_ids) || is_descendant_of($cat_id, $asaas_category_ids)) {
            return true;
        }
    }
    return false;
}

function is_descendant_of($cat_id, $parent_ids) {
    $parent = get_term($cat_id, 'product_cat');
    while ($parent && !is_wp_error($parent)) {
        if (in_array($parent->parent, $parent_ids)) {
            return true;
        }
        $parent = get_term($parent->parent, 'product_cat');
    }
    return false;
}

function reset_custom_payment_error_notice() {
    if ( isset( WC()->session->custom_payment_error ) ) {
        unset( WC()->session->custom_payment_error );
    }
}
add_action( 'woocommerce_before_checkout_form', 'reset_custom_payment_error_notice', 1 );
