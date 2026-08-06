<?php
/**
 * Plugin Name: Piña Bombas - Slider y refinamiento visual
 * Description: Banner administrable y mejoras visuales para Storefront y WooCommerce.
 * Version: 2.1.0
 */
if (!defined('ABSPATH')) exit;

function pbv2_slider_defaults() {
    $image = plugins_url('pinabombas-hero-engineering-motion.png', __FILE__);

    return array(
        array(
            'enabled' => true,
            'eyebrow' => 'EXPERTOS EN SISTEMAS DE COMBUSTIBLE',
            'title'   => 'Bombas de combustible y piñas para autos',
            'text'    => 'Venta, diagnóstico y reparación con atención técnica especializada. Revisamos compatibilidad por marca, modelo, año, motor y referencia antes de cotizar.',
            'image'   => $image,
        ),
        array(
            'enabled' => false,
            'eyebrow' => 'DIAGNÓSTICO Y REPARACIÓN ESPECIALIZADA',
            'title'   => 'Recupera el sistema de combustible de tu vehículo',
            'text'    => 'Diagnóstico, reparación y adaptación de componentes con respaldo técnico especializado.',
            'image'   => $image,
        ),
        array(
            'enabled' => false,
            'eyebrow' => 'COMPATIBILIDAD ANTES DE COMPRAR',
            'title'   => 'Envíanos marca, modelo, año y referencia',
            'text'    => 'Confirmamos la pieza correcta, disponibilidad y precio para evitar compras equivocadas.',
            'image'   => $image,
        ),
    );
}

function pbv2_get_slides() {
    $slides = array();

    foreach (pbv2_slider_defaults() as $index => $default) {
        $n = $index + 1;
        $enabled = (bool) get_theme_mod("pbv2_slide_{$n}_enabled", $default['enabled']);
        if (!$enabled) continue;

        $slides[] = array(
            'eyebrow' => get_theme_mod("pbv2_slide_{$n}_eyebrow", $default['eyebrow']),
            'title'   => get_theme_mod("pbv2_slide_{$n}_title", $default['title']),
            'text'    => get_theme_mod("pbv2_slide_{$n}_text", $default['text']),
            'image'   => get_theme_mod("pbv2_slide_{$n}_image", $default['image']) ?: $default['image'],
        );
    }

    return apply_filters('pbv2_slider_slides', $slides);
}

add_action('customize_register', function ($customizer) {
    $customizer->add_section('pbv2_slider', array(
        'title'       => 'Banner principal',
        'description' => 'Edita el banner desde WordPress. Activa más diapositivas únicamente cuando tengan imágenes diferentes.',
        'priority'    => 35,
    ));

    foreach (pbv2_slider_defaults() as $index => $default) {
        $n = $index + 1;

        $customizer->add_setting("pbv2_slide_{$n}_enabled", array(
            'default'           => $default['enabled'],
            'sanitize_callback' => 'wp_validate_boolean',
        ));
        $customizer->add_control("pbv2_slide_{$n}_enabled", array(
            'label'   => "Diapositiva {$n}: mostrar",
            'section' => 'pbv2_slider',
            'type'    => 'checkbox',
        ));

        $customizer->add_setting("pbv2_slide_{$n}_image", array(
            'default'           => $default['image'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $customizer->add_control(new WP_Customize_Image_Control(
            $customizer,
            "pbv2_slide_{$n}_image",
            array(
                'label'   => "Diapositiva {$n}: imagen",
                'section' => 'pbv2_slider',
            )
        ));

        foreach (array('eyebrow' => 'Antetítulo', 'title' => 'Título') as $key => $label) {
            $customizer->add_setting("pbv2_slide_{$n}_{$key}", array(
                'default'           => $default[$key],
                'sanitize_callback' => 'sanitize_text_field',
            ));
            $customizer->add_control("pbv2_slide_{$n}_{$key}", array(
                'label'   => "Diapositiva {$n}: {$label}",
                'section' => 'pbv2_slider',
                'type'    => 'text',
            ));
        }

        $customizer->add_setting("pbv2_slide_{$n}_text", array(
            'default'           => $default['text'],
            'sanitize_callback' => 'sanitize_textarea_field',
        ));
        $customizer->add_control("pbv2_slide_{$n}_text", array(
            'label'   => "Diapositiva {$n}: descripción",
            'section' => 'pbv2_slider',
            'type'    => 'textarea',
        ));
    }
});

add_action('wp_enqueue_scripts', function () {
    $css = __DIR__ . '/pinabombas-slider-v2.css';
    wp_enqueue_style(
        'pinabombas-slider-v2',
        plugins_url('pinabombas-slider-v2.css', __FILE__),
        array('pinabombas-frontend'),
        file_exists($css) ? (string) filemtime($css) : '2.1.0'
    );

    if (is_front_page()) {
        $js = __DIR__ . '/pinabombas-slider-v2.js';
        wp_enqueue_script(
            'pinabombas-slider-v2',
            plugins_url('pinabombas-slider-v2.js', __FILE__),
            array(),
            file_exists($js) ? (string) filemtime($js) : '2.1.0',
            true
        );
    }
}, 80);

add_action('storefront_before_content', function () {
    if (!is_front_page()) return;

    $slides = pbv2_get_slides();
    if (!$slides) return;

    $shop = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');

    $whatsapp = function_exists('pb_wa_link')
        ? pb_wa_link(null, 'Hola, quiero cotizar una bomba o piña de combustible. Tengo marca, modelo, año y referencia del vehículo.')
        : '#';
    ?>
    <section class="pbv2-slider" data-pbv2-slider data-delay="7000" aria-roledescription="carousel" aria-label="Servicios destacados de Piña Bombas">
        <div class="pbv2-slider__viewport">
            <?php foreach ($slides as $index => $slide) : $active = $index === 0; ?>
                <article class="pbv2-slider__slide pbv2-slider__slide--<?php echo esc_attr((string) ($index + 1)); ?><?php echo $active ? ' is-active' : ''; ?>" data-pbv2-slide aria-hidden="<?php echo $active ? 'false' : 'true'; ?>">
                    <img
                        class="pbv2-slider__image"
                        src="<?php echo esc_url($slide['image']); ?>"
                        alt="<?php echo esc_attr($slide['title']); ?>"
                        loading="<?php echo $active ? 'eager' : 'lazy'; ?>"
                        decoding="async"
                        <?php echo $active ? 'fetchpriority="high"' : ''; ?>
                    >
                    <span class="pbv2-slider__overlay" aria-hidden="true"></span>

                    <div class="pbv2-slider__content">
                        <div class="pbv2-slider__copy">
                            <p class="pb-eyebrow"><span aria-hidden="true"></span><?php echo esc_html($slide['eyebrow']); ?></p>

                            <?php if ($active) : ?><h1><?php else : ?><h2><?php endif; ?>
                                <?php echo esc_html($slide['title']); ?>
                            <?php if ($active) : ?></h1><?php else : ?></h2><?php endif; ?>

                            <p class="pbv2-slider__description"><?php echo esc_html($slide['text']); ?></p>

                            <div class="pbv2-slider__actions">
                                <a class="button pbv2-slider__primary" href="<?php echo esc_url($shop); ?>">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 9.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 7H7M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm8 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                                    <span>Ver catálogo</span>
                                </a>
                                <a class="button pbv2-slider__secondary" href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="nofollow noopener">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4A8 8 0 1 1 20 11.5Z"/><path d="M9 9.2c.6 2 1.8 3.2 3.8 3.8"/></svg>
                                    <span>Cotizar por WhatsApp</span>
                                </a>
                            </div>

                            <ul class="pbv2-slider__trust" aria-label="Ventajas principales">
                                <li>Compatibilidad verificada</li>
                                <li>Asesoría técnica</li>
                                <li>Stock multimarca</li>
                            </ul>
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

        <div class="pbv2-services" aria-label="Servicios principales">
            <div class="pbv2-services__inner">
                <article>
                    <span class="pbv2-services__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.8 2.7 8 7 10 4.3-2 7-5.2 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <div><strong>01. Venta de bombas</strong><p>Bombas completas, piñas y repuestos según marca y modelo.</p></div>
                </article>
                <article>
                    <span class="pbv2-services__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v7"/><path d="M15 3v4h4M6 3v18h7"/><circle cx="17" cy="17" r="3"/><path d="m19.2 19.2 2 2"/></svg>
                    </span>
                    <div><strong>02. Diagnóstico técnico</strong><p>Revisión de síntomas, presión, conectores y compatibilidad.</p></div>
                </article>
                <article>
                    <span class="pbv2-services__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m14 7 3-3 3 3-3 3M4 20l7-7M8 4l12 12M4 8l4-4"/><path d="m14 17 3 3 3-3-3-3"/></svg>
                    </span>
                    <div><strong>03. Reparación</strong><p>Soluciones con respaldo para sistemas de combustible.</p></div>
                </article>
            </div>
        </div>
    </section>
    <?php
}, 3);
