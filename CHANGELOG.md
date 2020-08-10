## v0.0.1

- add composer.json
- composer require --dev -vvv phpunit/phpunit
- composer require --dev -vvv orchestra/testbench
- composer require -vvv ellipsesynergie/api-response
- composer require -vvv barryvdh/laravel-ide-helper

### docs
```sh
php vendor/phpunit/phpunit/phpunit tests
php artisan ide-helper:generate # 为 Facades 生成注释
php artisan ide-helper:models # 为数据模型生成注释
php artisan ide-helper:meta # 生成 PhpStorm Meta file
```
