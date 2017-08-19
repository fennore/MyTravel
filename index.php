<?php

// Initialize application
require_once './modules/Core/Controller/App.php';

use MyTravel\Core\Controller\App;

$app = App::load();
// Autoloading
// @todo one day setup composer then this is no longer needed
$app
  ->setAutoloader('psr-4', 'lib/ClassLoader/Psr4ClassLoader.php', 'Symfony\\Component\\ClassLoader\\Psr4ClassLoader')
  ->setAutoloadPrefixes('psr-4', 'addPrefix')
  ->setAutoloader('psr-0', 'lib/ClassLoader/ClassLoader.php', 'Symfony\\Component\\ClassLoader\\ClassLoader')
  ->setAutoloadPrefixes('psr-0', 'addPrefix');
// Add Twig autoloader
$app->addAutoloadPrefix('psr-4', 'Twig\\', 'lib/Twig/src');
// Oh my Twig what are you doing...
$app->addAutoloadPrefix('psr-0', 'Twig_', 'lib/Twig/lib');
// Register autoloader
$app->registerAutoloader('psr-4', 'register', array(true));
$app->registerAutoloader('psr-0', 'register');

$app
  ->build()
  ->output();
