# Usage

Require this library:
`composer require --dev lucasbustamante/db-dumper-for-codeception`

Add the command to your `codeception.yml`:
```
extensions:
    commands:
        - DumpGenerator\GenerateDump
```

Run `./vendor/bin/codecept dump <suite>`, where `<suite>` is a suite that has one `Db` module or one module that extends `Db`.

Add these parameters to the `Db` module on that suite:

```
modules:
    config:
        WPDb:
            dump_dsn: 'mysql:host=%DUMP_DB_HOST%;dbname=%DUMP_DB_NAME%'
            dump_user: '%DUMP_DB_USER%'
            dump_password: '%DUMP_DB_PASSWORD%'
```

Replace `%DUMP_DB_HOST%` and similars with your actual values.

In the first run, a Dump config file will be generated at `tests/data/dump.sql.php` where you can fine-tune the generation of your dump. On the second run it will generate the dump.

This library is basically a Codeception wrapper for https://packagist.org/packages/ifsnop/mysqldump-php. You can check their documentation to get more information on how exactly you can fine-tune your dump.
