<?php
/*
Plugin Name: Custom Payment Methods for DBS Category
Description: Configurações personalizadas para métodos de pagamento e restrições de carrinho com base nas categorias dos produtos.
Author: Mateus Cardoso
Version: 2.1
*/

function custom_payment_methods_based_on_category( $available_gateways ) {
    if ( ! WC()->cart ) {
        return $available_gateways; 
    }

    $dbs_category = 'DBS';
    $dbs_category_id = get_term_id_by_name($dbs_category);

    $categories_in_cart = get_categories_in_cart(array($dbs_category_id));

    // Se o carrinho contém produtos da categoria "DBS"
    if ( $categories_in_cart['dbs'] ) {
        // Apenas os métodos da Belluno são permitidos
        $allowed_gateways = array('belluno_card', 'belluno_pix', 'belluno_bankslip');
    } else {
        // Métodos para categorias diferentes de "DBS"
        $allowed_gateways = array(
            'woo-pagarme-payments-credit_card',
            'woo-pagarme-payments-2_cards',
            'asaas-ticket',
            'asaas-pix'
        );
    }

    // Filtra os métodos permitidos
    $available_gateways = filter_gateways($available_gateways, $allowed_gateways);

    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'custom_payment_methods_based_on_category' );

function prevent_mixed_categories_in_cart( $passed, $product_id, $quantity, $variation_id = 0, $variation = '' ) {
    $dbs_category = 'DBS';
    $dbs_category_id = get_term_id_by_name($dbs_category);

    $categories_in_cart = get_categories_in_cart(array($dbs_category_id));

    // Verifica o produto que está sendo adicionado
    $product_categories = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'ids') );
    $is_dbs_product = in_array($dbs_category_id, $product_categories);

    // Bloqueia adição de produtos não "DBS" se houver "DBS" no carrinho, e vice-versa
    if ( $categories_in_cart['dbs'] && !$is_dbs_product || $categories_in_cart['non_dbs'] && $is_dbs_product ) {
        wc_add_notice('Produtos da categoria DBS não podem ser comprados junto com outros itens. Por favor, ajuste o carrinho.', 'error');
        return false;
    }

    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'prevent_mixed_categories_in_cart', 10, 5 );

function get_term_id_by_name($category_name) {
    $term = get_term_by('name', $category_name, 'product_cat');
    return ($term && !is_wp_error($term)) ? $term->term_id : 0;
}

function get_categories_in_cart($restricted_category_ids) {
    $categories_in_cart = array('dbs' => false, 'non_dbs' => false);

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product_categories = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array('fields' => 'ids') );
        if ( array_intersect($product_categories, $restricted_category_ids) ) {
            $categories_in_cart['dbs'] = true;
        } else {
            $categories_in_cart['non_dbs'] = true;
        }
    }

    return $categories_in_cart;
}

function filter_gateways($available_gateways, $allowed_gateways) {
    return array_filter($available_gateways, function($gateway_id) use ($allowed_gateways) {
        return in_array($gateway_id, $allowed_gateways);
    }, ARRAY_FILTER_USE_KEY);
}
