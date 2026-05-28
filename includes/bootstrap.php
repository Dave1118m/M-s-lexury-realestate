<?php
/**
 * Bootstrap – central include hub
 * Loads config, database, functions, and auth in the correct order.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
