<?php
/**
 * Plugin Name: Piña Bombas - Slider y refinamiento visual
 * Description: Slider administrable, mejoras de cabecera, catálogo y experiencia móvil para Storefront.
 * Version: 2.0.0
 */
if (!defined('ABSPATH')) exit;

function pbv2_slider_defaults() {
    $image = plugins_url('pinabombas-hero-engineering-motion.png', __FILE__);
    return array(
        array(
            'eyebrow' => 'EXPERTOS EN SISTEMAS DE COMBUSTIBLE',
            'title' => 'Bombas de combustible para cada vehículo',
            'text' => 'Venta de bombas y piñas con compatibilidad verificada antes de cotizar. Atención técnica para todas las marcas.',
            'image' => $image,
        ),
        array(
            'eyebrow' => 'DIAGNÓSTICO Y REPARACIÓN ESPECIALIZADA',
            'title' => 'Recupera el sistema de combustible de tu vehículo',
            'text' => 'Diagnóstico, reparación y adaptación de componentes con respaldo técnico especializado.',
            'image' => $image,
        ),
        array(
            'eyebrow' => 'COMPATIBILIDAD ANTES DE COMPRAR',
            'title' => 'Envíanos marca, modelo, año y referencia',
            'text' => 'Confirmamos la pieza correcta, disponibilidad y precio para evitar compras equivocadas.',
            'image' => $image,
        ),
    );
}

function pbv2_get_slides() {
    $slides = array();
    foreach (pbv2_slider_defaults() as $index => $default) {
        $n = $index + 1;
        if (!(bool) get_theme_mod("pbv2_slide_{$n}_enabled", true)) continue;
        $slides[] = array(
            'eyebrow' => get_theme_mod("pbv2_slide_{$n}_eyebrow", $default['eyebrow']),
            'title' => get_theme_mod("pbv2_slide_{$n}_title", $default['title']),
            'text' => get_theme_mod("pbv2_slide_{$n}_text", $default['text']),
            'image' => get_theme_mod("pbv2_slide_{$n}_image", $default['image']) ?: $default['image'],
        );
    }
    return apply_filters('pbv2_slider_slides', $slides);
}

add_action('customize_register', function ($customizer) {
    $customizer->add_section('pbv2_slider', array(
        'title' => 'Slider principal',
        'description' => 'Edita el banner principal sin Elementor.',
        'priority' => 35,
    ));

    foreach (pbv2_slider_defaults() as $index => $default) {
        $n = $index + 1;
        $customizer->add_setting("pbv2_slide_{$n}_enabled", array(
            'default' => true,
            'sanitize_callback' => 'wp_validate_boolean',
        ));
        $customizer->add_control("pbv2_slide_{$n}_enabled", array(
            'label' => "Diapositiva {$n}: mostrar",
            'section' => 'pbv2_slider',
            'type' => 'checkbox',
        ));

        $customizer->add_setting("pbv2_slide_{$n}_image", array(
            'default' => $default['image'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $customizer->add_control(new WP_Customize_Image_Control(
            $customizer,
            "pbv2_slide_{$n}_image",
            array('label' => "Diapositiva {$n}: imagen", 'section' => 'pbv2_slider')
        ));

        foreach (array('eyebrow' => 'Antetítulo', 'title' => 'Título') as $key => $label) {
            $customizer->add_setting("pbv2_slide_{$n}_{$key}", array(
                'default' => $default[$key],
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $customizer->add_control("pbv2_slide_{$n}_{$key}", array(
                'label' => "Diapositiva {$n}: {$label}",
                'section' => 'pbv2_slider',
                'type' => 'text',
            ));
        }

        $customizer->add_setting("pbv2_slide_{$n}_text", array(
            'default' => $default['text'],
            'sanitize_callback' => 'sanitize_textarea_field',
        ));
        $customizer->add_control("pbv2_slide_{$n}_text", array(
            'label' => "Diapositiva {$n}: descripción",
            'section' => 'pbv2_slider',
            'type' => 'textarea',
        ));
    }
});

add_action('wp_enqueue_scripts', function () {
    $css = __DIR__ . '/pinabombas-slider-v2.css';
    wp_enqueue_style(
        'pinabombas-slider-v2',
        plugins_url('pinabombas-slider-v2.css', __FILE__),
        array('pinabombas-frontend'),
        file_exists($css) ? (string) filemtime($css) : '2.0.0'
    );

    if (is_front_page()) {
        $js = __DIR__ . '/pinabombas-slider-v2.js';
        wp_enqueue_script(
            'pinabombas-slider-v2',
            plugins_url('pinabombas-slider-v2.js', __FILE__),
            array(),
            file_exists($js) ? (string) filemtime($js) : '2.0.0',
            true
        );
    }
}, 80);

add_action('storefront_before_content', function () {
    if (!is_front_page()) return;
    $slides = pbv2_get_slides();
    if (!$slides) return;

    $shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $whatsapp = function_exists('pb_wa_link')
        ? pb_wa_link(null, 'Hola, quiero cotizar una bomba o piña de combustible. Tengo marca, modelo, año y referencia del vehículo.')
        : '#';
    ?>
    <section class="pbv2-slider" data-pbv2-slider data-delay="7000" aria-roledescription="carousel" aria-label="Servicios destacados de Piña Bombas">
        <div class="pbv2-slider__viewport">
            <?php foreach ($slides as $index => $slide) : $active = $index === 0; ?>
                <article class="pbv2-slider__slide pbv2-slider__slide--<?php echo esc_attr((string) ($index + 1)); ?><?php echo $active ? ' is-active' : ''; ?>" data-pbv2-slide aria-hidden="<?php echo $active ? 'false' : 'true'; ?>">
                    <img class="pbv2-slider__image" src="<?php echo esc_url($slide['image']); ?>" alt="<?php echo esc_attr($slide['title']); ?>" loading="<?php echo $active ? 'eager' : 'lazy'; ?>" decoding="async" <?php echo $active ? 'fetchpriority="high"' : ''; ?>>
                    <span class="pbv2-slider__overlay" aria-hidden="true"></span>
                    <div class="col-full pbv2-slider__content">
                        <div class="pbv2-slider__copy">
                            <p class="pb-eyebrow"><?php echo esc_html($slide['eyebrow']); ?></p>
                            <?php if ($active) : ?><h1><?php else : ?><h2><?php endif; ?>
                                <?php echo esc_html($slide['title']); ?>
                            <?php if ($active) : ?></h1><?php else : ?></h2><?php endif; ?>
                            <p><?php echo esc_html($slide['text']); ?></p>
                            <div class="pbv2-slider__actions">
                                <a class="button pbv2-slider__primary" href="<?php echo esc_url($shop); ?>">Ver catálogo</a>
                                <a class="button pbv2-slider__secondary" href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="nofollow noopener">Cotizar por WhatsApp</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (count($slides) > 1) : ?>
                <div class="pbv2-slider__controls" aria-label="Controles del slider">
                    <button type="button" data-pbv2-prev aria-label="Diapositiva anterior">‹</button>
                    <div class="pbv2-slider__dots" role="tablist" aria-label="Elegir diapositiva">
                        <?php foreach ($slides as $index => $slide) : ?>
                            <button type="button" data-pbv2-dot role="tab" aria-label="Ir a diapositiva <?php echo esc_attr((string) ($index + 1)); ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>" class="<?php echo $index === 0 ? 'is-active' : ''; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" data-pbv2-toggle aria-label="Pausar slider" aria-pressed="false">Ⅱ</button>
                    <button type="button" data-pbv2-next aria-label="Siguiente diapositiva">›</button>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-full pbv2-slider__utility">
            <form class="pbv2-slider__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="screen-reader-text" for="pbv2-search">Buscar producto</label>
                <input id="pbv2-search" type="search" name="s" placeholder="Buscar por marca, modelo o referencia">
                <input type="hidden" name="post_type" value="product">
                <button type="submit">Buscar producto</button>
            </form>
            <div class="pbv2-slider__benefits">
                <article><strong>Compatibilidad verificada</strong><span>Evita comprar la pieza equivocada.</span></article>
                <article><strong>Diagnóstico especializado</strong><span>Atención técnica con experiencia real.</span></article>
                <article><strong>Cotización directa</strong><span>Precio y disponibilidad por WhatsApp.</span></article>
            </div>
        </div>
    </section>
    <?php
}, 3);
