# hmac
Hash-based message authorization code in Laravel applications

## Install

You can install the package via composer:
```bash
composer require patrikap/hmac
```

Optionally you can publish the config file with:
```bash
php artisan vendor:publish --provider="Patrikap\Hmac\HmacServiceProvider.php" --tag="config" 
```

Add `HMAC_PRIVATE_KEY=` string at the new line in `.env` file:
```bash
echo "HMAC_PRIVATE_KEY=" >> .env
```

Generate private key:
```bash
php artisan hmac:generate
```

## Usage

This packages provides a middleware which can be added as a global middleware or as a single route.

Add the middleware in route section:
```php
// in `app/Http/Kernel.php`

protected $routeMiddleware = [
    // ...
    'auth.hmac' => \Patrikap\Hmac\Middleware\AuthenticateWithHmac::class,
];
```
Use this in routes:
```php
// in a routes file

Route::post('/endpoint', function () {})
    ->middleware('auth.hmac');
// or use single mode
    ->middleware(\Patrikap\Hmac\Middleware\AuthenticateWithHmac::class);
```

## To be continue...
* some tests
* separation into interfaces ("sign maker" and "sign verifier")
* notification for new private key generate

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Authors
* [Patrikap](https://github.com/patrikap) - development
* [Victor Fursenko](https://github.com/va-fursenko) - code review

## Acknowledgments
* [Hash-based message authorization code](https://starkovden.github.io/authentication-and-authorization.html#hmac)
* Inspiration
