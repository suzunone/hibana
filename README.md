Hibana Static Site Generator
================================================


Installation
--------------------------------
Require this package with composer using the following command:

```bash
composer require --dev suzunone/hibana
```

> [!NOTE]  
> If you encounter version conflicts with doctrine/dbal, please try:
> `composer require --dev suzunone/hibana --with-all-dependencies`

This package makes use of [Laravels package auto-discovery mechanism](https://medium.com/@taylorotwell/package-auto-discovery-in-laravel-5-5-ea9e3ab20518), which means if you don't install dev dependencies in production, it also won't be loaded.

If for some reason you want manually control this:
- add the package to the `extra.laravel.dont-discover` key in `composer.json`, e.g.
  ``` json
  "extra": {
    "laravel": {
      "dont-discover": [
        "suzunone/hibana"
      ]
    }
  }
  ```

* Add the following class to the providers array in config/app.php

``` php
Suzunone\Hibana\Providers\HibanaServiceProvider::class
```

If you want to manually load it only in non-production environments, instead you can add this to your AppServiceProvider with the register() method:
``` php
if ($this->app->isLocal()) {
    $this->app->register(\Suzunone\Hibana\Providers\HibanaServiceProvider::class);
}
```

> Note: Avoid caching the configuration in your development environment, it may cause issues after installing this package; respectively clear the cache beforehand via `php artisan cache:clear` if you encounter problems when running the commands

* Publish the config file to change implementations (ie. change the storage disk from the default) or set defaults for hibana.
```php
php artisan vendor:publish --provider="Suzunone\Hibana\Providers\HibanaServiceProvider" --tag=config
```
