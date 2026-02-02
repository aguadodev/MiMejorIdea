Mi Mejor Idea es un proyecto de desarrollo didáctico, colaborativo y sin ánimo de lucro.

Puedes descargar e instalar tu propia versión de la aplicación:

Requisitos recomendados para desarrollo local en Windows: 
1. XAMPP (Apache, PHP, MySQL, phpMyAdmin)
2. Composer
3. Symfony CLI
4. Git
5. Cuenta de correo (por ejemplo GMail)

Pasos para instalación/despliegue:
1. Descarga/clona el repositorio
2. Crear una base de datos local y configurar sus datos en .env
3. Crear una contraseña de aplicación para la cuenta de correo y configurar en .env
4. Desde la carpeta del proyecto ejecutar "composer install"
5. Desde la carpeta del proyecto ejecutar migraciones de BD "php bin/console doctrine:migrations:migrate"
6. Ejecutar servidor web de desarrollo: "symfony server:start"
7. Abrir aplicación en el navegador local: "http://127.0.0.1:8000"

