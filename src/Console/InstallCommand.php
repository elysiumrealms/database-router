<?php

namespace Elysiumrealms\DatabaseRouter\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:router:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Database router';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing DatabaseRouter Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'database-router-provider']);

        $this->registerConfig();
        $this->registerServiceProvider();

        $this->info('DatabaseRouter scaffolding installed successfully.');
    }

    /**
     * Register the database connection in the application configuration file.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $databaseConfig = file_get_contents(config_path('database.php'));

        if (Str::contains($databaseConfig, "return collect([" . PHP_EOL)) {
            return;
        }

        file_put_contents(config_path('database.php'), str_replace(
            "return [" . PHP_EOL,
            "return collect([" . PHP_EOL,
            str_replace(
                "];" . PHP_EOL,
                <<<EOL
                ])->mapWithKeys(function (\$config, \$key) {
                        switch (\$key) {
                            case 'connections':
                                \$config = collect(\$config)
                                    ->merge(collect(\$config)->filter(function (\$config, \$key) {
                                        return in_array(\$key, ['mysql', 'pgsql', 'sqlsrv']);
                                    })->mapWithKeys(function (\$config, \$key) {
                                        \$default = env('DB_HOST', 'localhost');
                                        return [ "\$key-router" => collect(\$config)
                                            ->merge([
                                                'read' => [
                                                    'host' => env('DB_HOST_READ', \$default),
                                                ],
                                                'write' => [
                                                    'host' => env('DB_HOST_WRITE', \$default),
                                                ],
                                            ])->toArray() ];
                                    }));
                                break;
                        }
                        return [ \$key => \$config ];
                    })->toArray();
                EOL,
                $databaseConfig
            )
        ));
    }

    /**
     * Register the SQLInterceptor service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerServiceProvider()
    {
        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, 'Elysiumrealms\\DatabaseRouter\\DatabaseRouterServiceProvider::class')) {
            return;
        }

        file_put_contents(config_path('app.php'), str_replace(
            "        /*" . PHP_EOL .
                "         * Package Service Providers..." . PHP_EOL .
                "         */",
            "        /*" . PHP_EOL .
                "         * Package Service Providers..." . PHP_EOL .
                "         */" . PHP_EOL .
                "        Elysiumrealms\\DatabaseRouter\\DatabaseRouterServiceProvider::class,",
            $appConfig
        ));
    }
}
