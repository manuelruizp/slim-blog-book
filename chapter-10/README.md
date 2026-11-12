# Capítulo 10 · Probar el blog de principio a fin

Probar las reglas de validación y persistencia con SQLite en memoria.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-09/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-10/start
composer install
php database/setup.php
php database/migrate-article-status.php
php database/create-admin.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

La creación de cuenta muestra `admin@example.test` y una contraseña aleatoria en la terminal. Guarda esa contraseña para esta copia. Si la cuenta ya existe, el script conserva su contraseña; no genera una nueva cuenta.

Para una copia nueva, el orden es preparar la tabla, migrarla y crear la cuenta. Para una base que ya venía del capítulo 8, ejecuta **solo** la migración de estados; no uses `setup.php` como migración.

## Ejecutar end

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-10/end
composer install
php database/setup.php
php database/migrate-article-status.php
php database/create-admin.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

La creación de cuenta muestra `admin@example.test` y una contraseña aleatoria en la terminal. Guarda esa contraseña para esta copia. Si la cuenta ya existe, el script conserva su contraseña; no genera una nueva cuenta.

Para una copia nueva, el orden es preparar la tabla, migrarla y crear la cuenta. Para una base que ya venía del capítulo 8, ejecuta **solo** la migración de estados; no uses `setup.php` como migración.

## Archivos que cambian

- `tests/run.php`

## Qué comprobar al terminar

- `php tests/run.php` termina con 42 comprobaciones correctas.
- Una expectativa falsa detiene el script con código de salida 1.
- Las pruebas HTTP manuales siguen siendo necesarias.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.

Cada copia usa su propio `database/blog.sqlite`. La base de datos no se comparte automáticamente entre `start/` y `end/`. Consulta [las instrucciones generales](../README.md#bases-de-datos-y-sesiones) antes de mover datos.

Consulta los [notas de consistencia y bases anteriores](../docs/manuscript-notes.md), en particular la corrección de identidad de los artículos.

## Ejecutar las pruebas automáticas

Desde `chapter-10/end`:

```sh
php -l tests/run.php
php tests/run.php
```

El script usa SQLite en memoria y no modifica `database/blog.sqlite`. Contiene las mismas 42 comprobaciones del capítulo 10, incluidas ocho de regresión para identificadores eliminados.

## Formularios antiguos e identidad

Crea el último artículo A, deja abiertos sus formularios, elimínalo y crea B antes de enviar los formularios anteriores. B debe recibir otro identificador. Con sesión y token válidos, los POST de edición y eliminación de A deben devolver 404 y B debe conservarse. Desde el capítulo 9, comprueba también un botón antiguo de publicación: B debe seguir siendo borrador y sin fecha de publicación.
