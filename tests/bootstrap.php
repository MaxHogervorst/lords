<?php

/*
|--------------------------------------------------------------------------
| Set Testing Environment
|--------------------------------------------------------------------------
|
| Ensure APP_ENV is set to 'testing' before Laravel bootstraps.
| This allows Laravel's automatic CSRF bypass and .env.testing loading.
|
*/

$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');
