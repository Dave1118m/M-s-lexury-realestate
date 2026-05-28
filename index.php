<?php
// Root site entry point for the Hawassa real estate project.
// The public front end is stored under the `outside/` folder, and its includes are expected to resolve from the project root.
chdir(__DIR__);
require_once __DIR__ . '/outside/index.php';
