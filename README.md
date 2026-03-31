Mi Mejor Idea es un proyecto de desarrollo didáctico, colaborativo y sin ánimo de lucro.

Puedes descargar e instalar tu propia versión de la aplicación:

Requisitos recomendados para desarrollo local en Windows: 
1. **XAMPP** (Apache, PHP, MySQL, phpMyAdmin)
2. **php.ini** con las extensiones adecuadas (php-zip)
3. [**Composer**]([url](https://getcomposer.org/download/))
4. [**Symfony CLI**]([url](https://symfony.com/download)) (Añadido al path)
5. Git
6. Cuenta de correo (por ejemplo GMail)

Pasos para instalación/despliegue:
1. Descarga/clona el repositorio
2. Crear una base de datos local
3. Crear una contraseña de aplicación para la cuenta de correo
4. Configurar datos de BD y correo en .env
5. Desde la carpeta del proyecto ejecutar "**composer install**"
6. Desde la carpeta del proyecto ejecutar migraciones de BD "**php bin/console doctrine:migrations:migrate**"
7. Ejecutar servidor web de desarrollo: "**symfony server:start**"
8. Abrir aplicación en el navegador local: "**http://127.0.0.1:8000**"

