# Diagnóstico: Solo aparecen 3 de 5 órdenes

## Cambios Realizados

He agregado logs de debug detallados para identificar el problema:

### Logs agregados:
1. **Filtros recibidos** (RAW y LIMPIO)
2. **Resultados de consulta** (found_posts, post_count)
3. **Inscripciones procesadas**
4. **Filtro de categoría** (si se aplica)
5. **Inscripciones finales**

## Instrucciones para Diagnosticar

### Paso 1: Verificar logs de PHP

Los logs de debug se encuentran en el archivo de errores de PHP. Dependiendo de tu configuración:

**XAMPP (Windows):**
```
C:\xampp\php\logs\php_error_log
```
O en:
```
C:\xampp\apache\logs\error.log
```

### Paso 2: Abrir la página de Inscripciones

1. Ve a **WordPress Admin → Inscripciones**
2. Abre la **Consola del Navegador** (F12)
3. Ve a la pestaña **Network** y observa las peticiones AJAX
4. Recarga la página (Ctrl + Shift + R)

### Paso 3: Revisar los logs

En el archivo de error de PHP deberías ver algo como:

```
=== FILTROS RECIBIDOS ===
Categoría RAW: ""
Local RAW: ""
Equipo RAW: ""
Search RAW: ""
Categoría LIMPIA: ""
Local LIMPIA: ""
Equipo LIMPIA: ""
=== RESULTADOS DE CONSULTA ===
Found posts: 5
Post count: 5
Need all records: false
Total inscripciones procesadas: 5
Inscripciones FINALES a enviar: 5
```

## Posibles Causas del Problema

### 🔍 Causa 1: Filtro aplicándose sin querer
Si ves en los logs:
```
APLICANDO FILTRO DE CATEGORÍA: "algún_valor"
Inscripciones después de filtro categoría: 3
```
**Solución:** El filtro se está aplicando. Verifica que los selectores estén en "Todas las categorías".

### 🔍 Causa 2: Órdenes sin campos meta
Si ves:
```
Total inscripciones procesadas: 3
```
**Problema:** Solo 3 órdenes tienen los campos `local`, `nombre_equipo`, etc.
**Solución:** Las otras 2 órdenes no tienen estos campos personalizados.

### 🔍 Causa 3: Tipo de post incorrecto
Si ves:
```
Found posts: 3
```
**Problema:** La consulta solo encuentra 3 órdenes del tipo `shop_order_placehold`.
**Solución:** Las otras 2 órdenes son de tipo diferente.

### 🔍 Causa 4: Meta query filtrando por error
Si hay un `meta_query` en los logs de debug y solo encuentra 3:
**Problema:** Los filtros de local/equipo están activos sin querer.
**Solución:** Verificar que los campos de filtro estén vacíos.

## Acciones a Realizar

### Si solo 3 órdenes tienen los campos meta:

Necesitas agregar los campos personalizados a las otras 2 órdenes. Puedes hacerlo:

**Opción A: Manualmente en WooCommerce**
1. Ve a WooCommerce → Pedidos
2. Abre cada orden que no aparece
3. Busca "Custom Fields" al final
4. Agrega estos campos:
   - `nombre_equipo`: Nombre del equipo
   - `local`: Local del equipo
   - `nombre_jugador_1`: Jugador 1
   - `telefono_jugador_1`: Teléfono

**Opción B: Con código PHP**
Puedo crear un script para agregar estos campos automáticamente.

### Si las órdenes son de tipo diferente:

Necesitas cambiar la consulta para incluir otros tipos. Las órdenes de WooCommerce pueden ser:
- `shop_order` (tipo antiguo)
- `shop_order_placehold` (tipo nuevo)
- `wc-completed`, `wc-processing`, etc. (estados)

## Script de Verificación Rápida

Copia este código en tu navegador (consola F12) cuando estés en la página de Inscripciones:

```javascript
// Ver filtros actuales
console.log('Filtros activos:', currentFilters);

// Ver respuesta del servidor
jQuery.ajax({
    url: linmania_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'linmania_get_inscriptions',
        nonce: linmania_ajax.nonce,
        page: 1,
        per_page: 100,
        search: '',
        categoria: '',
        local: '',
        equipo: '',
        sort_by: 'ID',
        sort_order: 'DESC'
    },
    success: function(response) {
        console.log('=== RESPUESTA COMPLETA ===');
        console.log('Total encontrado:', response.data.total);
        console.log('Inscripciones:', response.data.inscriptions);
        console.log('Debug:', response.data.debug);
    }
});
```

## Próximos Pasos

1. **Recarga la página** de Inscripciones (Ctrl + Shift + R)
2. **Copia los logs** del archivo de error de PHP
3. **Comparte** qué ves en los logs

Con esa información podré identificar exactamente qué está causando que solo aparezcan 3 de 5 órdenes.


