# ProcessWire Env Config
A wrapper and enhancement utility for using [phpdotenv](https://github.com/vlucas/phpdotenv) and .env files with ProcessWire.

## Why?

Software and website development often requires working with sensitive data that may or may not be tied to the current environment that the application is running in. The `.env` file is a standard file used to keep private and sensitive values available at runtime securely. Servers like Apache will not serve .env files and they may be located anywhere, including outside of the root directory where the application is served.

From the phpdotenv repository:

> You should never store sensitive credentials in your code. Storing configuration in the environment is one of the tenets of a twelve-factor app. Anything that is likely to change between deployment environments – such as database credentials or credentials for 3rd party services – should be extracted from the code into environment variables.

## What?

This Env utility provides .env caching and parsing, as well as some additional features like pushing .env values straight to the ProcessWire `$config` object. It also has methods for checking if env variables do or do not exist, conditionally loading values, and loading environment values with fallbacks. Env will also cast boolean and integer strings to their respective types.

Caching `.env` files is highly beneficial and is standard practice in frameworks such as Laravel. Parsing `.env ` can be an expensive task and caching the output is [highly recommended by the phpdotenv package maintainers](https://github.com/vlucas/phpdotenv/issues/549). So while using `.env` files is important, Env makes it performant. This is done by caching the values read from `.env` to a PHP file as an array that is quickly accessed at runtime. This cache file is automatically regenerated when any changes are made to the `.env` file itself.

**Important:** It is critical that you exclude both the `.env` file and the generated cache file from your Github repository. Any sensitive information that has ever been committed to a repository should always be considered compromised.

## Installation

1. Copy the Env folder to the location of your choice.

2. Add the Env PSR4 autoloading entry to `composer.json` with the correct path to the Env folder.

```php
"autoload": {
  "files": [ "public/wire/core/ProcessWire.php" ],
  "psr-4": {
      "Env\\": "Env/"
  }
},
````

3. Install phpdotenv using Composer

```bash
$ composer require vlucas/phpdotenv
```

4. If you are using Git, add your `.env` file and the `Env/cache/*` directory to your `.gitignore` file.

5. Create your `.env` file and add values.

## Basic Usage

The best place to use this utility is in `/site/config.php`. Here's an example config file that uses this utility:

```php
<?php namespace ProcessWire;

use Env\Env;

if(!defined("PROCESSWIRE")) die();

// The load() method returns an object with cached .env variables/values
// The first argument must be a resolvable path to your .env file. This Example locates a file in the root directory
$env = Env::load(__DIR__ . '/../');

// You can optionally make env available globally in ProcessWire by assigning $env to a property
// This allows $config->env->get('NAME_OF_VARIABLE'); to be accessed anywhere in your ProcessWire application
$config->env = $env;

// Use $env->get('KEY'); to assign $config property values
$config->debug = $env->get('DEBUG');

// You may pass a second argument that will be the default value if the environment variable does not exist
// NOTE: If exceptions are enabled, the fallback value will be ignored and an exception still thrown
$config->debug = $env->get('DEBUG', false);
````

### Pushing Environment Variables To The ProcessWire Config Object

For fans of brevity, you can push values directly to the ProcessWire config object using an associative array.

```php
<?php namespace ProcessWire;

use Env\Env;

if(!defined("PROCESSWIRE")) die();

$env = Env::load(__DIR__ . '/../');

$config = $env->pushToConfig($config, [
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
  'debug' => ['DEBUG', false], // Use an array to specify a fallback value
]);

// Arrays aren't for .env files...
$config->httpHosts = ['yourdomain.com', 'anotherdomain.com'];

```

### Initialization Options

This Env utility contains some additional features that make working with variables and values easier. Here is the `load()` method with all parameters and their default values.

```php
<?php

Env::load(
    envLocation: null,
    createEnvVars: false,
    importGlobalVars: false,
    castBools: true,
    castInts: true,
    exceptionOnMissing: false,
);
```
- `$envLocation` - The path to your `.env` file
- `$createGlobalVars` - Create environment variables from your .env file accessible via the `$_ENV` global. **Warning:** the values in your `.env` file will overwrite any existing if keys match
- `$importGlobalVars` - Imports all of the global `$_ENV` vars to the Env object. **Warning:** the values in your `.env` file will overwrite any existing if keys match
- `$castBools` - Cast env string values of 'true' and 'false' to boolean `true` and `false` respectively
- `$castInts` - Cast env string integer values to integers
- `$exceptionOnMissing` - Passing `true` for this parameter means that attempting to access an environment variable that does not exist in the `.env` file will throw a `RuntimeException`. This may be helpful during development and debugging.

When importing global `$_ENV` variables to the Env object, values are not parsed or cast to booleans/integers if you have that enabled.

## Advanced Usage

Env provides some additional methods that may be helpful if you require conditional or more complex configurations. Most of the time you should be able to avoid using these in your `config.php` files by maintaining separate values in `.env` files but they may be useful if there are runtime conditions that change how ProcessWire should be configured.

```php
// Additional methods:
$env->exists('ENV_VAR');

$env->is('ENV_VAR', 'conditional value');
$env->eq('ENV_VAR', 'conditional value'); // Alias for eq()

$env->isNot('ENV_VAR', 'conditional value');
$env->notEq('ENV_VAR', 'conditional value'); // Alias for eq()

$env->if('ENV_VAR', 'conditional value', 'return if equals', 'return if does not equal');

$env->ifNot('ENV_VAR', 'conditional value', 'return if does not equal', 'return if does equal');
$env->ifNotEq('ENV_VAR', 'conditional value', 'return if does not equal', 'return if does equal'); // Alias for ifNot()

// Returns all data loaded by Env as an array
$env->getArray();

// Returns all data loaded by Env as a stdClass object
$env->getObject();
````

These additional methods may be most useful when you assign the instantiated `$env` object to the `$config->env` property as noted above. It then becomes possible to easily use environment variables in your templates and application logic.

Output something conditionally based on the environment:
```php
if ($config->env->is('APP_ENV', 'development')) {
  // Do something only intended for development environments
}

if ($config->env->isNot('APP_ENV', 'production')) {
  // Do something that may execute for local development, staging, etc.
}
```

Check critical values at runtime and handle cases gracefully:
```php
if (!$config->env->exists('GOOGLE_API_KEY')) {
  $wire->error('A Google API key could not be loaded from the environment file.', Notice::noGroup);
}
```

Work with env values directly in your code
```php
try {
  // Do something dangerous
} catch (Exception $e) {
    $message = $config->env->if('APP_ENV', 'production', 'Oh no. Friendly message', $e->getMessage());
}
```

Note: These methods only work with data loaded via Env. Environment variables

## Details on Loading & Parsing

When the `.env` file is parsed, values are read in as strings. This may cause issues in the case of boolean and integer values. When loading and caching, an attempt is made to detect these values and cast them where appropriate. As documented above, this may be disabled when loading your `.env` file.

Values that are cast to booleans automatically: 'true', 'false', Integer strings such as '0' and '1' are not cast to booleans

Strings are cast to integers _only_ if every character in the string is an integer.
