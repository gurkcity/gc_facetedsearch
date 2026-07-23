@echo off
set _PS_ROOT_DIR_=C:\xampp\htdocs\prestashop_9
php vendor\bin\phpstan analyse -v --configuration=tests/phpstan/phpstan.neon
