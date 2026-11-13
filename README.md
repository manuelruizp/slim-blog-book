# Código del ebook sobre Slim Framework

Proyectos completos para acompañar el ebook en español **Tu primer blog con PHP: Slim 4**. El código usa variables y archivos en inglés; las rutas de la aplicación pueden estar en español.

## Cómo usar el repositorio

Cada capítulo contiene una carpeta `start/` y otra `end/`. La primera muestra el punto de partida; la segunda, el resultado completo. Puedes trabajar sobre `start/` y consultar `end/` cuando necesites comparar. El inicio de cada capítulo coincide con el final del anterior. El inicio del capítulo 1 es una carpeta vacía con una nota.

No necesitas cambiar de ramas ni etiquetas de Git. Descarga o clona este repositorio, elige un capítulo y sigue su README. No ejecutes Composer desde la raíz del repositorio: cada proyecto tiene sus propios archivos de dependencias.

## Capítulos

- [Capítulo 1 · Del proyecto vacío a la primera página](chapter-01/README.md)
- [Capítulo 2 · De la ruta a la respuesta](chapter-02/README.md)
- [Capítulo 3 · Páginas con plantillas](chapter-03/README.md)
- [Capítulo 4 · De los datos de ejemplo a SQLite](chapter-04/README.md)
- [Capítulo 5 · Crear artículos con un formulario](chapter-05/README.md)
- [Capítulo 6 · Iniciar y cerrar sesión](chapter-06/README.md)
- [Capítulo 7 · Proteger el panel con middleware](chapter-07/README.md)
- [Capítulo 8 · Editar y eliminar publicaciones](chapter-08/README.md)
- [Capítulo 9 · Guardar borradores y publicar](chapter-09/README.md)
- [Capítulo 10 · Probar el blog de principio a fin](chapter-10/README.md)

## Entorno

- PHP 8.5 para reproducir el entorno de validación de esta primera versión.
- Composer 2.
- Extensiones habituales de PHP y `pdo_sqlite` desde el capítulo 4.
- Un navegador y una terminal.

Las versiones concretas de los paquetes están fijadas en cada `composer.lock`. Usa `composer install`; `composer update` puede cambiar las versiones del ejemplo. Los capítulos 1 y 2 necesitan Slim y Slim PSR-7; desde el 3 se añade PHP-View. No se incluye `vendor/`.

Comprueba el entorno con `php -v`, `composer --version` y, desde el capítulo 4, `php --ri pdo_sqlite`. Si falta SQLite, revisa el archivo de configuración que muestra `php --ini`.

## Primera ejecución

Por ejemplo, para abrir el resultado del capítulo 3, desde la raíz del repositorio:

```sh
cd chapter-03/end
composer install
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. En los capítulos con base de datos, ejecuta primero los scripts que indica su README. `public/` es la raíz web; no sirvas la carpeta completa del proyecto. El servidor integrado es para aprendizaje local.

## Comparar las versiones

En tu editor abre el mismo archivo de `start/` y `end/` con la opción de comparar archivos. Si usas Git, también puedes comparar, por ejemplo:

```sh
git diff --no-index -- chapter-08/start/public/index.php chapter-08/end/public/index.php
```

Ese comando muestra las diferencias y devuelve código 1 cuando las encuentra; en ese caso no significa que haya fallado el proyecto.

## Bases de datos y sesiones

Cada versión usa su propio `database/blog.sqlite`, generado con los scripts incluidos. No se publican bases de datos, contraseñas ni archivos de sesión. Las cuentas de copias diferentes pueden tener contraseñas diferentes.

Para empezar una versión desde cero, utiliza sus scripts en el orden indicado. Si prefieres conservar tus artículos al pasar de un capítulo a otro, detén ambos servidores, guarda una copia de seguridad y copia únicamente la base del final anterior al inicio siguiente. Al pasar al resultado del capítulo 9, ejecuta también su migración de estados. No copies una base del capítulo 9 a una versión anterior y no uses `setup.php` para cambiar su esquema.

Las bases creadas con versiones antiguas del manuscrito pueden tener un esquema distinto: consulta [los ajustes del manuscrito](docs/manuscript-notes.md) antes de reutilizarlas.

Detén el servidor anterior antes de abrir otra copia. Las cookies de un mismo host pueden compartirse entre puertos; cierra la sesión anterior o usa un perfil de navegador separado para evitar confundir accesos.

## Comprobar el material

```sh
php tools/check-snapshots.php
```

Este comando verifica la continuidad entre capítulos y la sintaxis de los archivos PHP; no instala dependencias ni prueba el comportamiento HTTP. En `chapter-10/end`, `php tests/run.php` comprueba las reglas del repositorio y de validación con SQLite en memoria.

Consulta [el alcance de las verificaciones](docs/validation.md) y los [notas de consistencia y bases anteriores](docs/manuscript-notes.md). Los ejemplos de creación del capítulo 5 todavía no tienen control de acceso: esa protección se incorpora en el capítulo 6. Estas etapas sirven para aprendizaje local.

## Alcance del proyecto

Los ejemplos del ebook se concentran en PHP, Slim y HTML. CSS y JavaScript quedan fuera del recorrido principal.

## Compatibilidad con bases antiguas

Los esquemas y las pruebas del manuscrito están sincronizados con estas versiones. Si conservas una base creada sin AUTOINCREMENT, consulta [la migración opcional de identidad](docs/manuscript-notes.md#si-conservas-una-base-de-una-versión-anterior) antes de reutilizarla. No se ejecuta automáticamente sobre tus datos.
