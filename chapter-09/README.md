# Capítulo 9 · Guardar borradores y publicar

Guardar borradores, publicar y filtrar los resultados públicos.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-08/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-09/start
composer install
php database/setup.php
php database/create-admin.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

La creación de cuenta muestra `admin@example.test` y una contraseña aleatoria en la terminal. Guarda esa contraseña para esta copia. Si la cuenta ya existe, el script conserva su contraseña; no genera una nueva cuenta.

## Ejecutar end

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-09/end
composer install
php database/setup.php
php database/migrate-article-status.php
php database/create-admin.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

La creación de cuenta muestra `admin@example.test` y una contraseña aleatoria en la terminal. Guarda esa contraseña para esta copia. Si la cuenta ya existe, el script conserva su contraseña; no genera una nueva cuenta.

Para una copia nueva, el orden es preparar la tabla, migrarla y crear la cuenta. Para una base que ya venía del capítulo 8, ejecuta **solo** la migración de estados; no uses `setup.php` como migración.

## Mapa de archivos

```text
slim-blog/
+-- composer.json
+-- composer.lock
+-- public/
|   +-- index.php [modificar]
+-- database/
|   +-- connection.php
|   +-- setup.php
|   +-- create-admin.php
|   +-- migrate-article-status.php [crear]
+-- src/
|   +-- ArticleRepository.php [modificar]
|   +-- validate-article.php
+-- templates/
|   +-- layout.php
|   +-- home.php
|   +-- article-list.php [modificar]
|   +-- article.php
|   +-- article-not-found.php
|   +-- article-form.php [modificar]
|   +-- login.php
|   +-- admin-article-list.php [modificar]
|   +-- article-delete.php
|   +-- article-preview.php [crear]
```

**Crear:** `database/migrate-article-status.php`, `templates/article-preview.php`.

**Modificar:** `src/ArticleRepository.php`, `public/index.php`, `templates/article-form.php`, `templates/admin-article-list.php`, `templates/article-list.php`.

**Conservar:** `database/connection.php`, la cuenta y las rutas de sesión, `src/validate-article.php`, los métodos `update()` y `delete()`, el grupo protegido y las plantillas de detalle, confirmación de eliminación y layout.

## Etapas del capítulo

### Primera etapa Migrar sin perder los datos

Crea y ejecuta `database/migrate-article-status.php`. Conserva el archivo SQLite y comprueba que una segunda ejecución mantiene los datos. El servidor permanecerá detenido durante la migración.

### Segunda etapa Guardar y recuperar borradores privados

Actualiza las consultas y la creación en `src/ArticleRepository.php`, el destino del POST y la vista previa en `public/index.php`, y las plantillas del formulario y del listado privado. Crea `templates/article-preview.php`. Completa juntos los filtros del listado y del detalle públicos antes de probar la privacidad.

### Tercera etapa Publicar y conservar la fecha inicial

Agrega `publish()` a `src/ArticleRepository.php` y el POST de publicación al grupo de `public/index.php`. La plantilla del panel ya deja preparado su formulario de envío.

### Ajuste final Recorrer todas las publicaciones

Reemplaza la ruta pública del listado en `public/index.php` y los enlaces de paginación en `templates/article-list.php`. El total debe contar solo publicaciones.

No crees contenido entre la migración y la implementación de ambos filtros públicos. El botón «Publicar» queda preparado con la plantilla del panel; úsalo después de completar su método y ruta en la tercera etapa.

## Archivos que cambian

- `database/migrate-article-status.php`
- `public/index.php`
- `src/ArticleRepository.php`
- `templates/admin-article-list.php`
- `templates/article-form.php`
- `templates/article-list.php`
- `templates/article-preview.php`

## Qué comprobar al terminar

- Un borrador está ausente del listado público y su URL pública devuelve 404.
- Publicar fija una fecha que no cambia al repetir el envío.
- Más de cuatro publicaciones permiten recorrer tres páginas.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.

Cada copia usa su propio `database/blog.sqlite`. La base de datos no se comparte automáticamente entre `start/` y `end/`. Consulta [las instrucciones generales](../README.md#bases-de-datos-y-sesiones) antes de mover datos.

Consulta los [notas de consistencia y bases anteriores](../docs/manuscript-notes.md), en particular la corrección de identidad de los artículos.

## Formularios antiguos e identidad

Crea el último artículo A, deja abiertos sus formularios, elimínalo y crea B antes de enviar los formularios anteriores. B debe recibir otro identificador. Con sesión y token válidos, los POST de edición y eliminación de A deben devolver 404 y B debe conservarse. Desde el capítulo 9, comprueba también un botón antiguo de publicación: B debe seguir siendo borrador y sin fecha de publicación.
