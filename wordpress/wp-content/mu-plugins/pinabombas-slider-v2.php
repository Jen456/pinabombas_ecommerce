<?php
/**
 * Plugin Name: Piña Bombas - Slider y refinamiento visual
 * Description: Banner administrable y portada comercial para Storefront y WooCommerce.
 * Version: 2.2.1
 */
if (!defined('ABSPATH')) exit;

function pbv2_slider_defaults() {
    $image = content_url('uploads/2026/08/Bomba-de-gasolina-Ford-F-150.png');
    return array(
        array(
            'enabled' => true,
            'eyebrow' => 'ESPECIALISTAS EN SISTEMAS DE COMBUSTIBLE',
            'title'   => 'Bombas y piñas de combustible para tu vehículo',
            'text'    => 'Repuestos para autos, camionetas y SUVs: bombas completas, piñas, filtros, prefiltros y reguladores. Verificamos compatibilidad antes de cotizar.',
            'image'   => $image,
        ),
        array(
            'enabled' => false,
            'eyebrow' => 'ASESORÍA PARA COMPRA DE REPUESTOS',
            'title'   => 'Compra la bomba correcta para tu vehículo',
            'text'    => 'Envía marca, modelo, año, motor y foto o código de la pieza. Te ayudamos a confirmar la referencia correcta.',
            'image'   => $image,
        ),
        array(
            'enabled' => false,
            'eyebrow' => 'COMPATIBILIDAD ANTES DE COMPRAR',
            'title'   => 'Envíanos los datos de tu vehículo y evita comprar mal',
            'text'    => 'Comparte marca, modelo, año, motor y foto de la pieza. Te ayudamos a confirmar disponibilidad y referencia.',
            'image'   => $image,
        ),
    );
}

function pbv2_get_slides() {
    $slides = array();
    foreach (pbv2_slider_defaults() as $index => $default) {
        $n = $index + 1;
        if (!(bool) get_theme_mod("pbv2_slide_{$n}_enabled", $default['enabled'])) continue;
        $slides[] = array(
            'eyebrow' => get_theme_mod("pbv2_slide_{$n}_eyebrow", $default['eyebrow']),
            'title'   => get_theme_mod("pbv2_slide_{$n}_title", $default['title']),
            'text'    => get_theme_mod("pbv2_slide_{$n}_text", $default['text']),
            'image'   => get_theme_mod("pbv2_slide_{$n}_image", $default['image']) ?: $default['image'],
        );
    }
    return apply_filters('pbv2_slider_slides', $slides);
}

function pbv2_product_showcase_cards($limit = 4) {
    $items = array(
        array('title' => 'Bomba de gasolina Ford F-150', 'file' => 'Bomba-de-gasolina-Ford-F-150.png'),
        array('title' => 'Bomba de gasolina Chery Tiggo 8 Pro', 'file' => 'Bomba-de-gasolina-Cherry-Tiggo-8-pro.png'),
        array('title' => 'Bomba de gasolina Suzuki Jimny', 'file' => 'Bomba-de-gasolina-Suzuky-Jimny.png'),
        array('title' => 'Bomba de gasolina Hyundai Tucson', 'file' => 'Bomba-de-gasolina-Hyundai-Tucson.png'),
    );

    $cards = array();
    foreach (array_slice($items, 0, $limit) as $item) {
        $cards[] = array(
            'title' => $item['title'],
            'url' => home_url('/shop/?s=' . rawurlencode($item['title']) . '&post_type=product'),
            'image_html' => '<img src="' . esc_url(content_url('uploads/2026/08/' . $item['file'])) . '" alt="' . esc_attr($item['title']) . '" loading="lazy">',
        );
    }
    return $cards;
}

add_action('customize_register', function ($customizer) {
    $customizer->add_section('pbv2_slider', array(
        'title' => 'Banner principal',
        'description' => 'Edita el banner desde WordPress. Activa más diapositivas cuando tengas imágenes diferentes.',
        'priority' => 35,
    ));

    foreach (pbv2_slider_defaults() as $index => $default) {
        $n = $index + 1;
        $customizer->add_setting("pbv2_slide_{$n}_enabled", array('default' => $default['enabled'], 'sanitize_callback' => 'wp_validate_boolean'));
        $customizer->add_control("pbv2_slide_{$n}_enabled", array('label' => "Diapositiva {$n}: mostrar", 'section' => 'pbv2_slider', 'type' => 'checkbox'));

        $customizer->add_setting("pbv2_slide_{$n}_image", array('default' => $default['image'], 'sanitize_callback' => 'esc_url_raw'));
        $customizer->add_control(new WP_Customize_Image_Control($customizer, "pbv2_slide_{$n}_image", array('label' => "Diapositiva {$n}: imagen", 'section' => 'pbv2_slider')));

        foreach (array('eyebrow' => 'Antetítulo', 'title' => 'Título') as $key => $label) {
            $customizer->add_setting("pbv2_slide_{$n}_{$key}", array('default' => $default[$key], 'sanitize_callback' => 'sanitize_text_field'));
            $customizer->add_control("pbv2_slide_{$n}_{$key}", array('label' => "Diapositiva {$n}: {$label}", 'section' => 'pbv2_slider', 'type' => 'text'));
        }

        $customizer->add_setting("pbv2_slide_{$n}_text", array('default' => $default['text'], 'sanitize_callback' => 'sanitize_textarea_field'));
        $customizer->add_control("pbv2_slide_{$n}_text", array('label' => "Diapositiva {$n}: descripción", 'section' => 'pbv2_slider', 'type' => 'textarea'));
    }
});

add_action('wp_enqueue_scripts', function () {
    $css = __DIR__ . '/pinabombas-slider-v2.css';
    wp_enqueue_style('pinabombas-slider-v2', plugins_url('pinabombas-slider-v2.css', __FILE__), array('pinabombas-frontend'), file_exists($css) ? (string) filemtime($css) : '2.2.1');
    if (is_front_page()) {
        $js = __DIR__ . '/pinabombas-slider-v2.js';
        wp_enqueue_script('pinabombas-slider-v2', plugins_url('pinabombas-slider-v2.js', __FILE__), array(), file_exists($js) ? (string) filemtime($js) : '2.2.1', true);
    }
}, 80);

add_action('storefront_before_content', function () {
    if (!is_front_page()) return;
    $slides = pbv2_get_slides();
    if (!$slides) return;

    $shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $whatsapp = function_exists('pb_wa_link') ? pb_wa_link(null, 'Hola, quiero cotizar una bomba o piña de combustible. Mi vehículo es: marca, modelo, año y motor.') : '#';
    $showcase_cards = pbv2_product_showcase_cards(4);
    ?>
    <section class="pbv2-slider" data-pbv2-slider data-delay="7000" aria-roledescription="carousel" aria-label="Piña Bombas de Combustible">
        <div class="pbv2-slider__viewport">
            <?php foreach ($slides as $index => $slide) : $active = $index === 0; ?>
                <article class="pbv2-slider__slide pbv2-slider__slide--<?php echo esc_attr((string) ($index + 1)); ?><?php echo $active ? ' is-active' : ''; ?>" data-pbv2-slide aria-hidden="<?php echo $active ? 'false' : 'true'; ?>">
                    <img class="pbv2-slider__image" src="<?php echo esc_url($slide['image']); ?>" alt="<?php echo esc_attr($slide['title']); ?>" loading="<?php echo $active ? 'eager' : 'lazy'; ?>" decoding="async" <?php echo $active ? 'fetchpriority="high"' : ''; ?>>
                    <span class="pbv2-slider__overlay" aria-hidden="true"></span>
                    <div class="pbv2-slider__content">
                        <?php if (!empty($showcase_cards)) : ?>
                            <div class="pbv2-slider__showcase" aria-label="Repuestos destacados">
                                <?php foreach ($showcase_cards as $card) : ?>
                                    <article class="pbv2-slider__product">
                                        <a href="<?php echo esc_url($card['url']); ?>">
                                            <?php echo wp_kses_post($card['image_html']); ?>
                                            <span><?php echo esc_html($card['title']); ?></span>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="pbv2-slider__copy">
                            <p class="pb-eyebrow"><span aria-hidden="true"></span><?php echo esc_html($slide['eyebrow']); ?></p>
                            <?php if ($active) : ?>
                                <h1><?php echo esc_html($slide['title']); ?></h1>
                            <?php else : ?>
                                <h2><?php echo esc_html($slide['title']); ?></h2>
                            <?php endif; ?>
                            <p class="pbv2-slider__description"><?php echo esc_html($slide['text']); ?></p>
                            <div class="pbv2-slider__actions">
                                <a class="button pbv2-slider__primary" href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="nofollow noopener">Cotizar por WhatsApp</a>
                                <a class="button pbv2-slider__secondary" href="<?php echo esc_url($shop); ?>">Ver catálogo</a>
                            </div>
                            <ul class="pbv2-slider__trust" aria-label="Ventajas principales">
                                <li>Compatibilidad verificada</li>
                                <li>Asesoría de compra</li>
                                <li>Repuestos multimarca</li>
                            </ul>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (count($slides) > 1) : ?>
                <div class="pbv2-slider__controls" aria-label="Controles del slider">
                    <button type="button" data-pbv2-prev aria-label="Diapositiva anterior">‹</button>
                    <div class="pbv2-slider__dots" role="tablist" aria-label="Elegir diapositiva">
                        <?php foreach ($slides as $index => $slide) : ?><button type="button" data-pbv2-dot role="tab" aria-label="Ir a diapositiva <?php echo esc_attr((string) ($index + 1)); ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>" class="<?php echo $index === 0 ? 'is-active' : ''; ?>"></button><?php endforeach; ?>
                    </div>
                    <button type="button" data-pbv2-toggle aria-label="Pausar slider" aria-pressed="false">Ⅱ</button>
                    <button type="button" data-pbv2-next aria-label="Siguiente diapositiva">›</button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="pbv2-benefits" aria-label="Por qué elegir Piña Bombas">
        <div class="pbv2-container pbv2-benefits__grid">
            <article><strong>Compatibilidad antes de comprar</strong><span>Revisamos marca, modelo, año, motor y referencia.</span></article>
            <article><strong>Asesoría especializada</strong><span>Atención supervisada por el Ing. Jorge Piña.</span></article>
            <article><strong>Stock multimarca</strong><span>Ford, Nissan, Chery, Suzuki, Chevrolet, Toyota, Kia, Hyundai y más.</span></article>
            <article><strong>Cotización directa</strong><span>Respuesta rápida por WhatsApp al 099 101 5866.</span></article>
        </div>
    </section>

    <section class="pbv2-home-section pbv2-categories">
        <div class="pbv2-container">
            <p class="pbv2-kicker">ENCUENTRA LO QUE NECESITAS</p>
            <h2>Productos para el sistema de combustible de tu vehículo</h2>
            <p class="pbv2-lead">No compres a ciegas. Dinos qué vehículo tienes y te ayudamos a buscar la referencia correcta para tu bomba, piña, filtro o regulador.</p>
            <div class="pbv2-category-grid">
                <a href="<?php echo esc_url($shop); ?>"><strong>Bombas de combustible</strong><span>Bombas completas para autos, camionetas y SUVs.</span><b>Ver productos →</b></a>
                <a href="<?php echo esc_url($shop); ?>"><strong>Filtros de combustible</strong><span>Nissan X-Trail, Suzuki S-Cross, Chevrolet Cruze, Ford F-150 y más.</span><b>Ver productos →</b></a>
                <a href="<?php echo esc_url($shop); ?>"><strong>Prefiltros</strong><span>Mallas y elementos de protección para la bomba.</span><b>Ver productos →</b></a>
                <a href="<?php echo esc_url($shop); ?>"><strong>Reguladores y accesorios</strong><span>Componentes para el sistema de inyección y presión.</span><b>Ver productos →</b></a>
            </div>
        </div>
    </section>

    <section class="pbv2-home-section pbv2-fitment">
        <div class="pbv2-container pbv2-fitment__grid">
            <div>
                <p class="pbv2-kicker">TE AYUDAMOS A ELEGIR</p>
                <h2>Envíanos los datos de tu vehículo antes de comprar</h2>
                <p>Para confirmar compatibilidad necesitamos marca, modelo, año, motor y, si la tienes, foto o código de la pieza. Así cotizamos con menos riesgo de error.</p>
                <a class="button pbv2-red-button" href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="nofollow noopener">Enviar datos por WhatsApp</a>
            </div>
            <ol>
                <li><b>1</b><span><strong>Identifica tu vehículo</strong>Marca, modelo, año y motor.</span></li>
                <li><b>2</b><span><strong>Envíanos una foto</strong>Pieza, conector o referencia si está disponible.</span></li>
                <li><b>3</b><span><strong>Recibe la cotización</strong>Confirmamos opción, disponibilidad y precio.</span></li>
            </ol>
        </div>
    </section>

    <section class="pbv2-home-section pbv2-brands">
        <div class="pbv2-container">
            <p class="pbv2-kicker">MULTIMARCA</p>
            <h2>Trabajamos con aplicaciones para distintas marcas</h2>
            <div class="pbv2-brand-list" aria-label="Marcas de vehículos">
                <span>Chevrolet</span><span>Toyota</span><span>Kia</span><span>Hyundai</span><span>Nissan</span><span>Mazda</span><span>Suzuki</span><span>Ford</span><span>Chery</span><span>Dongfeng</span><span>Mitsubishi</span><span>Peugeot</span><span>Renault</span>
            </div>
        </div>
    </section>

    <section class="pbv2-home-section pbv2-contact">
        <div class="pbv2-container pbv2-contact__grid">
            <div>
                <p class="pbv2-kicker">CONTACTO DIRECTO</p>
                <h2>Piña Bombas de Combustible</h2>
                <p><strong>WhatsApp:</strong> +593 99 101 5866</p>
                <p><strong>Dirección:</strong> Aguirre 1628 y Av. del Ejército, local esquinero frente a la gasolinera, Guayaquil.</p>
                <p><strong>Correo:</strong> autopinajr@gmail.com</p>
                <p><strong>Sitio:</strong> pinabombascombustible.com</p>
            </div>
            <div class="pbv2-socials" aria-label="Redes sociales">
                <a href="https://www.facebook.com/bombasdecombustiblec" target="_blank" rel="noopener">Facebook <span>@bombasdecombustiblec</span></a>
                <a href="https://www.instagram.com/pinabombascombustible.ec/" target="_blank" rel="noopener">Instagram <span>@pinabombascombustible.ec</span></a>
                <a href="https://www.tiktok.com/@pinabombascombustible.ec" target="_blank" rel="noopener">TikTok <span>@pinabombascombustible.ec</span></a>
                <a href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="nofollow noopener">WhatsApp <span>099 101 5866</span></a>
            </div>
        </div>
    </section>
    <?php
}, 3);

add_action('wp', function () {
    if (!is_front_page()) return;
    remove_action('storefront_page', 'storefront_page_content', 20);
});
