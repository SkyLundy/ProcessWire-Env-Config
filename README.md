# ProcessWire Env Config
A strategy for implementing .env config files in ProcessWire with supporting application.

This is a mock ProcessWire install shows an modified folder structure and files common to ProcessWire applications that moves public files and folders into a `public` directory. This creates a more secure root directory for assets that should not be served or accessed.

## File Structure
This assumes that the directories are restructured as described in [this article](https://processwire.dev/integrate-composer-with-processwire/#recommended-directory-structure-for-processwire-projects-with-composer), with some notable differences:

- In `public/` the default ProcessWire `index.php` is renamed to `bootstrap.php`
- The `public/index.php` file now imports Composer's `autoload.php` and `bootstrap.php`

This change is to both ensure that Composer is loaded globally first, and prevent any modification to ProcessWire's `index.php` file contents. I also find it preferable to initializing Composer further in the application, such as within `init.php`.

The only new requirement in maintaining this configuration is that any required updates to the ProcessWire `index.php` must now be made to `bootstrap.php` instead.

## Installation

1. Modify your `composer.json` file with the PSR4 autoloading entry

2. Install phpdotenv using Composer

```bash
$ composer require vlucas/phpdotenv
```

3. Create your `.env` file, example provided.

4. **IMPORTANT** Modify your `.gitignore` file with the entries located in the example file included here

## Usage

The best place to use this application is in `/public/site/config.php`. Here's an example config file that uses this strategy:

```php
<?php namespace ProcessWire;

use App\Env\Env;

if(!defined("PROCESSWIRE")) die();

// The load() method returns an object with a memoized store of cached .env keys and values
$env = Env::load();

// You can optionally make env available globally in ProcessWire by assigning $env to a property
// This allows $config->env->get('NAME_OF_VARIABLE');
$config->env = $env;

// Use $env->get('KEY'); to assign $config property values
$config->debug = $env->get('DEBUG');
````

Attempting to read an environment variable that does not exist will result in a `RuntimeException`

For fans of brevity, you can push your values straight to the ProcessWire config object.

```php
<?php namespace ProcessWire;

use App\Env\Env;

if(!defined("PROCESSWIRE")) die();

$env = Env::load();

$env->pushToConfig($config, [
  'useFunctionsAPI' => 'USE_FUNCTIONS_API',
  'usePageClasses' => 'USE_PAGE_CLASSES',
  'useMarkupRegions' => 'USE_MARKUP_REGIONS',
  'prependTemplateFile' => 'PREPEND_TEMPLATE_FILE',
  'appendTemplateFile' => 'APPEND_TEMPLATE_FILE',
  'templateCompile' => 'TEMPLATE_COMPILE',
  'dbHost' => 'DB_HOST',
  'dbName' => 'DB_NAME',
  'dbUser' => 'DB_USER',
  'dbPass' => 'DB_PASS',
  'dbPort' => 'DB_PORT',
  'dbEngine' => 'DB_ENGINE',
  'userAuthSalt' => 'USER_AUTH_SALT',
  'tableSalt' => 'TABLE_SALT',
  'chmodDir' => 'CHMOD_DIR',
  'chmodFile' => 'CHMOD_FILE',
  'timezone' => 'TIMEZONE',
  'defaultAdminTheme' => 'DEFAULT_ADMIN_THEME',
  'installed' => 'INSTALLED',
  'debug' => 'DEBUG',
]);

// Arrays aren't for .env files...
$config->httpHosts = ['yourdomain.com'];

```

## Details on Loading & Parsing

When the `.env` file is parsed, values are read in as strings. This may cause issues in the case of boolean and integer values. When loading and caching, an attempt is made to detect these values and cast them where appropriate. This may or may not be desired, so this behavior can be disabled or modified where necessary.

Values that are cast to booleans automatically: `'0', '1', 'true', 'false'`

Strings are cast to integers _only_ if every character in the string is an integer.

Options for casting:

```php

// Disable casting
$env->load(
  castBools: false,
  castInts: false
);

// Modify boolean casting, in this example only 'true' and 'false' are cast, '0' and '1' are left
// untouched as strings. An empty array will disable boolean casting.
$env->load(
  castBools: ['true', 'false']
);

```

### Using `$_ENV`

The method above is satisfactory for most ProcessWire applications, however it does not add your configs to the actual server `$_ENV` array by default. This is because ProcessWire has a global `$config` object and much of the actual configuration is done in `config.php` which works for most. By not loading into `$_ENV` we save some time and skip a potentially unnecessary step where it's not required.

If you would like to have keys/values accessible via `$_ENV` just pass `true` when initially calling the `load()` method.

```php
$env = Env::load(true);

// Or as a named parameter
$env = Env::load(createEnvVars: true);

// Now this works globally in any directory inside /public or otherwise
$foo = $_ENV['FOO'];
```

The `$env` object will still function as described above.

## Clearing The Cacahe

Because values are cached, changes to the `.env` file require that the cache be cleared. This can be done via the `clearCache()` method.

```php
$env = Env::load();

$env->clearCache();
```

## How It Works

An attempt to load values from the cached file are made first, if that fails then the `.env` file is parsed and the values that are found are cached as an array in a PHP file. On first load, the values in that file are read using PHP's native `require_once` directive which is fast and efficient. Using this method, there is no actual parsing that is required after caching and the values are loaded into a memoized object that can be used without reloading from the cached file.

This is the same approach that Laravel uses when working with .env files. By "the same approach", I mean [line 65](https://github.com/laravel/framework/blob/e2d55af66635941d931b8e89af17553625c9699d/src/Illuminate/Foundation/Console/ConfigCacheCommand.php#L65) of Laravel's `ConfigCacheCommand.php` (thanks, Taylor!).


