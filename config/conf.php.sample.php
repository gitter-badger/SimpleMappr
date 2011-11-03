<?php

// define the environment as 'development' or 'production'
define("ENVIRONMENT", "development");

// System root directory to the SimpleMappr application without a trailing slash
define("MAPPR_DIRECTORY", "/var/www/simplemappr");

// Number of textarea boxes for user data entry
define("NUMTEXTAREA", 3);

// Google Analytics UA-XXXXXX-XX key
define("GOOGLE_ANALYTICS", "UA-XXXXXX-XX");

// Google API key
define("GOOGLE_API", "xxxxxxxxxxxxx");

//set the locale
setlocale(LC_ALL, 'en_US.utf8');

//set the default timezone
date_default_timezone_set('America/New_York');

define("COOKIE_TIMEOUT", time() + (2 * 7 * 24 * 60 * 60)); //two week cookie lifetime
?>