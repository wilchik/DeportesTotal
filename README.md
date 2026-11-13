# Deportes Total

Deportes Total es un sistema web para administrar una tienda de ropa y artículos deportivos. Esta carpeta es el paquete de entrega para instalar localmente con XAMPP. Incluye el código existente de CodeIgniter 3, sus recursos, migraciones SQL y una base anonimizada de demostración.

## Tecnologías

- PHP 7.4 o la versión compatible incluida en la instalación de XAMPP utilizada por el proyecto.
- CodeIgniter 3 (framework incluido en `system/`).
- MySQL o MariaDB, con el controlador PHP `mysqli` habilitado.
- HTML, CSS y JavaScript sin dependencias de instalación con npm.
- Apache de XAMPP.

## Módulos

- Inicio de sesión, administración de usuarios y permisos por rol.
- Panel de análisis del propietario y reportes por período, canal, producto y cliente.
- Catálogo de productos con referencia, talla, precio, costo de adquisición y existencias.
- Inventario y movimientos relacionados con compras y ventas.
- Clientes y proveedores.
- Compras y detalle de compras.
- Punto de venta con búsqueda de productos y clientes, carrito, selección de canal y pago.
- Apertura y cierre de caja, movimientos de efectivo e historial de cierres.

## Roles

| Rol | Acceso principal |
| --- | --- |
| Propietario | Análisis, reportes consolidados, costos e inventario valorizado, cierres de caja y gestión de usuarios. |
| Administrador | Operación de productos, costos, clientes, proveedores, compras, ventas, inventario y su caja. No administra usuarios ni ve los indicadores globales reservados al propietario. |
| Caja | Punto de venta, clientes y operación de su propia caja. No puede ver ni cambiar costos ni consultar ganancias globales. |

## Requisitos

- XAMPP con Apache, MySQL/MariaDB y PHP `mysqli`.
- PHP 7.4.33 fue usado para validar este paquete. Mantenga la versión de PHP configurada en la instalación académica compatible con CodeIgniter 3.
- Extensión `mysqli` habilitada.
- Puerto local de MySQL disponible (por defecto, 3306).
- Espacio para importar la base incluida.

## Instalación en XAMPP

1. Copie esta carpeta dentro de `C:\xamppp\htdocs` y cámbiele el nombre a `DeportesTotal`. La ruta resultante debe ser `C:\xamppp\htdocs\DeportesTotal`.
2. Inicie Apache y MySQL desde el panel de XAMPP.
3. En phpMyAdmin, cree una base de datos llamada `deportes_total` con cotejamiento `utf8mb4_unicode_ci`.
4. Configure los archivos locales:
   - Copie `application/config/database.php.example` como `application/config/database.php` y ajuste host, usuario, contraseña y nombre de base. El ejemplo usa `root` sin contraseña, valor habitual solo en instalaciones locales nuevas de XAMPP.
   - Copie `application/config/config.php.example` como `application/config/config.php`. Cambie `base_url` si utiliza otra carpeta y reemplace `encryption_key` por una clave aleatoria propia. Genere una con `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"` desde la consola de XAMPP.
5. Importe `database/seeds/deportes_total_demo.sql` desde phpMyAdmin o con el cliente MySQL. El archivo incluye `CREATE DATABASE` y `USE deportes_total`.
6. Abra `http://localhost/DeportesTotal/`.

> **Atención:** el SQL demo recrea las tablas del esquema. Impórtelo únicamente en una base vacía o descartable; no lo ejecute sobre una base con información que quiera conservar.

## Cuentas ficticias de demostración

Las cuentas y los datos personales del SQL se sustituyeron por valores ficticios. Las tres cuentas principales comparten esta clave:

| Rol | Usuario | Contraseña |
| --- | --- | --- |
| Propietario | `propietario_demo` | `DeportesDemo2026!` |
| Administrador | `administrador_demo` | `DeportesDemo2026!` |
| Caja | `caja_demo` | `DeportesDemo2026!` |

El cajero debe abrir su propia caja antes de registrar ventas. La base demo conserva la estructura y las relaciones comerciales del corte exportado; contiene también una sesión abierta de una cuenta administrativa auxiliar. Las contraseñas son exclusivas de esta base de demostración y deben cambiarse si se reutiliza fuera de una presentación local.

## Migraciones

Los archivos de `database/migrations/` describen los cambios de esquema del proyecto y sus scripts de reversión cuando existen. El SQL demo ya incluye la estructura resultante, por lo que no es necesario aplicar estas migraciones al importarlo.

## Protección de datos

Los archivos locales `application/config/database.php` y `application/config/config.php` se excluyen de Git. No publique respaldos privados, claves de cifrado, contraseñas reales, sesiones ni datos personales. Los valores ficticios del seed solo sirven para demostración académica.

