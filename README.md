# Database Router

## Description

Database Router is an extension for databases using master-slave architecture. This extension ensures that the default connection uses a read-write capable database host and extends an optional router connection to partially apply read-write isolation strategies based on business logic.

## Installation

1. Install `elysiumrealms/database-router`.

    ```bash
    composer require elysiumrealms/database-router
    ```

2. Configure `config/database.php` and `config/app.php`.

    ```bash
    php artisan db:router:install
    ```

## Read from `DB_READ_HOST`

To perform read operations using the read host, you can use the following methods:

```php
$model = Model::router()->first();
```

```php
$model = Admin::where('id', 1)->router()->first();
```

```php
$model = Admin::router()->where('id', 1)->first();
```

```php
DB::table('users')->router()->find(1);
```

## Update with `DB_WRITE_HOST`

To perform update operations using the write host, you can use the following methods:

```php
$model = Model::router()
    ->update(['name' => 'new-name']);
```

```php
$model = Admin::where('id', 1)->router()
    ->update(['name' => 'new-name']);
```

```php
$model = Admin::router()->where('id', 1)
    ->update(['name' => 'new-name']);
```

```php
DB::table('users')->router()
    ->update(['name' => 'new-name']);
```
