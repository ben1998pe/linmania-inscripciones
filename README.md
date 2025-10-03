# Linmania Blog e Inscripciones

Plugin de WordPress para gestionar inscripciones de torneos y eventos deportivos con integración a WooCommerce.

## Descripción

Este plugin permite:
- Visualizar inscripciones de torneos en una tabla administrativa
- Filtrar inscripciones por categoría, local y equipo
- Ver detalles de jugadores en modal popup
- Exportar datos a Excel y PDF
- Integración completa con WooCommerce

## Características

- ✅ **Tabla de inscripciones** - Muestra datos de órdenes de WooCommerce
- ✅ **Filtros dinámicos** - Por categoría de producto, local y equipo
- ✅ **Modal de jugadores** - Detalles de hasta 4 jugadores + suplentes
- ✅ **Exportación** - Excel y PDF con todos los datos
- ✅ **Responsive** - Diseño adaptable a diferentes pantallas
- ✅ **Integración WooCommerce** - Usa datos reales de órdenes

## Instalación

1. Sube la carpeta `linmania-blog-inscripciones` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el panel de administración de WordPress
3. Asegúrate de que WooCommerce esté instalado y activado
4. Ve a **Inscripciones** en el menú de administración

## Requisitos

- WordPress 5.0 o superior
- WooCommerce 3.0 o superior
- PHP 7.4 o superior

## Uso

### Visualizar Inscripciones
1. Ve a **Inscripciones** en el menú de administración
2. La tabla mostrará todas las inscripciones de WooCommerce
3. Usa los filtros para encontrar inscripciones específicas

### Filtrar Datos
- **Categoría**: Filtra por categoría del producto comprado
- **Local**: Filtra por local del equipo
- **Equipo**: Filtra por nombre del equipo
- **Búsqueda**: Búsqueda general en local y equipo

### Ver Detalles de Jugadores
1. Haz clic en el botón **+** en cualquier fila
2. Se abrirá un modal con los detalles de los jugadores
3. Incluye nombres, teléfonos y suplentes

### Exportar Datos
1. Usa los filtros para seleccionar los datos deseados
2. Haz clic en **Exportar Excel** o **Exportar PDF**
3. El archivo se descargará automáticamente

## Campos Personalizados Requeridos

El plugin utiliza los siguientes campos personalizados en WooCommerce:

- `nombre_equipo` - Nombre del equipo
- `local` - Local del equipo
- `nombre_jugador_1` a `nombre_jugador_4` - Nombres de jugadores
- `telefono_jugador_1` a `telefono_jugador_4` - Teléfonos de jugadores
- `suplentes` - Lista de suplentes

## Estructura del Plugin

```
linmania-blog-inscripciones/
├── linmania-blog-inscripciones.php    # Archivo principal
├── assets/
│   ├── css/
│   │   └── admin.css                  # Estilos del admin
│   └── js/
│       └── admin.js                   # JavaScript del admin
├── includes/
│   └── export-functions.php           # Funciones de exportación
└── README.md                          # Este archivo
```

## Autor

**Benjamin Oscco Arias**
- Website: https://linmania.com
- Plugin URI: https://linmania.com

## Licencia

GPL v2 o posterior - https://www.gnu.org/licenses/gpl-2.0.html

## Changelog

### 1.0.0
- Lanzamiento inicial
- Tabla de inscripciones con filtros
- Modal de detalles de jugadores
- Exportación a Excel y PDF
- Integración completa con WooCommerce

