#!/bin/bash
# Aplica identidad y contenido real (recuperado del Wayback) a la tienda Storefront.
cd /var/www/html
export WP_CLI_PHP_ARGS='-d error_reporting=0'

echo ">> Tagline / identidad..."
wp option update blogname "Piña Bombas de Combustible" >/dev/null 2>&1
wp option update blogdescription "Venta y Reparación" >/dev/null 2>&1

echo ">> Colores de marca (Storefront)..."
wp theme mod set storefront_header_background_color "#021827" >/dev/null 2>&1
wp theme mod set storefront_header_text_color "#ffffff" >/dev/null 2>&1
wp theme mod set storefront_header_link_color "#ffffff" >/dev/null 2>&1
wp theme mod set storefront_footer_background_color "#021827" >/dev/null 2>&1
wp theme mod set storefront_footer_heading_color "#ffffff" >/dev/null 2>&1
wp theme mod set storefront_footer_text_color "#e8e8e8" >/dev/null 2>&1
wp theme mod set storefront_button_background_color "#f71635" >/dev/null 2>&1
wp theme mod set storefront_button_text_color "#ffffff" >/dev/null 2>&1
wp theme mod set storefront_button_alt_background_color "#f71635" >/dev/null 2>&1
wp theme mod set storefront_button_alt_text_color "#ffffff" >/dev/null 2>&1
wp theme mod set storefront_accent_color "#f71635" >/dev/null 2>&1

get_page () { wp post list --post_type=page --pagename="$1" --field=ID 2>/dev/null | head -1; }

echo ">> Página NOSOTROS..."
NOS=$(get_page nosotros)
if [ -z "$NOS" ]; then
  NOS=$(wp post create --post_type=page --post_status=publish --post_title="Nosotros" --post_name="nosotros" \
    --post_content="<h2>Nosotros</h2><p>Somos una empresa ecuatoriana, trabajamos con calidad. Contamos con stock y atención personalizada.</p><p>Venta y Reparación de bombas y piñas de combustible. Jorge Piña R. (Ing. Eléctrico).</p>" \
    --porcelain 2>/dev/null)
fi

echo ">> Página CONTACTO..."
CON=$(get_page contacto)
if [ -z "$CON" ]; then
  CON=$(wp post create --post_type=page --post_status=publish --post_title="Contacto" --post_name="contacto" \
    --post_content="<h2>Contacto</h2><p>Escríbenos: <a href='mailto:info@pinabombasdecombustible.com'>info@pinabombasdecombustible.com</a></p><p>Empresa ecuatoriana — atención personalizada.</p>" \
    --porcelain 2>/dev/null)
fi

echo ">> Entradas de blog (Bienvenidos / Bombas)..."
if [ -z "$(wp post list --post_type=post --pagename=bienvenidos --field=ID 2>/dev/null)" ]; then
  wp post create --post_type=post --post_status=publish --post_title="Bienvenidos" --post_name="bienvenidos" \
    --post_date="2022-03-02 09:00:00" --post_content="<p>Bienvenidos a Piña Bombas de Combustible.</p>" >/dev/null 2>&1
fi
wp post create --post_type=post --post_status=publish --post_title="Bombas" --post_name="bombas" \
  --post_date="2022-03-04 09:00:00" --post_content="<p>Reparamos y vendemos.</p>" >/dev/null 2>&1

echo ">> Menú principal (INICIO / PRODUCTOS / NOSOTROS / CONTACTO)..."
SHOP=$(wp option get woocommerce_shop_page_id 2>/dev/null | tr -d '\r')
HOME=$(wp option get page_on_front 2>/dev/null | tr -d '\r')
wp menu delete "Principal" >/dev/null 2>&1
MENU=$(wp menu create "Principal" --porcelain 2>/dev/null)
[ -n "$HOME" ] && wp menu item add-post "$MENU" "$HOME" --title="INICIO" >/dev/null 2>&1
[ -n "$SHOP" ] && wp menu item add-post "$MENU" "$SHOP" --title="PRODUCTOS" >/dev/null 2>&1
[ -n "$NOS" ] && wp menu item add-post "$MENU" "$NOS" --title="NOSOTROS" >/dev/null 2>&1
[ -n "$CON" ] && wp menu item add-post "$MENU" "$CON" --title="CONTACTO" >/dev/null 2>&1
wp menu location assign "$MENU" primary >/dev/null 2>&1

echo ">> LISTO."
echo "   Nosotros=$NOS  Contacto=$CON  Menu=$MENU  Shop=$SHOP  Home=$HOME"
