<?php namespace ProcessWire;

use App\Env\Env;

/**
 * ProcessWire Configuration File
 */

if(!defined("PROCESSWIRE")) die();

// the load() method returns an object with a memoized store of your .env file accessible using
// the get() method
$env = Env::load();

// You can make env available throughout application by assigning $env to a property
// Access values with $config->env->get('NAME_OF_VARIABLE');
$config->env = $env;

/** @var Config $config */

/*** SITE CONFIG *************************************************************************/

// Let core API vars also be functions? So you can use $page or page(), for example.
$config->useFunctionsAPI = $env->get('USE_FUNCTIONS_API');

// Use custom Page classes in /site/classes/ ? (i.e. template "home" => HomePage.php)
$config->usePageClasses = $env->get('USE_PAGE_CLASSES');

// Use Markup Regions? (https://processwire.com/docs/front-end/output/markup-regions/)
$config->useMarkupRegions = $env->get('USE_MARKUP_REGIONS');

// Prepend this file in /site/templates/ to any rendered template files
$config->prependTemplateFile = $env->get('PREPEND_TEMPLATE_FILE');

// Append this file in /site/templates/ to any rendered template files
$config->appendTemplateFile = $env->get('APPEND_TEMPLATE_FILE');

// Allow template files to be compiled for backwards compatibility?
$config->templateCompile = $env->get('TEMPLATE_COMPILE');

/*** INSTALLER CONFIG ********************************************************************/
/**
 * Installer: Database Configuration
 *
 */
$config->dbHost = $env->get('DB_HOST');
$config->dbName = $env->get('DB_NAME');
$config->dbUser = $env->get('DB_USER');
$config->dbPass = $env->get('DB_PASS');
$config->dbPort = $env->get('DB_PORT');
$config->dbEngine = $env->get('DB_ENGINE');

/**
 * Installer: User Authentication Salt
 *
 * This value was randomly generated for your system on 2024/05/25.
 * This should be kept as private as a password and never stored in the database.
 * Must be retained if you migrate your site from one server to another.
 * Do not change this value, or user passwords will no longer work.
 *
 */
$config->userAuthSalt = $env->get('USER_AUTH_SALT');

/**
 * Installer: Table Salt (General Purpose)
 *
 * Use this rather than userAuthSalt when a hashing salt is needed for non user
 * authentication purposes. Like with userAuthSalt, you should never change
 * this value or it may break internal system comparisons that use it.
 *
 */
$config->tableSalt = $env->get('TABLE_SALT');

/**
 * Installer: File Permission Configuration
 *
 */
$config->chmodDir = $env->get('CHMOD_DIRE');
$config->chmodFile = $env->get('CHMOD_FILE');

/**
 * Installer: Time zone setting
 *
 */
$config->timezone = $env->get('TIMEZONE');

/**
 * Installer: Admin theme
 *
 */
$config->defaultAdminTheme = $env->get('DEFAULT_ADMIN_THEME');

/**
 * Installer: Unix timestamp of date/time installed
 *
 * This is used to detect which when certain behaviors must be backwards compatible.
 * Please leave this value as-is.
 *
 */
$config->installed = $env->get('INSTALLED');


/**
 * Installer: HTTP Hosts Whitelist
 *
 */
$config->httpHosts = ['yourdomain.com'];


/**
 * Installer: Debug mode?
 *
 * When debug mode is true, errors and exceptions are visible.
 * When false, they are not visible except to superuser and in logs.
 * Should be true for development sites and false for live/production sites.
 *
 */
$config->debug = $env->get('DEBUG');

