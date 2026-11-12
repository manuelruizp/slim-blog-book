# Capítulo 7 · Proteger el panel con middleware

Compartir la protección del panel mediante un grupo de rutas.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-06/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-07/start
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
cd chapter-07/end
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
|   +-- ArticleRepository.php
+-- templates/
|   +-- layout.php
|   +-- home.php
|   +-- article-list.php
|   +-- article.php
|   +-- article-not-found.php
|   +-- article-form.php
|   +-- login.php
```

**Crear:** ningún archivo.

**Modificar:** `public/index.php`.

**Conservar:** La conexión y la tabla de datos, `ArticleRepository.php`, todas las plantillas, la sesión, `$hasValidToken` y la definición de `$requireLogin`.

## Etapas del capítulo

### Primera etapa Reunir las rutas del editor

Modifica `public/index.php`: agrega el import de `RouteCollectorProxy` y reemplaza las dos rutas individuales del editor por el grupo completo de tres bloques. Conserva `$requireLogin` y las rutas públicas.

### Segunda etapa Verificar el alcance

Revisa el mismo `public/index.php`: las rutas de acceso permanecen fuera del grupo y el cierre de sesión conserva su protección individual. No se cambian plantillas ni scripts de datos.

## Archivos que cambian

- `public/index.php`

## Qué comprobar al terminar

- Las rutas del grupo mantienen el comportamiento del capítulo anterior.
- Una ruta externa al grupo no queda protegida por tener un prefijo parecido.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.

Cada copia usa su propio `database/blog.sqlite`. La base de datos no se comparte automáticamente entre `start/` y `end/`. Consulta [las instrucciones generales](../README.md#bases-de-datos-y-sesiones) antes de mover datos.

## Relación con el capítulo 6

El capítulo 6 explica e implementa las decisiones del middleware de acceso. Aquí se conserva esa función y se cambia el alcance de su asociación: de rutas individuales al grupo del panel. Las credenciales, los formularios y el comportamiento final permanecen iguales.
