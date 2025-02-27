<?php

namespace Pollora\Colt\Laravel;

use Pollora\Colt\Colt;
use Pollora\Colt\Laravel\Auth\AuthUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\ShortcodeFacade;

/**
 * Class ColtServiceProvider
 *
 * @package Pollora\Colt\Providers\Laravel
 * @author Mickael Burguet <www.rundef.com>
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class ColtServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->publishConfigFile();
        $this->registerAuthProvider();
    }

    /**
     * @return void
     */
    private function publishConfigFile()
    {
        $this->publishes([
            __DIR__ . '/config.php' => base_path('config/colt.php'),
        ]);
    }

    /**
     * @return void
     */
    private function registerAuthProvider()
    {
        Auth::provider('colt', function ($app, array $config) {
            return new AuthUserProvider($config);
        });
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->app->bind(ShortcodeFacade::class, function () {
            return tap(new ShortcodeFacade(), function (ShortcodeFacade $facade) {
                $parser_class = config('colt.shortcode_parser', RegularParser::class);
                $facade->setParser(new $parser_class);
            });
        });
    }
}
