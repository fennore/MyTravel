<?php
// Initialize application
require_once './modules/Core/Controller/App.php';

use MyTravel\Core\Controller\App;

$app = App::load();
// Autoloading
// @todo one day setup composer then this is no longer needed
$app
  ->setAutoloader('lib/ClassLoader/Psr4ClassLoader.php', 'Symfony\\Component\\ClassLoader\\Psr4ClassLoader')
  ->setAutoloadPrefixes('addPrefix')
  ->registerAutoloader('register', array(true));

$app
  ->build()
  ->output();
