Welcome to laravel vue admin panel
This laravel vue admin panel is build using Laravel, Vuejs, Vuetify, Mdi-Icons inspired by 'Perfect web Soultion' and developed by Arham Roshan Ar

Copyrights © abrsoftwaretechnologies@gmail.com All Rights Reserved
Getting strated
composer install,
first rename .env.example,
then php artisan key:generate,
and setup your database connection,
php artisan migrate,
php artisan tinker,
then run this line,
\App\User::create(['name'=>'Admin','email'=>'admin@admin.com','password'=>bcrypt('password')]),
now run,
\App\Role::create(['name'=>'Administrator']),
then php artisan serve,
email:admin@admin.com,
password:password
Thanks