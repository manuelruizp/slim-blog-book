# Capítulo 4 · De los datos de ejemplo a SQLite

Recuperar artículos desde un archivo SQLite mediante un repositorio.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-03/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-04/start
composer install
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

## Ejecutar end

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-04/end
composer install
php database/setup.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

## Archivos que cambian

- `database/connection.php`
- `database/setup.php`
- `public/index.php`
- `src/ArticleRepository.php`
- `templates/article-list.php`

## Qué comprobar al terminar

- Los artículos sobreviven a un reinicio del servidor.
- El detalle ausente devuelve 404.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.

Cada copia usa su propio `database/blog.sqlite`. La base de datos no se comparte automáticamente entre `start/` y `end/`. Consulta [las instrucciones generales](../README.md#bases-de-datos-y-sesiones) antes de mover datos.

Consulta los [notas de consistencia y bases anteriores](../docs/manuscript-notes.md), en particular la corrección de identidad de los artículos.
