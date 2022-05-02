<?php

namespace Milkcan\Whitelabel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Milkcan\Whitelabel\Commands\MapDomain;
use Milkcan\Whitelabel\Models\WhitelabelDomain;
use Milkcan\Whitelabel\Commands\AddDomain;
use Milkcan\Whitelabel\Commands\AddFlavor;
use Milkcan\Whitelabel\Commands\WhitelabelInstall;
use Milkcan\Whitelabel\Helpers\DomainHelper;


class WhitelabelServiceProvider extends ServiceProvider
{
    public function boot(){
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                WhitelabelInstall::class,
                AddFlavor::class,
                AddDomain::class,
                MapDomain::class
            ]);
        }
        $this->publishes([
            realpath(__DIR__.'/database/migrations')
            => $this->app->databasePath().'/migrations',
        ]);

        Blade::directive('flavor',function ($expression){
            $helper = new DomainHelper();
            $domain = $helper->base(Request::root());
            $whiteLabel = WhitelabelDomain::select('folder')->where('domain','like','%'.$domain.'%')->get()->first();
            $text = config('flavor.' . $whiteLabel->folder . '.' . $expression);
            return "<?php echo ('$text') ?>";
        });

    }

    public function  register()
    {

    }
}
