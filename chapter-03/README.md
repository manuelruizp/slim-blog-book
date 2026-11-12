# Capítulo 3 · Páginas con plantillas

Separar el HTML en plantillas con un layout común.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-02/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-03/start
composer install
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

## Ejecutar end

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-03/end
composer install
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

## Archivos que cambian

- `composer.json`
- `composer.lock`
- `public/index.php`
- `templates/article-list.php`
- `templates/article-not-found.php`
- `templates/article.php`
- `templates/home.php`
- `templates/layout.php`

## Qué comprobar al terminar

- Portada, listado y detalle comparten navegación.
- Los signos `<` y `>` de los datos aparecen como texto.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.
