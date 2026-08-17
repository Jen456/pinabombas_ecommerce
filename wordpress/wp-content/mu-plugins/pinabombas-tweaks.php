<?php
/**
 * Plugin Name: Piña Bombas - Ajustes E-commerce
 * Description: WhatsApp dinámico, precio a consultar, pestañas de especificaciones, admin bar, schema.
 * Version: 2.1
 */
if (!defined('ABSPATH')) exit;

if (!defined('PB_WHATSAPP')) define('PB_WHATSAPP', '593991015866');

add_filter('woocommerce_get_price_html', function ($price, $product) {
    $p = $product->get_price();
    if ($p === '' || $p === null) return '<span class="precio-consultar">Precio: a consultar</span>';
    return $price;
}, 10, 2);

function pb_wa_link($product = null, $fallback = null) {
    $base = 'https://wa.me/' . PB_WHATSAPP . '?text=';
    if ($product instanceof WC_Product) {
        $sku = $product->get_sku();
        $msg = "Hola 👋, me interesa este producto:\n*" . $product->get_name() . "*";
        if ($sku) $msg .= "\nSKU: " . $sku;
        $msg .= "\n" . get_permalink($product->get_id());
        $msg .= "\n\n¿Precio y disponibilidad?";
    } else {
        $msg = $fallback ?: 'Hola 👋, quisiera información sobre bombas y piñas de combustible.';
    }
    return $base . rawurlencode($msg);
}

add_filter('woocommerce_loop_add_to_cart_link', function ($html, $product) {
    $url = pb_wa_link($product);
    return '<a href="' . esc_url($url) . '" target="_blank" rel="nofollow" class="button pb-wa-btn">Consultar por WhatsApp</a>';
}, 20, 2);

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product) return;
    $url = pb_wa_link($product);
    echo '<div class="pb-wa-single"><a href="' . esc_url($url) . '" target="_blank" rel="nofollow" class="button alt pb-wa-btn">Consultar por WhatsApp</a></div>';
}, 31);

add_action('wp_footer', function () {
    $url = pb_wa_link(null);
    echo '<a href="' . esc_url($url) . '" target="_blank" rel="nofollow" class="pb-wa-float" aria-label="Escríbenos por WhatsApp" title="WhatsApp 099 101 5866">'
       . '<svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 3C9.4 3 4 8.4 4 15c0 2.1.6 4.1 1.6 5.9L4 29l8.3-1.6c1.7.9 3.6 1.4 5.7 1.4 6.6 0 12-5.4 12-12S22.6 3 16 3zm0 22c-1.8 0-3.5-.5-5-1.3l-.4-.2-4.9 1 1-4.8-.3-.5C5.5 18.6 5 16.8 5 15 5 9 9.9 4 16 4s11 5 11 11-4.9 10-11 10zm6.1-7.6c-.3-.2-1.9-1-2.2-1.1-.3-.1-.5-.2-.8.2-.2.3-.9 1.1-1 1.3-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-.9-1.6-1.9-1.8-2.3-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.6.1-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.8-1.9-1.1-2.6-.3-.7-.6-.6-.8-.6h-.7c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.3 1.4 3.5c.2.2 2.4 3.7 5.9 5.1.8.4 1.5.6 2 .8.8.3 1.6.2 2.2.1.7-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.2-.3-.2-.6-.4z"/></svg></a>';
});

add_action('wp_head', function () {
    echo '<style>
    .pb-wa-btn{background:#25D366!important;color:#fff!important;border-radius:4px}
    .pb-wa-btn:hover{background:#1da851!important;color:#fff!important}
    .pb-wa-single{margin-top:14px}
    .pb-wa-float{position:fixed;right:20px;bottom:20px;z-index:99999;width:72px;height:72px;border:4px solid #fff;border-radius:50%;background:#25D366;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(0,0,0,.30);transition:transform .15s}
    .pb-wa-float:hover{transform:scale(1.08)}
    .pb-wa-float svg{width:38px;height:38px;fill:#fff}
    table.pb-specs th{width:40%;background:#f5f6f8}
    </style>';
});

add_filter('show_admin_bar', function ($show) {
    return current_user_can('manage_options') ? $show : false;
});

add_filter('woocommerce_product_tabs', function ($tabs) {
    $tabs['pb_specs'] = array('title' => 'Especificaciones', 'priority' => 15, 'callback' => 'pb_render_specs');
    return $tabs;
});
function pb_render_specs() {
    global $product;
    $id = $product->get_id();
    $g  = get_post_meta($id, '_spec_presion', true);
    $rows = array(
        'Presión'              => get_post_meta($id, '_spec_presion', true),
        'Voltaje'              => get_post_meta($id, '_spec_voltaje', true) ?: '12 V DC (estándar automotriz)',
        'Flujo (L/h)'          => get_post_meta($id, '_spec_flujo', true),
        'Terminales/Conector'  => get_post_meta($id, '_spec_conector', true),
        'Compatibilidad'       => get_post_meta($id, '_spec_compat', true),
        'Garantía'             => get_post_meta($id, '_spec_garantia', true) ?: '3 meses por defectos de fábrica',
    );
    echo '<h2>Especificaciones técnicas</h2>';
    echo '<table class="shop_attributes pb-specs">';
    foreach ($rows as $k => $v) {
        if ($v === '' || $v === null) $v = '<em>Consultar</em>';
        echo '<tr><th>' . esc_html($k) . '</th><td>' . wp_kses_post($v) . '</td></tr>';
    }
    echo '</table>';
    echo '<p style="margin-top:10px"><em>¿Dudas de compatibilidad con tu vehículo? Escríbenos por WhatsApp con marca, modelo y año.</em></p>';
}

add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product()) return;
    global $product;
    if (!$product) return;
    $data = array(
        '@context'    => 'https://schema.org/',
        '@type'       => 'Product',
        'name'        => $product->get_name(),
        'image'       => wp_get_attachment_url($product->get_image_id()),
        'description' => wp_strip_all_tags($product->get_short_description() ?: $product->get_description()),
        'sku'         => $product->get_sku() ?: ('PB-' . $product->get_id()),
        'category'    => wp_strip_all_tags(wc_get_product_category_list($product->get_id())),
        'brand'       => array('@type' => 'Brand', 'name' => 'Piña Bombas de Combustible'),
    );
    echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}, 99);
