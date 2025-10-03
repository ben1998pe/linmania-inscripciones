=== Linmania Blog e Inscripciones ===
Contributors: benjaminoscco
Tags: woocommerce, inscriptions, tournaments, sports, admin
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin para gestionar inscripciones de torneos y eventos deportivos con integración completa a WooCommerce.

== Description ==

**Linmania Blog e Inscripciones** es un plugin especializado para la gestión de inscripciones de torneos y eventos deportivos. Desarrollado por Benjamin Oscco Arias, este plugin se integra perfectamente con WooCommerce para manejar las inscripciones como órdenes de compra.

### Características Principales

* **Gestión de Inscripciones**: Tabla administrativa completa para visualizar todas las inscripciones
* **Integración con WooCommerce**: Utiliza las órdenes de WooCommerce como base para las inscripciones
* **Campos Personalizados**: Manejo de información de jugadores (Jugador 1, Jugador 2, Jugador 3, Jugador 4, Suplentes)
* **Filtros Avanzados**: Filtrado por categorías, local, equipo y búsqueda general
* **Ordenamiento**: Ordenamiento por cualquier columna (ID, Fecha, Categorías, Local, Equipo)
* **Paginación**: Sistema de paginación completo con control de registros por página
* **Exportación**: Exportación a Excel y PDF con todos los datos
* **Interfaz Responsive**: Diseño adaptativo para diferentes dispositivos

### Campos Personalizados Requeridos

El plugin espera los siguientes campos personalizados en las órdenes de WooCommerce:

* `categoria_torneo` - Categoría del torneo (ej: BULLSHOOTER, CONNECTION)
* `local_torneo` - Local donde se realiza el torneo
* `nombre_equipo` - Nombre del equipo
* `jugador_1` - Nombre del primer jugador
* `jugador_2` - Nombre del segundo jugador
* `jugador_3` - Nombre del tercer jugador
* `jugador_4` - Nombre del cuarto jugador
* `suplentes` - Información de jugadores suplentes

### Requisitos

* WordPress 5.0 o superior
* WooCommerce activo
* PHP 7.4 o superior

== Installation ==

1. Sube el archivo del plugin a la carpeta `/wp-content/plugins/linmania-blog-inscripciones/`
2. Activa el plugin desde el panel de administración de WordPress
3. Asegúrate de que WooCommerce esté instalado y activado
4. Ve a "Inscripciones" en el menú de administración para comenzar a usar el plugin

== Frequently Asked Questions ==

= ¿El plugin funciona sin WooCommerce? =

No, este plugin requiere WooCommerce para funcionar correctamente ya que utiliza las órdenes de WooCommerce como base para las inscripciones.

= ¿Cómo se configuran los campos personalizados? =

Los campos personalizados deben ser agregados a las órdenes de WooCommerce a través de otros plugins o código personalizado. El plugin simplemente lee estos campos existentes.

= ¿Puedo exportar los datos? =

Sí, el plugin incluye funcionalidad de exportación tanto a Excel como a PDF.

== Screenshots ==

1. Vista principal de la tabla de inscripciones
2. Filtros y controles de búsqueda
3. Detalles expandidos de jugadores
4. Opciones de exportación

== Changelog ==

= 1.0.0 =
* Lanzamiento inicial
* Tabla administrativa completa
* Integración con WooCommerce
* Sistema de filtros y búsqueda
* Exportación a Excel y PDF
* Interfaz responsive

== Upgrade Notice ==

= 1.0.0 =
Primera versión del plugin. Instala para comenzar a gestionar inscripciones de torneos.

== Developer Information ==

**Desarrollado por**: Benjamin Oscco Arias
**Sitio web**: https://linmania.com
**Versión**: 1.0.0
**Licencia**: GPL v2 o superior


