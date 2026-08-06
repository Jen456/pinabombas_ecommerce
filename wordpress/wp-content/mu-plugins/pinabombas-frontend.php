<?php
/**
 * Plugin Name: Pinabombas - Frontend de tienda
 * Description: Capa visual y CRO para Storefront y WooCommerce: barra superior, hero, tarjetas, busqueda y flujo de cotizacion.
 * Version: 1.1.0
 */
if (!defined('ABSPATH')) exit;

define('PB_FRONTEND_VERSION', '1.1.0');

add_action('wp_enqueue_scripts', function () {
    $css_file = __DIR__ . '/pinabombas-frontend.css';
    $version = file_exists($css_file) ? (string) filemtime($css_file) : PB_FRONTEND_VERSION;
    wp_enqueue_style(
        'pinabombas-frontend',
        plugins_url('pinabombas-frontend.css', __FILE__),
        array(),
        $version
    );
}, 50);

add_filter('body_class', function ($classes) {
    $classes[] = 'pb-storefront-upgrade';
    $classes[] = 'pb-quote-store';
    return $classes;
});

add_filter('woocommerce_page_title', function ($title) {
    return $title === 'Shop' ? 'Productos' : $title;
});

add_filter('woocommerce_catalog_orderby', function () {
    return array(
        'menu_order' => 'Orden recomendado',
        'date'       => 'Mas recientes',
        'popularity' => 'Mas consultados',
    );
});

add_filter('gettext', function ($translated, $text, $domain) {
    if (!is_admin() && in_array($domain, array('woocommerce', 'storefront', 'default'), true)) {
        $replacements = array(
            'Shop' => 'Productos',
            'Search' => 'Buscar',
            'Search products…' => 'Buscar por marca, modelo o referencia',
            'Search products&hellip;' => 'Buscar por marca, modelo o referencia',
            'Default sorting' => 'Orden recomendado',
            'Sort by popularity' => 'Mas consultados',
            'Sort by latest' => 'Mas recientes',
            'Related products' => 'Productos relacionados',
            'Description' => 'Descripcion',
            'Reviews' => 'Opiniones',
            'Reviews (%d)' => 'Opiniones (%d)',
            'My Account' => 'Mi cuenta',
            'Cart' => 'Carrito',
            'View your shopping cart' => 'Ver carrito',
        );
        if (isset($replacements[$text])) return $replacements[$text];
    }
    return $translated;
}, 20, 3);

add_filter('woocommerce_product_search_form', function ($form) {
    $form = str_replace('placeholder="Search products&hellip;"', 'placeholder="Buscar por marca, modelo o referencia"', $form);
    $form = str_replace('placeholder="Search products…"', 'placeholder="Buscar por marca, modelo o referencia"', $form);
    $form = str_replace('value="Search"', 'value="Buscar"', $form);
    $form = str_replace('>Search</button>', '>Buscar</button>', $form);
    return $form;
});

add_filter('woocommerce_product_tabs', function ($tabs) {
    if (isset($tabs['description'])) $tabs['description']['title'] = 'Descripcion';
    unset($tabs['reviews']);
    return $tabs;
}, 90);

add_action('storefront_before_header', function () {
    if (is_admin()) return;
    ?>
    <div class="pb-topbar" role="region" aria-label="Informacion de atencion">
        <div class="col-full pb-topbar__inner">
            <span>Atencion especializada en Ecuador</span>
            <span>Bombas, piñas y reparacion</span>
            <span>Cotizacion directa por WhatsApp</span>
        </div>
    </div>
    <?php
}, 5);

add_action('storefront_before_content', function () {
    if (!function_exists('is_shop')) return;

    if (!(is_shop() || is_product_category() || is_product_tag())) return;
    ?>
    <section class="pb-shop-hero" aria-label="Resumen de tienda">
        <div class="col-full pb-shop-hero__inner">
            <div class="pb-shop-hero__copy">
                <p class="pb-eyebrow">EXPERTOS EN SISTEMAS DE COMBUSTIBLE</p>
                <h1>Bombas y Piñas de Combustible: Garantía y Respaldo Técnico Especializado.</h1>
                <p>Venta, diagnóstico y reparación con la asesoría de un Ingeniero Eléctrico. Compatibilidad verificada antes de tu compra.</p>
                <div class="pb-hero-actions" aria-label="Acciones principales">
                    <a class="button pb-wa-btn pb-hero-cta" href="<?php echo esc_url(function_exists('pb_wa_link') ? pb_wa_link(null, 'Hola, quiero cotizar una bomba o piña de combustible. Tengo marca, modelo y año del vehiculo.') : '#'); ?>" target="_blank" rel="nofollow">Cotizar por WhatsApp</a>
                    <a class="pb-hero-link" href="#pb-product-filters">Ver marcas disponibles</a>
                </div>
            </div>
            <div class="pb-shop-hero__stats" aria-label="Servicios principales">
                <div>
                    <strong>Ingeniería aplicada</strong>
                    <span>Diagnóstico y respaldo técnico</span>
                </div>
                <div>
                    <strong>Garantía</strong>
                    <span>Por defectos de fábrica</span>
                </div>
                <div>
                    <strong>Compatibilidad</strong>
                    <span>Verificada antes de comprar</span>
                </div>
            </div>
        </div>
    </section>
    <?php
}, 5);

add_action('woocommerce_before_shop_loop', function () {
    if (!function_exists('is_shop')) return;

    if (!(is_shop() || is_product_category() || is_product_tag())) return;
    ?>
    <div class="pb-shop-assurance" aria-label="Ventajas de compra">
        <div>
            <strong>Compatibilidad revisada</strong>
            <span>Envia marca, modelo y año antes de comprar.</span>
        </div>
        <div>
            <strong>Precio a consultar</strong>
            <span>Cotizacion segun disponibilidad y referencia.</span>
        </div>
        <div>
            <strong>Soporte tecnico</strong>
            <span>Orientacion para seleccion e instalacion.</span>
        </div>
    </div>
    <?php
}, 4);

add_action('woocommerce_before_shop_loop', function () {
    if (!function_exists('is_shop') || !(is_shop() || is_product_category() || is_product_tag())) return;

    $terms = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'number'     => 14,
    ));

    if (is_wp_error($terms) || empty($terms)) return;

    echo '<nav id="pb-product-filters" class="pb-brand-filter" aria-label="Filtrar por marca">';
    echo '<span>Filtrar por marca</span>';
    foreach ($terms as $term) {
        if ($term->slug === 'bombas-de-combustible') continue;
        echo '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
    }
    echo '</nav>';
}, 5);

add_action('woocommerce_before_shop_loop_item_title', function () {
    echo '<span class="pb-product-badge">Cotizacion tecnica</span>';
}, 8);

add_action('woocommerce_single_product_summary', function () {
    ?>
    <div class="pb-single-assurance" aria-label="Informacion de compra">
        <span>Verificacion por vehiculo</span>
        <span>Disponibilidad por WhatsApp</span>
        <span>Garantia por defectos de fabrica</span>
    </div>
    <?php
}, 28);

add_action('woocommerce_after_single_product_summary', function () {
    if (!function_exists('is_product') || !is_product()) return;
    ?>
    <section class="pb-fitment-panel" aria-label="Datos para confirmar compatibilidad">
        <div>
            <p class="pb-eyebrow">Antes de cotizar</p>
            <h2>Ten a mano los datos del vehiculo</h2>
            <p>Para confirmar la bomba correcta, comparte marca, modelo, año, motor y una foto de la pieza o referencia si la tienes.</p>
        </div>
        <ul>
            <li>Marca y modelo</li>
            <li>Año y cilindraje</li>
            <li>Foto o codigo de la pieza</li>
        </ul>
    </section>
    <?php
}, 7);

add_action('wp', function () {
    remove_action('storefront_header', 'storefront_header_cart', 60);
    remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);
});

add_filter('storefront_handheld_footer_bar_links', function ($links) {
    unset($links['my-account'], $links['cart']);
    return $links;
}, 20);

add_filter('document_title_parts', function ($parts) {
    if (function_exists('is_shop') && is_shop()) {
        $parts['title'] = 'Productos';
    }
    return $parts;
}, 20);

add_action('storefront_before_content', function () {
    if (!is_front_page()) return;

    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $wa_url = function_exists('pb_wa_link')
        ? pb_wa_link(null, 'Hola, quiero cotizar una bomba de combustible. Tengo marca, modelo y año del vehiculo.')
        : '#';
    $hero_img = plugins_url('pinabombas-hero-engineering-motion.png', __FILE__);
    ?>
    <section class="pb-home-hero pb-home-hero--ad" aria-label="Presentacion principal">
        <span class="pb-home-hero__slash pb-home-hero__slash--top" aria-hidden="true"></span>
        <span class="pb-home-hero__slash pb-home-hero__slash--bottom" aria-hidden="true"></span>
        <span class="pb-home-hero__dots pb-home-hero__dots--left" aria-hidden="true"></span>
        <span class="pb-home-hero__dots pb-home-hero__dots--right" aria-hidden="true"></span>
        <div class="col-full pb-home-hero__frame">
            <div class="pb-home-hero__grid">
                <div class="pb-home-hero__copy">
                    <p class="pb-eyebrow">EXPERTOS EN SISTEMAS DE COMBUSTIBLE</p>
                    <h1>Venta, Diagnóstico y Reparación de Bombas de Combustible</h1>
                    <p>Stock para todas las marcas y atención técnica especializada por el Ing. Eléctrico Jorge Piña. Verificamos compatibilidad antes de tu compra.</p>
                    <div class="pb-home-hero__actions" aria-label="Acciones principales">
                        <a class="button pb-home-hero__catalog" href="<?php echo esc_url($shop_url); ?>">Ver Catálogo de Productos</a>
                        <a class="button pb-home-hero__whatsapp" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="nofollow">Consultar por WhatsApp</a>
                    </div>
                    <form class="pb-home-hero__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <label class="screen-reader-text" for="pb-home-search">Buscar producto</label>
                        <input id="pb-home-search" type="search" name="s" placeholder="Buscar por marca, modelo o referencia">
                        <input type="hidden" name="post_type" value="product">
                        <button type="submit">Buscar</button>
                    </form>
                </div>
                <figure class="pb-home-hero__media">
                    <img src="<?php echo esc_url($hero_img); ?>" alt="Bomba de combustible revisada en taller tecnico" loading="eager" decoding="async">
                </figure>
            </div>
            <div class="pb-home-hero__cards" aria-label="Servicios destacados">
                <article>
                    <span aria-hidden="true">01</span>
                    <h2>Venta de repuestos</h2>
                    <p>Bombas y piñas por marca, modelo y referencia.</p>
                </article>
                <article>
                    <span aria-hidden="true">02</span>
                    <h2>Diagnóstico técnico</h2>
                    <p>Revisión de compatibilidad antes de cotizar.</p>
                </article>
                <article>
                    <span aria-hidden="true">03</span>
                    <h2>Reparación con respaldo</h2>
                    <p>Atención especializada y garantía por defectos.</p>
                </article>
            </div>
        </div>
    </section>
    <?php
}, 4);

add_action('wp', function () {
    if (is_front_page()) {
        remove_action('storefront_page', 'storefront_page_header', 10);
    }
});

add_filter('render_block', function ($block_content, $block) {
    static $pb_removed_home_cover = false;

    if (is_admin() || !is_front_page() || $pb_removed_home_cover) {
        return $block_content;
    }

    if (($block['blockName'] ?? '') === 'core/cover') {
        $pb_removed_home_cover = true;
        return '';
    }

    return $block_content;
}, 10, 2);
