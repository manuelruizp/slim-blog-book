# Capítulo 8 · Editar y eliminar publicaciones

Editar y eliminar artículos mediante el panel privado.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-07/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-08/start
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
cd chapter-08/end
composer install
php database/setup.php
php database/create-admin.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

La creación de cuenta muestra `admin@example.test` y una contraseña aleatoria en la terminal. Guarda esa contraseña para esta copia. Si la cuenta ya existe, el script conserva su contraseña; no genera una nueva cuenta.

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
+-- src/
|   +-- ArticleRepository.php [modificar]
|   +-- validate-article.php [crear]
+-- templates/
|   +-- layout.php
|   +-- home.php
|   +-- article-list.php
|   +-- article.php
|   +-- article-not-found.php
|   +-- article-form.php [modificar]
|   +-- login.php
|   +-- admin-article-list.php [crear]
|   +-- article-delete.php [crear]
```

**Crear:** `src/validate-article.php`, `templates/admin-article-list.php`, `templates/article-delete.php`.

**Modificar:** `src/ArticleRepository.php`, `templates/article-form.php`, `public/index.php`.

**Conservar:** `database/connection.php`, el esquema de la tabla, las plantillas públicas, las rutas públicas y de sesión y la protección `$requireLogin` del grupo.

## Etapas del capítulo

### Primera etapa Preparar las piezas compartidas

Amplía `src/ArticleRepository.php` con la lectura del panel y la actualización; crea `src/validate-article.php`; adapta `templates/article-form.php` y la validación del POST de creación en `public/index.php`. La creación debe seguir funcionando al terminar.

### Segunda etapa Mostrar y editar artículos

Crea `templates/admin-article-list.php` y agrega a `public/index.php` el GET del listado y las dos rutas de edición. Conserva la validación compartida, el token y la protección del grupo.

### Tercera etapa Confirmar y eliminar artículos

Agrega `delete()` a `src/ArticleRepository.php`, crea `templates/article-delete.php` e incorpora sus rutas GET y POST al grupo existente en `public/index.php`. Termina probando cancelación, eliminación y rechazos.

En la primera etapa se comprueba que crear sigue funcionando. En la segunda se comprueban listado y edición. El enlace «Eliminar» y el enlace «Volver al panel» se utilizan cuando sus rutas ya estén implementadas.

## Archivos que cambian

- `public/index.php`
- `src/ArticleRepository.php`
- `src/validate-article.php`
- `templates/admin-article-list.php`
- `templates/article-delete.php`
- `templates/article-form.php`

## Qué comprobar al terminar

- Editar conserva el identificador y permite cambiar el slug.
- GET de confirmación no elimina; POST elimina y redirige.
- Un formulario antiguo no debe afectar a otro artículo creado después.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.

Cada copia usa su propio `database/blog.sqlite`. La base de datos no se comparte automáticamente entre `start/` y `end/`. Consulta [las instrucciones generales](../README.md#bases-de-datos-y-sesiones) antes de mover datos.

Consulta los [notas de consistencia y bases anteriores](../docs/manuscript-notes.md), en particular la corrección de identidad de los artículos.

## Formularios antiguos e identidad

Crea el último artículo A, deja abiertos sus formularios, elimínalo y crea B antes de enviar los formularios anteriores. B debe recibir otro identificador. Con sesión y token válidos, los POST de edición y eliminación de A deben devolver 404 y B debe conservarse. Desde el capítulo 9, comprueba también un botón antiguo de publicación: B debe seguir siendo borrador y sin fecha de publicación.
