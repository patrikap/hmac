<?php
declare(strict_types = 1);


namespace Patrikap\Hmac;


use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Patrikap\Hmac\Services\HmacService;
use Patrikap\Hmac\Console\Commands\HmacGenerateCommand;

/**
 * Class HmacServiceProvider
 * @package Patrikap\Hmac
 *
 * Base HMAC service provider
 *
 * @date 08.05.2020 21:57
 * @author Patrikap
 */
class HmacServiceProvider extends ServiceProvider implements DeferrableProvider
{
    private const CONFIG_PATH = __DIR__ . '/../config/';
    private const CONFIG_NAME = 'hmac.php';

    /**
     *
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH . self::CONFIG_NAME => config_path(self::CONFIG_NAME),
            ], 'config');
            $this->commands([
                HmacGenerateCommand::class,
            ]);
        }
    }

    /** @inheritDoc */
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH . self::CONFIG_NAME, 'hmac');
        $this->app->singleton(HmacService::class);
    }

    /** @inheritDoc */
    public function provides(): array
    {
        return [HmacService::class];
    }

}
