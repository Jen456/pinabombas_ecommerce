<?php
/**
 * Plugin Name: Pinabombas - Frontend de tienda
 * Description: Capa visual y CRO para Storefront y WooCommerce: barra superior, hero, tarjetas, busqueda y flujo de cotizacion.
 * Version: 1.2.0
 */
if (!defined('ABSPATH')) exit;

define('PB_FRONTEND_VERSION', '1.2.0');

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

function pb_frontend_wa_url($message) {
    return function_exists('pb_wa_link') ? pb_wa_link(null, $message) : '#';
}

function pb_frontend_shop_url() {
    return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
}

function pb_frontend_asset_url($filename) {
    return plugins_url('pinabombas-assets/' . ltrim($filename, '/'), __FILE__);
}

function pb_frontend_products($limit = 6) {
    if (!function_exists('wc_get_products')) return array();

    $products = wc_get_products(array(
        'status'  => 'publish',
        'limit'   => $limit,
        'orderby' => 'date',
        'order'   => 'DESC',
        'return'  => 'objects',
    ));

    return is_array($products) ? $products : array();
}

function pb_frontend_brand_names() {
    return array('Chevrolet', 'Hyundai', 'Kia', 'Toyota', 'Nissan', 'Mazda', 'Suzuki', 'Ford', 'Chery', 'Dongfeng', 'Mitsubishi', 'Peugeot', 'Renault', 'Fiat');
}

function pb_frontend_catalog_cards($limit = 6) {
    $cards = array();

    foreach (pb_frontend_products($limit) as $product) {
        if (!$product instanceof WC_Product) continue;
        $cards[] = array(
            'title' => $product->get_name(),
            'url' => get_permalink($product->get_id()),
            'image_html' => $product->get_image('woocommerce_thumbnail'),
            'wa_url' => function_exists('pb_wa_link') ? pb_wa_link($product) : pb_frontend_wa_url('Hola, quiero cotizar este repuesto de combustible.'),
        );
    }

    if (!empty($cards)) return array_slice($cards, 0, $limit);

    $fallbacks = array(
        array('title' => 'Bomba de gasolina Ford F-150', 'file' => 'Bomba-de-gasolina-Ford-F-150.png'),
        array('title' => 'Bomba de gasolina Chery Tiggo 8 Pro', 'file' => 'Bomba-de-gasolina-Cherry-Tiggo-8-pro.png'),
        array('title' => 'Bomba de gasolina Suzuki Jimny', 'file' => 'Bomba-de-gasolina-Suzuky-Jimny.png'),
        array('title' => 'Bomba de gasolina Hyundai Tucson', 'file' => 'Bomba-de-gasolina-Hyundai-Tucson.png'),
        array('title' => 'Bomba de gasolina Chevrolet Grand Vitara', 'file' => 'Bomba-de-gasolina-Chevrolet-Grand-Vitara.png'),
        array('title' => 'Bomba de gasolina Nissan Tiida', 'file' => 'Bomba-de-gasolina-Nissan-Tiida.png'),
    );

    foreach (array_slice($fallbacks, 0, $limit) as $item) {
        $url = pb_frontend_shop_url() . '?s=' . rawurlencode($item['title']) . '&post_type=product';
        $img = content_url('uploads/2026/08/' . $item['file']);
        $cards[] = array(
            'title' => $item['title'],
            'url' => $url,
            'image_html' => '<img src="' . esc_url($img) . '" alt="' . esc_attr($item['title']) . '" loading="lazy">',
            'wa_url' => pb_frontend_wa_url('Hola, quiero cotizar: ' . $item['title'] . '. Mi vehiculo es: marca, modelo, año y motor.'),
        );
    }

    return $cards;
}
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
            'Search products...' => 'Buscar por marca, modelo o referencia',
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
    $form = str_replace('placeholder="Search products..."', 'placeholder="Buscar por marca, modelo o referencia"', $form);
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
            <span>Guayaquil: Aguirre 1628 y Av. del Ejercito</span>
            <span>Bombas, piñas, filtros y repuestos</span>
            <span>WhatsApp: 099 101 5866</span>
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
                <h1>Bombas y Piñas de Combustible para tu Vehiculo</h1>
                <p>Tienda especializada en bombas, piñas, filtros, prefiltros y reguladores de combustible. Confirmamos compatibilidad por marca, modelo, año, motor y referencia antes de comprar.</p>
                <div class="pb-hero-actions" aria-label="Acciones principales">
                    <a class="button pb-wa-btn pb-hero-cta" href="<?php echo esc_url(pb_frontend_wa_url('Hola, quiero cotizar una bomba o piña de combustible. Tengo marca, modelo y año del vehiculo.')); ?>" target="_blank" rel="nofollow">Cotizar por WhatsApp</a>
                    <a class="pb-hero-link" href="#pb-product-filters">Ver marcas disponibles</a>
                </div>
            </div>
            <div class="pb-shop-hero__stats" aria-label="Servicios principales">
                <div>
                    <strong>Asesoria</strong>
                    <span>Orientacion para elegir el repuesto correcto</span>
                </div>
                <div>
                    <strong>Garantia</strong>
                    <span>Respaldo por defectos de fabrica</span>
                </div>
                <div>
                    <strong>Compatibilidad</strong>
                    <span>Confirmada antes de comprar</span>
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
            <strong>Asesoria de compra</strong>
            <span>Orientacion para seleccionar el repuesto compatible.</span>
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
    echo '<span class="pb-product-badge">Cotizar repuesto</span>';
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

    $shop_url = pb_frontend_shop_url();
    $wa_url = pb_frontend_wa_url('Hola, quiero cotizar un repuesto de combustible. Mi vehiculo es: marca, modelo, año y motor.');
    $product_cards = pb_frontend_catalog_cards(4);
    ?>
    <section class="pb-home-hero pb-home-hero--catalog" aria-label="Tienda de repuestos de combustible">
        <div class="col-full pb-home-hero__shell">
            <div class="pb-home-hero__copy">
                <p class="pb-eyebrow">TIENDA ESPECIALIZADA EN REPUESTOS</p>
                <h1>Bombas y piñas de combustible para tu vehiculo</h1>
                <p>Repuestos para autos, camionetas y SUVs: bombas completas, piñas, filtros, prefiltros y reguladores. Verificamos compatibilidad antes de cotizar.</p>
                <div class="pb-home-hero__actions" aria-label="Acciones principales">
                    <a class="button pb-home-hero__catalog" href="<?php echo esc_url($shop_url); ?>">Ver catalogo</a>
                    <a class="button pb-home-hero__whatsapp" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="nofollow">Cotizar por WhatsApp</a>
                </div>
                <div class="pb-home-hero__trust" aria-label="Ventajas de compra">
                    <span>Bombas completas</span>
                    <span>Filtros y prefiltros</span>
                    <span>Compatibilidad verificada</span>
                </div>
            </div>

            <div class="pb-hero-products" aria-label="Productos del catalogo">
                <?php foreach ($product_cards as $index => $card) : ?>
                    <article class="pb-hero-product pb-hero-product--<?php echo esc_attr($index + 1); ?>">
                        <a href="<?php echo esc_url($card['url']); ?>">
                            <?php echo wp_kses_post($card['image_html']); ?>
                            <span><?php echo esc_html($card['title']); ?></span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}, 4);

add_action('storefront_before_content', function () {
    if (!is_front_page()) return;

    $shop_url = pb_frontend_shop_url();
    $wa_url = pb_frontend_wa_url('Hola, quiero revisar compatibilidad de un repuesto de combustible para mi vehiculo.');
    $product_cards = pb_frontend_catalog_cards(6);
    ?>
    <section class="pb-home-content pb-home-content--catalog" aria-label="Contenido de catalogo de repuestos">
        <div class="col-full">
            <div class="pb-section-heading">
                <p class="pb-eyebrow">CATALOGO MULTIMARCA</p>
                <h2>Repuestos de combustible por marca y modelo</h2>
                <p>Stock multimarca para autos, camionetas y SUVs: Ford, Nissan, Chery, Suzuki, Chevrolet, Toyota, Kia, Hyundai, Mazda y mas. Cada pedido se confirma por WhatsApp antes de comprar.</p>
            </div>

            <div class="pb-category-strip" aria-label="Categorias principales">
                <article><span>01</span><h3>Bombas completas</h3><p>Conjunto listo para reemplazo segun modelo, tanque y conector.</p></article>
                <article><span>02</span><h3>Piñas de combustible</h3><p>Modulo completo con filtro interno, terminales y conectores compatibles.</p></article>
                <article><span>03</span><h3>Filtros y prefiltros</h3><p>Opciones para Nissan X-Trail, Suzuki S-Cross, Chevrolet Cruze, Ford F-150 y mas.</p></article>
                <article><span>04</span><h3>Reguladores y accesorios</h3><p>Componentes para completar el sistema de inyeccion y presion de combustible.</p></article>
            </div>

            <div class="pb-brand-wall" aria-label="Marcas disponibles">
                <?php foreach (pb_frontend_brand_names() as $brand) : ?>
                    <a href="<?php echo esc_url($shop_url . '?s=' . rawurlencode($brand) . '&post_type=product'); ?>"><?php echo esc_html($brand); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="pb-buying-guide" aria-label="Como cotizar el repuesto correcto">
                <div>
                    <p class="pb-eyebrow">COMPRA CON MENOS RIESGO</p>
                    <h2>Datos para confirmar compatibilidad</h2>
                    <p>Antes de cotizar, envia marca, modelo, año, motor y una foto de la pieza o codigo si lo tienes. Asi evitamos referencias incorrectas.</p>
                    <a class="button pb-home-hero__catalog" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="nofollow">Enviar datos por WhatsApp</a>
                </div>
                <ol>
                    <li><strong>Marca y modelo</strong><span>Ejemplo: Ford Ranger, Nissan Frontier, Chery Tiggo, Suzuki Jimny.</span></li>
                    <li><strong>Año y motor</strong><span>Ayuda a diferenciar versiones y conectores.</span></li>
                    <li><strong>Foto o referencia</strong><span>Permite comparar la pieza antes de confirmar disponibilidad.</span></li>
                </ol>
            </div>

            <div class="pb-featured-products" aria-label="Productos destacados">
                <div class="pb-featured-products__header">
                    <h2>Productos destacados</h2>
                    <a href="<?php echo esc_url($shop_url); ?>">Ver todos</a>
                </div>
                <div class="pb-featured-products__grid">
                    <?php foreach ($product_cards as $card) : ?>
                        <article>
                            <a href="<?php echo esc_url($card['url']); ?>">
                                <?php echo wp_kses_post($card['image_html']); ?>
                                <h3><?php echo esc_html($card['title']); ?></h3>
                            </a>
                            <a class="button pb-wa-btn" href="<?php echo esc_url($card['wa_url']); ?>" target="_blank" rel="nofollow">Consultar</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
}, 6);

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
