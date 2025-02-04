# Panel Administrativo Salteñería

![Mockup del panel administrativo](/mockup.png)

El Panel Administrativo para la Salteñería en la ciudad de Santa cruz de la sierra - Bolivia,Una herramienta de gestión integral que permite administrar usuarios, roles, permisos y temas visuales de manera eficiente. Está diseñado para facilitar la administración de una salteñería, ofreciendo control total sobre las operaciones y el inventario del sistema.

## Características principales
- **Gestión de usuarios**: Creación, edición y eliminación de usuarios.
- **Roles y permisos**: Asignación de roles y permisos utilizando Spatie.
- **Temas personalizables**: Cambio de temas visuales utilizando el plugin de Themes.
- **Interfaz intuitiva**: Diseño moderno y responsive con Tailwind CSS.

## Tecnologías utilizadas
- **Frontend**: Laravel Filament, Tailwind CSS
- **Backend**: Laravel
- **Autenticación y autorización**: Spatie para roles y permisos
- **Temas**: `Themes`
- **Base de datos**: MySQL

## Requisitos previos
Para ejecutar este proyecto, necesitas tener instalado:
- [PHP](https://www.php.net/) (v8.0 o superior)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (v18 o superior)
- [npm](https://www.npmjs.com/) o [Yarn](https://yarnpkg.com/)
- [MySQL](https://www.mysql.com/) (o una instancia en la nube)

## Instalación
Sigue estos pasos para configurar y ejecutar el proyecto:

1. Clona el repositorio:
   ```bash
   git clone https://github.com/programfive/restaurante-sistema.git
2. Navega al directorio del proyecto:
    ```bash
    cd restaurante-sistema
3. Instala las dependencias de Composer:
    ```bash
    composer install 
4. Instala las dependencias de npm:
    ```bash
    npm install
5. Configura las variables de entorno:
    ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=nombre_de_tu_base_de_datos
    DB_USERNAME=tu_usuario
    DB_PASSWORD=tu_contraseña
6. Genera la clave de aplicación:
   ```bash
   php artisan key:generate
7. Ejecuta las migraciones y seeders:
    ```bash
    php artisan migrate --seed
8. Inicia el servidor de desarrollo:
     ```bash
    php artisan serve
## Licencia MIT

Copyright (c) [2024] [Salteñas]

Por la presente se otorga permiso, libre de cargos, a cualquier persona que obtenga una copia de este software y los archivos de documentación asociados (el "Software"), a utilizar el Software sin restricción, incluyendo sin limitación los derechos de uso, copia, modificación, fusión, publicación, distribución, sublicencia y/o venta de copias del Software, y a permitir a las personas a las que se les proporcione el Software a hacer lo mismo, sujeto a las siguientes condiciones:

El aviso de copyright anterior y este aviso de permiso se incluirán en todas las copias o partes sustanciales del Software.

EL SOFTWARE SE PROPORCIONA "TAL CUAL", SIN GARANTÍA DE NINGÚN TIPO, EXPRESA O IMPLÍCITA, INCLUYENDO PERO NO LIMITADO A GARANTÍAS DE COMERCIALIZACIÓN, IDONEIDAD PARA UN PROPÓSITO PARTICULAR Y NO INFRACCIÓN. EN NINGÚN CASO LOS AUTORES O TITULARES DEL COPYRIGHT SERÁN RESPONSABLES POR NINGUNA RECLAMACIÓN, DAÑOS U OTRAS RESPONSABILIDADES, YA SEA EN UNA ACCIÓN DE CONTRATO, AGRAVIO O CUALQUIER OTRO MOTIVO, QUE SURJA DE O EN CONEXIÓN CON EL SOFTWARE O EL USO U OTROS TRATOS EN EL SOFTWARE.
