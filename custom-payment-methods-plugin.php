<?php
/*
Plugin Name: Custom Payment Methods Based on Category
Description: Altera os métodos de pagamento disponíveis com base nas categorias dos produtos no carrinho e adiciona um alerta para evitar combinações de pagamento incompatíveis. Também impede a adição de produtos de categorias específicas no mesmo carrinho.
Author: Mateus Cardoso
Version: 1.6
*/

function custom_payment_methods_based_on_category( $available_gateways ) {
    if ( ! WC()->cart ) {
        return $available_gateways; 
    }

    $asaas_categories = array('Novos', 'Semi-Novos', 'Peças DBS');
    $asaas_category_ids = get_term_ids_by_names($asaas_categories);

    $categories_in_cart = get_categories_in_cart($asaas_category_ids);

    // Se o carrinho contém categorias misturadas
    if ( $categories_in_cart['asaas'] && $categories_in_cart['non_asaas'] ) {
        wc_add_notice('Produtos e peças não podem ser comprados juntos. Por favor, retire uma categoria do seu carrinho para adicionar este item.', 'error');
        WC()->session->custom_payment_error = true;
        return array(); // Nenhum gateway disponível
    }

    // Ajusta os gateways de pagamento com base nas categorias
    if ( $categories_in_cart['asaas'] ) {
        $available_gateways = filter_gateways($available_gateways, array('asaas-pix', 'asaas-credit-card', 'asaas-ticket'));
    } else {
        $available_gateways = filter_gateways($available_gateways, array('woo-mercado-pago-basic', 'woo-mercado-pago-pix', 'woo-mercado-pago-ticket', 'woo-mercado-pago-custom'));
    }

    // Remove o erro de pagamento customizado se a condição não for mais atendida
    if ( isset( WC()->session->custom_payment_error ) && WC()->session->custom_payment_error ) {
        unset( WC()->session->custom_payment_error );
    }

    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'custom_payment_methods_based_on_category' );

function prevent_mixed_categories_in_cart( $passed, $product_id, $quantity, $variation_id = 0, $variation = '' ) {
    $asaas_categories = array('Novos', 'Semi-Novos', 'Peças DBS');
    $asaas_category_ids = get_term_ids_by_names($asaas_categories);

    $categories_in_cart = get_categories_in_cart($asaas_category_ids);

    // Verifica o produto que está sendo adicionado
    $product_categories = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'ids') );
    $is_asaas_product = has_asaas_category($product_categories, $asaas_category_ids);

    if ( $categories_in_cart['asaas'] && !$is_asaas_product || $categories_in_cart['non_asaas'] && $is_asaas_product ) {
wc_add_notice('Você não pode comprar produtos e peças no mesmo pedido. Por favor, remova os itens de uma das categorias do seu carrinho para adicionar este item.', 'error');
        return false;
    }

    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'prevent_mixed_categories_in_cart', 10, 5 );

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

function get_categories_in_cart($asaas_category_ids) {
    $categories_in_cart = array('asaas' => false, 'non_asaas' => false);

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product_categories = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array('fields' => 'ids') );
        if ( has_asaas_category($product_categories, $asaas_category_ids) ) {
            $categories_in_cart['asaas'] = true;
        } else {
            $categories_in_cart['non_asaas'] = true;
        }
    }

    return $categories_in_cart;
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
    while ($parent_id = get_term($cat_id, 'product_cat')->parent) {
        if (in_array($parent_id, $parent_ids)) {
            return true;
        }
        $cat_id = $parent_id;
    }
    return false;
}

function filter_gateways($available_gateways, $allowed_gateways) {
    return array_filter($available_gateways, function($gateway_id) use ($allowed_gateways) {
        return in_array($gateway_id, $allowed_gateways);
    }, ARRAY_FILTER_USE_KEY);
}

function reset_custom_payment_error_notice() {
    if ( isset( WC()->session->custom_payment_error ) ) {
        unset( WC()->session->custom_payment_error );
    }
}
add_action( 'woocommerce_before_checkout_form', 'reset_custom_payment_error_notice', 1 );
