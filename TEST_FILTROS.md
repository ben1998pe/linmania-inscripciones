# Guía de Prueba de Filtros

## Cambios Implementados

### ✅ Correcciones realizadas:
1. Estructura de `meta_query` corregida
2. Filtro de categorías habilitado
3. Paginación con filtros arreglada
4. Búsqueda general mejorada

## Pruebas a Realizar

### 1. Probar Filtro de CATEGORÍAS
- [ ] Ve a `Inscripciones` en el menú de WordPress
- [ ] Selecciona una categoría (ej: BULLSHOOTER)
- [ ] Verifica que solo aparezcan inscripciones de esa categoría
- [ ] Cambia a otra categoría (ej: CONNECTION)
- [ ] Selecciona "Nothing selected" para ver todas

### 2. Probar Filtro de LOCAL
- [ ] Selecciona un local del dropdown
- [ ] Verifica que solo aparezcan inscripciones de ese local
- [ ] Prueba combinarlo con el filtro de categorías

### 3. Probar Filtro de EQUIPO
- [ ] Escribe parte del nombre de un equipo
- [ ] Verifica que filtre mientras escribes (debounce de 500ms)
- [ ] Prueba con mayúsculas y minúsculas (debe ser case-insensitive)

### 4. Probar BÚSQUEDA GENERAL
- [ ] Escribe en el campo "Buscar"
- [ ] Debe buscar en LOCAL y EQUIPO simultáneamente
- [ ] Verifica que actualice con debounce

### 5. Probar PAGINACIÓN con Filtros
- [ ] Aplica un filtro de categoría
- [ ] Verifica que el contador de registros sea correcto
- [ ] Navega entre páginas
- [ ] Verifica que el filtro se mantenga al cambiar de página

### 6. Probar COMBINACIÓN de Filtros
- [ ] Aplica Categoría + Local
- [ ] Aplica Categoría + Equipo
- [ ] Aplica Categoría + Local + Equipo
- [ ] Verifica que todos trabajen juntos correctamente

### 7. Probar LIMPIAR Filtros
- [ ] Aplica varios filtros
- [ ] Selecciona "Nothing selected" en cada dropdown
- [ ] Borra el texto del campo Equipo
- [ ] Verifica que vuelvan a aparecer todos los registros

## Verificación Técnica

### En la Consola del Navegador (F12):
```javascript
// Deberías ver estos mensajes al cargar la página:
// - "Response received:" con los datos
// - "Debug info:" con información de la consulta
// - "Filter options response:" con las opciones de filtros
```

### Verificación de Datos:
- [ ] Los totales de registros son correctos
- [ ] La paginación muestra el número correcto de páginas
- [ ] No hay errores en la consola del navegador
- [ ] No hay errores PHP en el log de WordPress

## Problemas Comunes

### Si los filtros no aparecen:
1. Verifica que haya órdenes de WooCommerce con datos
2. Revisa que los campos meta estén guardados correctamente
3. Comprueba la consola del navegador para errores AJAX

### Si las categorías no filtran:
1. Verifica que los productos tengan categorías asignadas
2. Revisa que las órdenes tengan productos asociados

### Si la paginación falla:
1. Limpia el caché del navegador
2. Verifica que `$per_page` tenga un valor válido
3. Revisa el log de errores de PHP

## Notas Técnicas

### Filtro de Categorías
- Se aplica DESPUÉS de obtener los datos
- Usa búsqueda case-insensitive con `stripos()`
- Obtiene categorías de los términos del producto

### Filtros de Local y Equipo
- Se aplican mediante `meta_query` de WordPress
- Usan comparación `LIKE` para búsqueda parcial
- Son case-sensitive en la base de datos

### Búsqueda General
- Busca en LOCAL y EQUIPO con relación OR
- Tiene debounce de 500ms
- Se combina con otros filtros usando AND

## Código de Debug

Si necesitas hacer debug, abre la consola del navegador y ejecuta:

```javascript
// Ver datos actuales de la tabla
console.log(currentFilters);
console.log(currentPage, perPage);

// Forzar recarga
loadInscriptions();
```


