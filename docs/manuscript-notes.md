# Consistencia del manuscrito y bases anteriores

El manuscrito y los proyectos utilizan `id INTEGER PRIMARY KEY AUTOINCREMENT` para los artículos desde el capítulo 4. El capítulo 10 y `chapter-10/end/tests/run.php` contienen las mismas **42 comprobaciones**, incluidas las de eliminar, crear otro artículo y volver a intentar editar, eliminar o publicar con el identificador anterior. Los capítulos 8 y 9 explican también el caso HTTP: con sesión y token válidos, el destino eliminado responde 404 y el nuevo artículo permanece intacto.

## Si comienzas con esta versión

Sigue el README del capítulo y crea la base con sus scripts. No necesitas la herramienta de compatibilidad siguiente. `AUTOINCREMENT` evita reutilizar automáticamente identificadores de artículos eliminados; no asegura números consecutivos.

## Si conservas una base de una versión anterior

Cambiar `CREATE TABLE IF NOT EXISTS` y ejecutar `setup.php` de nuevo no modifica la identidad de una tabla existente. La migración de estados del capítulo 9 tampoco hace ese cambio.

1. Detén todos los servidores que utilicen esa base, cierra sesión y descarta las pestañas y formularios anteriores.
2. Desde la raíz del repositorio, ejecuta la herramienta sobre **una copia de trabajo** del archivo:

   ```sh
   php tools/migrate-article-identity.php "ruta/a/la/copia/database/blog.sqlite"
   ```

3. La herramienta reconoce los esquemas originales de los capítulos 4–8 y 9–10. Antes de reconstruir la tabla genera un respaldo junto a la copia, con el sufijo `.before-identity-…sqlite`. Conserva los artículos y sus identificadores actuales y, cuando existen, sus estados y fechas. Compara las filas y verifica la integridad antes de confirmar la transacción. Una segunda ejecución informa que no hacen falta cambios.
4. Comprueba la copia: inicia sesión de nuevo y sigue el caso de formulario antiguo del capítulo 8; desde el 9 comprueba también publicación. Conserva el archivo original y su respaldo hasta verificar los resultados.

Si modificaste el esquema, añadiste dependencias o la herramienta rechaza la base, prepara una migración específica; no intentes forzar esta herramienta. El servidor debe permanecer detenido durante el proceso.

Una base antigua no conserva necesariamente el mayor identificador eliminado en el pasado. La migración conserva las identidades presentes, pero no reconstruye ese historial ni invalida por sí misma formularios antiguos. Por eso debes descartarlos y comenzar sesiones nuevas. Las bases nuevas de esta edición usan la regla desde su creación.

Referencia: [asignación de identificadores y AUTOINCREMENT en SQLite](https://www.sqlite.org/autoinc.html).

## Otras precisiones sincronizadas

El capítulo 1 explica las respuestas nuevas de `withHeader()` y `withStatus()`, su retorno o reasignación y la diferencia con el flujo mutable del cuerpo. También explica cómo consultar los errores y habilitar detalles temporalmente en desarrollo local. Las aplicaciones conservan `addErrorMiddleware(false, true, true)` como configuración del ejemplo.

El capítulo 5 advierte que, antes del capítulo 9, la paginación pública está limitada a dos páginas de dos artículos. Un quinto artículo puede tener detalle sin aparecer en ese listado provisional. El capítulo 9 calcula el número de páginas para recorrer todas las publicaciones.

## Alcance del proyecto

El proyecto se concentra en PHP, Slim y HTML para aprendizaje local, con una cuenta de administrador. CSS, JavaScript, despliegue y recuperación de contraseña quedan fuera del recorrido principal. Esas ampliaciones no son requisitos para terminar el libro.
