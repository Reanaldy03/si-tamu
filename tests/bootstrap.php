<?php

/*
|--------------------------------------------------------------------------
| PHPUnit Bootstrap
|--------------------------------------------------------------------------
|
| This workstation's shell environment exports the whole contents of `.env`
| (`APP_ENV=local`, `DB_CONNECTION=mysql`, `SESSION_DRIVER=database`, ...), so
| every child process inherits them through the OS environment.
|
| PHPUnit's `<env>` entries only call `putenv()` and write `$_ENV`; they never
| touch `$_SERVER` (see `PHPUnit\TextUI\Configuration\PhpHandler`). Laravel's
| env repository reads `$_SERVER` first (phpdotenv's `ServerConstAdapter`), so
| without the mirroring below the leaked `$_SERVER` values win over
| `phpunit.xml`:
|
|   1. `APP_ENV` stays `local` -> `VerifyCsrfToken` stops skipping CSRF in
|      tests -> every POST/PUT/DELETE answers 419.
|   2. `DB_CONNECTION` stays `mysql` -> `RefreshDatabase` migrates and then
|      wipes the real development database instead of in-memory SQLite.
|
| Mirroring the (already forced) `$_ENV` values onto `$_SERVER` makes
| `phpunit.xml` authoritative regardless of the ambient shell environment.
|
*/

require __DIR__.'/../vendor/autoload.php';

foreach ($_ENV as $name => $value) {
    if (is_string($name) && is_string($value)) {
        $_SERVER[$name] = $value;
    }
}
