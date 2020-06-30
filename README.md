# Usage

`composer require --dev lucasbustamante/db-dumper-for-codeception`

Add the command to your `codeception.yml`:
```
extensions:
    commands:
        - DumpGenerator\GenerateDump
```

On a suite that uses a `Codeception\Modules\Db` module (or any module that extends `Db`), add these parameters:

```
modules:
    config:
        Db:
            dump_dsn: 'mysql:host=%DUMP_DB_HOST%;dbname=%DUMP_DB_NAME%'
            dump_user: '%DUMP_DB_USER%'
            dump_password: '%DUMP_DB_PASSWORD%'
```

Replace `%DUMP_DB_HOST%` and similars with your actual values. This should be your local development database, from which the dumps will be generated from.

Run `./vendor/bin/codecept dump <suite>`

In the first run, a Dump config file will be generated at `tests/data/<dump>.sql.php` where you can fine-tune how your dump will be generated. On the subsequent runs, it will just generate the dump.

This library is basically a Codeception wrapper around https://packagist.org/packages/ifsnop/mysqldump-php.

You can check their documentation to get more information on how exactly you can fine-tune your dump.
