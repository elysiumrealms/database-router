<?php

namespace Elysiumrealms\DatabaseRouter;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class DatabaseRouterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /**
         * Database Router for Eloquent
         * @example Admin::router()->first();
         * @example Admin::where('id', 1)->router()->first();
         * @example Admin::router()->where('id', 1)->first();
         */
        Builder::macro('router', function () {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $connection = $this->model->getConnection()->getName();
            if (in_array($connection, ['mysql', 'pgsql', 'sqlsrv'])) {
                $connection = "$connection-router";
                $this->query = $this->query->router();
                $this->model->setConnection($connection);
            }
            return $this;
        });
        /**
         * Database Router for Query Builder
         * @example DB::table('users')->router()->find(1);
         */
        QueryBuilder::macro('router', function () {
            /** @var \Illuminate\Database\Query\Builder $this */
            /** @var \Illuminate\Database\Connection $instance */
            $instance = $this->connection;
            $connection = $instance->getName();
            if (in_array($connection, ['mysql', 'pgsql', 'sqlsrv']))
                $instance = DB::connection("$connection-router");
            $this->connection = $instance;
            return $this;
        });

        $this->registerCommands();
    }

    /**
     * Register the SQLInterceptor Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }
    }
}
