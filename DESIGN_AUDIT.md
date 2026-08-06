# Auditoría de diseño — Piña Bombas e-commerce

Fecha: 2026-08-06  
Alcance: WordPress + WooCommerce + Storefront + mu-plugins propios.

## Resumen ejecutivo

El proyecto tiene una base técnica correcta para un catálogo de cotización por WhatsApp: WooCommerce administra productos y la personalización está concentrada en mu-plugins. El principal problema era visual y estructural: el inicio dependía de un hero fijo, el CSS conservaba varias versiones superpuestas del banner y el contenido no podía administrarse fácilmente desde WordPress.

La prioridad es convertir el inicio en una vitrina comercial clara: propuesta de valor, búsqueda, catálogo, compatibilidad y contacto técnico. El diseño debe verse automotriz y profesional, no como una plantilla genérica cargada de efectos.

## Cambios incluidos en esta rama

- Slider ligero de tres diapositivas, sin Elementor ni librerías externas.
- Edición de imagen, antetítulo, título y descripción desde `Apariencia > Personalizar > Slider principal`.
- Autoplay de siete segundos con pausa manual.
- Flechas, indicadores, navegación por teclado y gesto táctil.
- Pausa al enfocar o pasar el cursor.
- Respeto a `prefers-reduced-motion`.
- Buscador de productos bajo el slider.
- Bloques de confianza: compatibilidad, diagnóstico y cotización.
- Cabecera sticky en escritorio y navegación con estado activo.
- Tarjetas de producto más modernas, títulos limitados a dos líneas y dos columnas en móviles medianos.
- Resumen de producto sticky en escritorio.

## Hallazgo crítico de seguridad

La web pública antigua contiene contenido de casino/spam indexado. No se debe desplegar este rediseño encima de esa instalación sin sanearla.

Antes del despliegue:

1. Crear respaldo forense de archivos y base de datos.
2. Revisar administradores, tareas cron, plugins, temas, `wp-config.php`, `.htaccess`, mu-plugins, uploads con PHP y contenido inyectado.
3. Reinstalar WordPress core desde una fuente limpia.
4. Rotar credenciales, salts, FTP/SSH, base de datos y panel.
5. Eliminar URLs de spam, devolver 410 cuando corresponda y solicitar nueva indexación.
6. Desplegar el proyecto en una instalación limpia.

## Prioridades siguientes

### Inicio

Construir una página completa y controlada con este orden:

1. Slider principal.
2. Marcas o categorías más buscadas.
3. Productos recientes o destacados.
4. Proceso de cotización en tres pasos.
5. Servicios de diagnóstico y reparación.
6. Evidencia de confianza: taller, técnico responsable, garantía y ubicación.
7. CTA final por WhatsApp.

No conviene seguir mezclando contenido heredado del editor con secciones inyectadas por hooks.

### Cabecera

- Configurar teléfono, dirección y WhatsApp reales desde administración.
- Revisar calidad y proporción del logo.
- Crear menú móvil lateral con categorías principales.

### Catálogo

- Añadir atributos reales: marca, modelo, año, motor, presión, flujo, conector y referencia OEM.
- Implementar filtros por vehículo, no solo por categoría.
- Mostrar SKU o referencia en la tarjeta.
- Separar productos disponibles, bajo pedido y servicios de reparación.
- Mostrar el filtro de marca activo.

### Ficha de producto

- Galería con fotos reales y referencia visible.
- Estado de disponibilidad administrable.
- CTA para enviar foto de la pieza.
- Tabla de vehículos compatibles.
- Evitar schema Product duplicado si WooCommerce o un plugin SEO ya lo genera.

### Rendimiento y accesibilidad

- Convertir imágenes a WebP o AVIF.
- Mantener contraste AA y foco visible.
- Verificar el slider con teclado y lector de pantalla.
- Probar a 320, 360, 768, 1024 y 1440 px.
- Medir Core Web Vitals antes de publicar.

## Riesgos técnicos

- `PB_WHATSAPP` todavía contiene un número placeholder en el repositorio.
- La base de datos no está versionada, por lo que el render final depende de contenido local.
- El CSS principal conserva reglas históricas del hero. La nueva capa las reemplaza visualmente, pero conviene eliminarlas después de aprobar el rediseño.

## Criterios de aceptación

- Slider editable y funcional en navegadores modernos.
- Navegación por teclado y botón de pausa verificados.
- Sin scroll horizontal a 320 px.
- WhatsApp real configurado y probado.
- Imágenes optimizadas.
- Instalación WordPress limpia.
- URLs de spam eliminadas y monitoreo posterior activado.
