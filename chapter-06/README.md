# Capítulo 6 · Iniciar y cerrar sesión

Crear una cuenta local, iniciar y cerrar sesión y proteger el editor.

## Las dos versiones

`start/` contiene exactamente los archivos de `chapter-05/end/`.
`end/` contiene el proyecto completo al terminar este capítulo. Puedes abrir los mismos archivos en ambas carpetas para comparar sus cambios.

## Ejecutar start

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-06/start
composer install
php database/setup.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

## Ejecutar end

Desde la raíz del repositorio, entra en la carpeta y prepara **su propia** instalación:

```sh
cd chapter-06/end
composer install
php database/setup.php
php database/create-admin.php
php -S 127.0.0.1:8080 -t public
```

Abre <http://127.0.0.1:8080/>. Detén el servidor con `Ctrl+C` antes de cambiar de versión. Los comandos de esta sección parten de una copia recién descargada.

La creación de cuenta muestra `admin@example.test` y una contraseña aleatoria en la terminal. Guarda esa contraseña para esta copia. Si la cuenta ya existe, el script conserva su contraseña; no genera una nueva cuenta.

## Archivos que cambian

- `database/create-admin.php`
- `public/index.php`
- `templates/article-form.php`
- `templates/login.php`

## Qué comprobar al terminar

- Sin sesión, el editor redirige al acceso.
- Una contraseña incorrecta devuelve 422; un token incorrecto devuelve 403.
- Cerrar sesión impide guardar desde un formulario antiguo.

## Si te pierdes

1. Detén el servidor y confirma que la terminal está en la versión que quieres ejecutar.
2. Compara primero el archivo que estás editando con el de `end/`.
3. Revisa que hayas completado todos los bloques antes de comprobar su sintaxis con `php -l ruta/al/archivo.php`.
4. Consulta la terminal del servidor si aparece un error 500.

Cada copia usa su propio `database/blog.sqlite`. La base de datos no se comparte automáticamente entre `start/` y `end/`. Consulta [las instrucciones generales](../README.md#bases-de-datos-y-sesiones) antes de mover datos.

## Etapas de lectura

El capítulo se organiza en tres etapas: preparar el acceso, iniciar y recordar la sesión, y proteger el editor y cerrar sesión. Comprueba primero la cuenta local; después, el GET del formulario y el POST de acceso; finalmente, los rechazos de sesión/token y el cierre de sesión. Completa los bloques indicados antes de ejecutar cada comprobación.

`start/` y `end/` siguen representando los límites del capítulo completo. El resultado de una comprobación intermedia no sustituye la protección que se completa en la tercera etapa.
