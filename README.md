# Pina Bombas de Combustible - Proyecto e-commerce (WordPress + WooCommerce)

Tienda de venta y reparacion de bombas y pinas de combustible (Ecuador). Proyecto preparado para abrir en VS Code y levantar con Docker.

## Estructura
```text
pinabombas_ecommerce/
├─ docker-compose.yml        # levanta WordPress + MariaDB
├─ db/                       # dumps locales no versionados
├─ wordpress/                # sitio WordPress montado en Docker
│  └─ wp-content/
│     ├─ themes/storefront/  # tema activo
│     ├─ mu-plugins/         # ajustes propios del proyecto
│     └─ uploads/            # imagenes de producto y logo
├─ scripts/                  # scripts de aprovisionamiento
├─ .vscode/                  # settings + extensiones recomendadas
└─ pinabombas.code-workspace
```

## Abrir en VS Code
1. Abre `pinabombas.code-workspace` o la carpeta del proyecto.
2. Acepta instalar las extensiones recomendadas.
3. Codigo propio principal:
   - `wordpress/wp-content/mu-plugins/pinabombas-tweaks.php`
   - `wordpress/wp-content/mu-plugins/pinabombas-frontend.php`
   - `wordpress/wp-content/mu-plugins/pinabombas-frontend.css`

## Levantar el sitio local
Copia `.env.example` a `.env` y ajusta los valores locales antes de iniciar Docker.

```bash
cp .env.example .env
docker compose up -d
# Tienda: http://localhost:8080/shop/
docker compose down
docker compose down -v
```

Si ya tienes otro WordPress usando el puerto 8080, usa el override local incluido:

```bash
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d
# Tienda: http://localhost:8081/shop/
```

## Base de datos
El dump SQL se mantiene como archivo local y no se versiona en GitHub por seguridad. Si clonas este repositorio en otra maquina, copia manualmente tu dump a `db/pinabombas.sql` antes de levantar Docker por primera vez.

## Accesos locales
No se publican credenciales en el repositorio. Usa las credenciales configuradas en tu entorno local o restablecelas desde WordPress/WP-CLI.

## WP-CLI
```bash
docker compose run --rm wpcli plugin list
docker compose run --rm wpcli post list --post_type=product
```

## Configuracion pendiente
En `wordpress/wp-content/mu-plugins/pinabombas-tweaks.php`, cambia el numero de WhatsApp:

```php
define('PB_WHATSAPP', '593XXXXXXXXX');
```

## Notas
- Modelo actual: cotizacion por WhatsApp.
- Productos sin precio muestran "Precio: a consultar".
- El frontend principal esta personalizado desde `pinabombas-frontend.php` y `pinabombas-frontend.css`.
