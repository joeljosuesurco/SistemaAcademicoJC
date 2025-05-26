#PARA EJECUTAR EL PROYECTO SE DEBE TENER
-XAAMP xampp-windows-x64-8.2.12-0-VS16
-COMPOSER Composer version 2.8.6 
-NODE NPM v22.14.0

#PASOS EN LA CARPETA API
composer install
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve

#PASOS EN LA CARPETA FRONT END
npm install
npm run dev

#PARA LA APLICACION MOVIL
abrir con android studio
