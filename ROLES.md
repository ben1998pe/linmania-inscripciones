# Gestión de Roles - Linmania Blog e Inscripciones

## Rol Personalizado: "Gestor de Inscripciones"

El plugin crea automáticamente un rol personalizado llamado **"Gestor de Inscripciones"** que permite acceso exclusivo al apartado de inscripciones.

### Características del Rol

- **Nombre del Rol**: `gestor_inscripciones`
- **Nombre para Mostrar**: "Gestor de Inscripciones"
- **Permisos**:
  - `read`: Acceso básico de lectura
  - `linmania_manage_inscriptions`: Permiso específico para gestionar inscripciones
  - **Acceso al Admin**: Puede acceder al panel de administración de WordPress
  - **Permisos Mínimos**: Solo tiene acceso a lo necesario para gestionar inscripciones

### Cómo Asignar el Rol

1. **Ve a Usuarios > Todos los Usuarios** en el admin de WordPress
2. **Edita el usuario** al que quieres asignar el rol
3. **En la sección "Rol"**, selecciona **"Gestor de Inscripciones"**
4. **Guarda los cambios**

### Qué Puede Hacer un Gestor de Inscripciones

✅ **Acceso permitido**:
- Acceder al panel de administración de WordPress
- Ver la página de inscripciones
- Filtrar y buscar inscripciones
- Ver detalles de jugadores
- Exportar datos a Excel y PDF
- Acceder a todas las funcionalidades del plugin

❌ **Acceso denegado**:
- No puede editar posts o páginas
- No puede gestionar usuarios
- No puede modificar configuraciones del sitio
- No puede gestionar plugins o temas
- No puede acceder a WooCommerce (excepto para ver inscripciones)
- No puede subir archivos
- No puede moderar comentarios
- Solo tiene acceso al apartado de inscripciones

### Administradores

Los usuarios con rol **"Administrador"** también tienen acceso automático al apartado de inscripciones, ya que se les asigna automáticamente el capability `linmania_manage_inscriptions`.

### Seguridad

- Todas las funciones AJAX verifican los permisos
- El menú solo aparece para usuarios autorizados
- Las exportaciones requieren permisos específicos
- Los datos están protegidos contra acceso no autorizado

### Limpieza Automática

Cuando el plugin se desactiva:
- Se elimina automáticamente el rol "Gestor de Inscripciones"
- Se remueve el capability de los administradores
- No quedan restos del plugin en la base de datos

## Casos de Uso

### Para Organizadores de Torneos
- Crear usuarios específicos para gestionar inscripciones
- Dar acceso solo a la información necesaria
- Mantener la seguridad del sitio principal

### Para Equipos de Trabajo
- Asignar diferentes niveles de acceso
- Separar responsabilidades
- Facilitar la gestión de usuarios

### Para Administradores
- Mantener control total del sitio
- Acceso automático a todas las funcionalidades
- Posibilidad de gestionar roles y permisos
