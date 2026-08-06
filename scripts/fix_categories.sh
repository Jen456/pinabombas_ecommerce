#!/bin/bash
cd /var/www/html
export WP_CLI_PHP_ARGS='-d error_reporting=0'
WP(){ wp "$@" 2>/dev/null; }

echo ">> mu-plugin cargado?"
WP eval 'echo has_filter("woocommerce_get_price_html") ? "MU-OK\n" : "MU-NO\n";'

echo ">> Borrando categorías basura (nombre numérico)..."
for tid in $(WP term list product_cat --field=term_id); do
  name=$(WP term get product_cat "$tid" --field=name 2>/dev/null | tr -d '\r')
  if [[ "$name" =~ ^[0-9]+$ ]]; then WP term delete product_cat "$tid" >/dev/null 2>&1; echo "   borrada: '$name' ($tid)"; fi
done

echo ">> IDs de categorías buenas..."
term_id(){ WP term list product_cat --name="$1" --field=term_id 2>/dev/null | head -1; }
ensure(){ local id=$(term_id "$1"); [ -z "$id" ] && id=$(WP term create product_cat "$1" --porcelain 2>/dev/null); echo "$id"; }
BOMBAS=$(ensure "Bombas de combustible")
echo "   Bombas de combustible = $BOMBAS"

brand_of(){
  case "$1" in
    *Cherry*) echo "Chery";;
    *Chevrolet*|*Corsa*|*Sail*|*Spark*|*Grand\ Vitara*) echo "Chevrolet";;
    *Daewoo*) echo "Daewoo";; *Fiat*) echo "Fiat";; *Ford*) echo "Ford";;
    *Hyundai*|*Hyumdai*|*Grand\ i10*|*Getz*|*Accent*|*Elantra*|*Matrix*|*Tucson*|*i10*) echo "Hyundai";;
    *Kia*) echo "Kia";; *Mazda*) echo "Mazda";; *Nissan*|*Tiida*) echo "Nissan";;
    *Peugeot*) echo "Peugeot";; *Renault*|*Logan*|*Duster*|*Megane*) echo "Renault";;
    *Suzuky*|*Suzuki*|*Jimny*) echo "Suzuki";; *Toyota*|*Hilux*|*Yaris*) echo "Toyota";;
    *) echo "Otros";;
  esac
}
declare -A BID
n=0
while IFS=$'\t' read -r PID TITLE; do
  [ -z "$PID" ] && continue
  b=$(brand_of "$TITLE")
  [ -z "${BID[$b]}" ] && BID[$b]=$(ensure "$b")
  WP wc product update "$PID" --categories="[{\"id\":$BOMBAS},{\"id\":${BID[$b]}}]" --user=admin >/dev/null 2>&1
  n=$((n+1))
done < <(WP post list --post_type=product --fields=ID,post_title --posts_per_page=100 --format=csv | tail -n +2 | sed 's/,/\t/')
echo "   productos re-categorizados: $n"

WP term recount product_cat >/dev/null 2>&1
echo ">> Categorías finales (name,count):"
WP term list product_cat --fields=name,count --format=csv 2>/dev/null | tail -n +2 | sort
