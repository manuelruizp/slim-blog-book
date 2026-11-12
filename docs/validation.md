# Verificación de la primera versión

Fecha de verificación: 12 de noviembre de 2026.

## Entorno probado

- Windows con PHP 8.5.9 y Composer 2.10.2.
- Slim 4.15.3, Slim PSR-7 1.8.0 y PHP-View 3.4.0 desde el capítulo 3.
- SQLite mediante PDO_SQLITE.
- Servidor integrado de PHP en la interfaz local `127.0.0.1`.

Los archivos `composer.lock` de las copias coinciden con los de los paquetes utilizados en la verificación. Composer validó ambos conjuntos de dependencias: capítulos 1–2 y capítulos 3–10. Su aviso sobre la ausencia de una licencia es esperado: la licencia de distribución todavía debe decidirla el autor.

## Verificaciones realizadas

- Las nueve transiciones entre el final de un capítulo y el inicio del siguiente contienen los mismos archivos y contenido.
- Los 184 archivos PHP de los proyectos pasan la comprobación de sintaxis.
- Se prepararon copias temporales de los diez proyectos finales y se ejecutaron peticiones HTTP contra cada aplicación completa, sin sustituir sus rutas ni el repositorio.
- Se usaron bases de datos y sesiones temporales; no se escribieron datos de prueba en las carpetas entregadas.
- Las 42 comprobaciones de `chapter-10/end/tests/run.php` pasan. Incluyen las 34 del manuscrito y ocho nuevas de identidad de artículos.
- Una expectativa deliberadamente falsa en una copia temporal detuvo ese script con código de salida 1; después se restauró la expectativa.

| Proyecto final | Comprobaciones del recorrido y preparación |
| --- | ---: |
| Capítulo 1 | 5 |
| Capítulo 2 | 11 |
| Capítulo 3 | 13 |
| Capítulo 4 | 16 |
| Capítulo 5 | 22 |
| Capítulo 6 | 41 |
| Capítulo 7 | 41 |
| Capítulo 8 | 58 |
| Capítulo 9 | 86 |
| Capítulo 10 | 88 |
| Total | 381 |

Este total incluye verificaciones de contenido, redirecciones, preparación, datos persistidos y resultados de scripts. No son 381 peticiones HTTP ni pruebas independientes de seguridad. Las comprobaciones del capítulo 10 incluyen verificar la ejecución satisfactoria de su script; las 42 condiciones internas de ese script se informan por separado.

## Recorridos cubiertos

Se comprobaron portada, listado, paginación, detalle y artículo inexistente; HTML compartido y escape de texto persistido; creación válida, campos inválidos y slug duplicado; inicio y cierre de sesión; rechazo de operaciones sin sesión o token correcto; edición y eliminación con confirmación; formularios antiguos tras eliminar y crear otro artículo; privacidad de borradores, vista previa, publicación repetida y acceso a una tercera página pública.

En los capítulos 9 y 10 se repitió la migración y se comprobó que conservaba estados y fechas. Las pruebas de formularios antiguos comprobaron que no se modificaba ni eliminaba el artículo de reemplazo, y que tampoco se publicaba cuando correspondía.

## Repetir comprobaciones

Desde la raíz del repositorio:

```sh
php tools/check-snapshots.php
```

Este comprobador incluido verifica continuidad y sintaxis, sin necesitar `vendor/`.

Desde `chapter-10/end`, después de `composer install`:

```sh
php tests/run.php
```

El segundo comando no requiere preparar la base del blog: crea su propia base SQLite en memoria. Para repetir los recorridos HTTP, prepara el proyecto como indica el README de cada capítulo y sigue las comprobaciones del capítulo 10 del libro y de los README. El comprobador HTTP usado durante esta preparación fue una herramienta temporal de revisión y no forma parte de los comandos incluidos en el repositorio.

## Límites

Se verificaron las diez versiones finales. Las nueve versiones iniciales ejecutables tienen los mismos archivos que los finales previos, comprobados por contenido, y no se repitió su ejecución HTTP por separado. El inicio del capítulo 1 no es una aplicación.

No se comprobó la presentación en distintos navegadores, dispositivos o sistemas operativos, ni un despliegue público, ni las descargas desde una instalación sin caché de Composer. Las dependencias se utilizaron desde instalaciones locales con las versiones bloqueadas. La revisión no equivale a una auditoría completa de seguridad.

El esquema de identidad y las 42 comprobaciones del capítulo 10 están sincronizados con el manuscrito. Consulta [las instrucciones para bases anteriores](manuscript-notes.md).

## Sincronización técnica del manuscrito

En la revisión del 12 de noviembre de 2026 se comparó el bloque `database/setup.php` del capítulo 4 con el archivo correspondiente del repositorio. También se ensamblaron los ocho bloques de `tests/run.php` del capítulo 10: el archivo obtenido coincide exactamente con `chapter-10/end/tests/run.php` y devuelve **42 comprobaciones correctas**.

La verificación adicional pasó **96 comprobaciones** de correspondencia de esquemas, ejecución del script del manuscrito, migración de identidad y peticiones HTTP. En las versiones finales de los capítulos 8, 9 y 10 se creó A, se conservó su identificador, se eliminó y se creó B. Con sesión y token válidos, los formularios antiguos de edición y eliminación devolvieron 404; desde el capítulo 9 también lo hizo publicación. B mantuvo sus campos y, cuando corresponde, su estado de borrador sin fecha de publicación.

La herramienta opcional de identidad se probó con el esquema antiguo básico y con el esquema que incluye estados. Conservó filas, identificadores y la cuenta de administrador, generó un respaldo legible y no realizó cambios en una segunda ejecución. Rechazó una base con un índice adicional y una ruta inexistente sin modificarlas ni crear una base vacía.

El comprobador de versiones informó **9 transiciones idénticas y 184 archivos PHP sin errores de sintaxis**. La nueva herramienta de migración se comprobó por separado con `php -l`. Las pruebas se hicieron en copias aisladas; no se migraron bases personales del autor.
