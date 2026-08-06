#!/bin/bash
# Pulido integral de la tienda Piña Bombas de Combustible
cd /var/www/html
export WP_CLI_PHP_ARGS='-d error_reporting=0'
WP(){ wp "$@" 2>/dev/null; }

echo ">> 1) Limpiar contenido demo..."
for slug in sample-page hello-world; do
  ID=$(WP post list --post_type=any --name="$slug" --field=ID | head -1)
  [ -n "$ID" ] && WP post delete "$ID" --force >/dev/null 2>&1 && echo "   borrado: $slug ($ID)"
done

echo ">> 2) Favicon (site icon) con el logo..."
LOGO_ID=$(WP theme mod get custom_logo 2>/dev/null | tr -d '\r')
[ -z "$LOGO_ID" ] && LOGO_ID=80
WP option update site_icon "$LOGO_ID" >/dev/null 2>&1 && echo "   site_icon=$LOGO_ID"

echo ">> 3) Categorías por marca de vehículo..."
declare -A BRAND
brand_of(){
  local t="$1"
  case "$t" in
    *Cherry*) echo "Chery";;
    *Chevrolet*|*Corsa*|*Sail*|*Spark*|*Grand\ Vitara*) echo "Chevrolet";;
    *Daewoo*) echo "Daewoo";;
    *Fiat*) echo "Fiat";;
    *Ford*) echo "Ford";;
    *Hyundai*|*Hyumdai*|*Grand\ i10*|*Getz*|*Accent*|*Elantra*|*Matrix*|*Tucson*|*i10*) echo "Hyundai";;
    *Kia*) echo "Kia";;
    *Mazda*) echo "Mazda";;
    *Nissan*|*Tiida*) echo "Nissan";;
    *Peugeot*) echo "Peugeot";;
    *Renault*|*Logan*|*Duster*|*Megane*) echo "Renault";;
    *Suzuky*|*Suzuki*|*Jimny*) echo "Suzuki";;
    *Toyota*|*Hilux*|*Yaris*) echo "Toyota";;
    *) echo "Otros";;
  esac
}
declare -A TERMCACHE
get_term(){
  local name="$1"
  if [ -n "${TERMCACHE[$name]}" ]; then echo "${TERMCACHE[$name]}"; return; fi
  local id=$(WP term list product_cat --name="$name" --field=term_id | head -1)
  [ -z "$id" ] && id=$(WP term create product_cat "$name" --porcelain)
  TERMCACHE[$name]="$id"; echo "$id"
}
n=0
while IFS=$'\t' read -r PID TITLE; do
  [ -z "$PID" ] && continue
  b=$(brand_of "$TITLE")
  tid=$(get_term "$b")
  [ -n "$tid" ] && WP post term add "$PID" product_cat "$tid" >/dev/null 2>&1
  n=$((n+1))
done < <(WP post list --post_type=product --fields=ID,post_title --posts_per_page=100 --format=csv | tail -n +2 | sed 's/,/\t/')
echo "   productos categorizados: $n"

echo ">> 4) Footer widgets (contacto)..."
for w in $(WP widget list footer-1 --format=ids 2>/dev/null); do WP widget delete "$w" >/dev/null 2>&1; done
WP widget add block footer-1 --content='<!-- wp:heading {"level":3} --><h3>Piña Bombas de Combustible</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Venta y Reparación de bombas y piñas de combustible. Empresa ecuatoriana con stock y atención personalizada.</p><!-- /wp:paragraph -->' >/dev/null 2>&1
WP widget add block footer-2 --content='<!-- wp:heading {"level":3} --><h3>Contacto</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Jorge Piña R. (Ing. Eléctrico)<br>info@pinabombasdecombustible.com</p><!-- /wp:paragraph -->' >/dev/null 2>&1
echo "   footer configurado"

echo ">> 5) CSS de refinamiento + ocultar título de portada..."
FRONT=$(WP option get page_on_front | tr -d '\r')
CSS='/* Piña Bombas - pulido */
.home .entry-title, .page-id-'"$FRONT"' .entry-title, .home .page-title{display:none!important}
.home .content-area{padding-top:0}
.site-header{border-bottom:3px solid #f71635}
ul.products li.product .button{background:#f71635;color:#fff;border-radius:4px}
ul.products li.product .button:hover{background:#c9102b}
ul.products li.product{transition:transform .15s ease, box-shadow .15s ease;padding:10px;border-radius:8px}
ul.products li.product:hover{transform:translateY(-4px);box-shadow:0 8px 22px rgba(2,24,39,.12)}
ul.products li.product img{border-radius:6px}
.precio-consultar{color:#f71635;font-weight:600}
.storefront-breadcrumb{opacity:.8}
h1,h2{letter-spacing:.3px}
.site-footer{border-top:3px solid #f71635}
.woocommerce-store-notice{background:#021827}'
WP eval 'wp_update_custom_css_post($argv[1]);' "$CSS" >/dev/null 2>&1 && echo "   CSS aplicado"

echo ">> 6) Ajustes de catálogo..."
WP option update woocommerce_catalog_columns 4 >/dev/null 2>&1
WP option update woocommerce_catalog_rows 3 >/dev/null 2>&1
WP option update posts_per_page 12 >/dev/null 2>&1
WP wc --user=admin tool run regenerate_product_lookup_tables >/dev/null 2>&1

echo ">> 7) Menú: añadir Blog y (Categorías) si aplica..."
# nada extra por ahora

echo ">> PULIDO COMPLETO."
echo "   Categorías creadas:"; WP term list product_cat --fields=name,count --format=csv | tail -n +2