<?php
// Please note: you haven't seen all the things you don't like to see.
// 
// Initialize application
require_once './modules/Core/Controller/App.php';

use MyTravel\Core\Controller\App;

$app = App::load();
// Autoloading.
// @todo one day setup composer then this is no longer needed.
// But we all like independency.
$app
  ->setAutoloader('psr-4', 'lib/ClassLoader/Psr4ClassLoader.php', 'Symfony\\Component\\ClassLoader\\Psr4ClassLoader')
  ->setAutoloadPrefixes('psr-4', 'addPrefix')
// Add Twig autoloader (psr-0)
  ->setAutoloader('psr-0', 'lib/ClassLoader/ClassLoader.php', 'Symfony\\Component\\ClassLoader\\ClassLoader')
  ->setAutoloadPrefixes('psr-0', 'addPrefix');
// Add prefixes
$app->addAutoloadPrefix('psr-4', 'Doctrine\\', 'lib/Doctrine/lib/Doctrine');
$app->addAutoloadPrefix('psr-4', 'Twig\\', 'lib/Twig/src');
$app->addAutoloadPrefix('psr-4', 'MatthiasMullie\Minify\\', 'lib/Minify/src');
$app->addAutoloadPrefix('psr-4', 'MatthiasMullie\PathConverter\\', 'lib/PathConverter/src');
$app->addAutoloadPrefix('psr-4', 'Patchwork\\', 'lib/JSqueeze');
// Oh my Twig what are you doing...
$app->addAutoloadPrefix('psr-0', 'Twig_', 'lib/Twig/lib');
// Register autoloaders
$app->registerAutoloader('psr-4', 'register', array(true));
$app->registerAutoloader('psr-0', 'register');

$app
  ->build()
  ->output();
