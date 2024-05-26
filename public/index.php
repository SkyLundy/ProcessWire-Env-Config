<?php namespace ProcessWire;

/**
 * This file is the entry point for the application
 * The ProcessWire index.php file is renamed to bootstrap.php and included here
 *
 * This allows for autoloading Composer using the proper directory location while not requiring any
 * modification to the ProcessWire index.php file.
 *
 * Any updates to the ProcessWire index.php file should be made in bootstrap.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/bootstrap.php';
