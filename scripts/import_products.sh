#!/bin/bash
# Importa las imagenes de /material como productos WooCommerce.
# Se ejecuta DENTRO del contenedor wpcli.
cd /var/www/html
export WP_CLI_PHP_ARGS='-d error_reporting=0'

IMG_DIR="${1:-/var/www/html/_import}"

echo ">> Configurando opciones de tienda..."
wp option update woocommerce_store_address "Ecuador" >/dev/null 2>&1
wp option update woocommerce_default_country "EC" >/dev/null 2>&1
wp option update woocommerce_currency "USD" >/dev/null 2>&1
wp option update woocommerce_weight_unit "kg" >/dev/null 2>&1
wp option update woocommerce_dimension_unit "cm" >/dev/null 2>&1
# Marca el asistente de WooCommerce como completado
wp option update woocommerce_onboarding_profile '{"completed":true,"skipped":true}' --format=json >/dev/null 2>&1

echo ">> Creando categoria de producto..."
CAT_ID=$(wp term create product_cat "Bombas de combustible" --porcelain 2>/dev/null)
if [ -z "$CAT_ID" ]; then
  CAT_ID=$(wp term list product_cat --name="Bombas de combustible" --field=term_id 2>/dev/null | head -1)
fi
echo "   Categoria ID = $CAT_ID"

echo ">> Creando productos desde imagenes en: $IMG_DIR"
n=0
shopt -s nullglob
for f in "$IMG_DIR"/*.png "$IMG_DIR"/*.jpg; do
  [ -e "$f" ] || continue
  base="$(basename "$f")"
  name="${base%.*}"
  # importar la imagen a la biblioteca de medios
  ATT=$(wp media import "$f" --porcelain 2>/dev/null)
  # crear el producto (sin precio: pendiente de definir)
  PID=$(wp wc product create \
        --name="$name" \
        --type=simple \
        --status=publish \
        --catalog_visibility=visible \
        --description="$name. Repuesto original. Precio pendiente por confirmar." \
        --short_description="$name" \
        --manage_stock=false \
        --user=admin --porcelain 2>/dev/null)
  if [ -n "$PID" ]; then
    [ -n "$ATT" ] && wp post meta update "$PID" _thumbnail_id "$ATT" >/dev/null 2>&1
    [ -n "$CAT_ID" ] && wp post term set "$PID" product_cat "$CAT_ID" >/dev/null 2>&1
    n=$((n+1))
    echo "   [$n] OK  producto=$PID  img=$ATT  -> $name"
  else
    echo "   ERROR creando producto para: $name"
  fi
done

echo ">> TERMINADO. Productos creados: $n"
echo ">> Total productos en la tienda:"
wp post list --post_type=product --format=count 2>/dev/null
