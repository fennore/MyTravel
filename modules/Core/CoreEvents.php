<?php

namespace MyTravel\Core;

class CoreEvents {
  const DBCONNECT = 'module.service.db.connect';
  const APPCONFIG = 'module.config.application';
  const DIRCONFIG = 'module.config.application.directories';
  const DBCONFIG = 'module.config.database';
  const BUILDROUTES = 'module.routing.routes.build';

}