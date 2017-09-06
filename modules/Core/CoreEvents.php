<?php

namespace MyTravel\Core;

class CoreEvents {
  const DBCONNECT = 'db.connect';
  const APPCONFIG = 'config.application';
  const DIRCONFIG = 'config.application.directories';
  const DBCONFIG = 'config.database';
  const BUILDROUTES = 'routing.routes.build';
  const RMFILES = 'files.removed';

}