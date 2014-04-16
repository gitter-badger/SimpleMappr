<?php
  
function switchConf($restore = false) {
  $config_dir = dirname(dirname(__FILE__)) . '/config/';

  $conf = array(
    'prod' => $config_dir . 'conf.php',
    'test' => $config_dir . 'conf.test.php'
  );
  $db = array(
    'prod' => $config_dir . 'conf.db.php',
    'test' => $config_dir . 'conf.db.test.php'
  );

  if(!$restore) {
    if(!file_exists($conf['prod'] . ".old")) {
      if(file_exists($conf['prod'])) { copy($conf['prod'], $conf['prod'] . ".old"); }
      copy($conf['test'], $conf['prod']);
      if(file_exists($db['prod'])) { copy($db['prod'], $db['prod'] . ".old"); }
      copy($db['test'], $db['prod']);
    }
  } else {
    if(file_exists($conf['prod'] . ".old")) { rename($conf['prod'] . ".old", $conf['prod']); }
    if(file_exists($db['prod'] . ".old")) { rename($db['prod'] . ".old", $db['prod']); }
  }

}

function requireFiles() {
  $root = dirname(dirname(__FILE__));
  $files = glob($root . '/lib/*.php');
  foreach ($files as $file) {
    require_once($file);
  }

  require_once($root . '/Tests/SimpleMapprTest.php');
  require_once($root . '/Tests/php-webdriver/lib/__init__.php');
}

function trashCachedFiles() {
  $root = dirname(dirname(__FILE__));
  $cssFiles = glob($root . "/public/stylesheets/cache/*.{css}", GLOB_BRACE);
  foreach ($cssFiles as $file) {
    unlink($file);
  }
  $jsFiles = glob($root . "/public/javascript/cache/*.{js}", GLOB_BRACE);
  foreach ($jsFiles as $file) {
    unlink($file);
  }
  $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
  foreach ($tmpfiles as $file) {
    unlink($file);
  }
}

function loader() {
  date_default_timezone_set("America/New_York");
  switchConf();
  requireFiles();
  Header::flush_cache(false);
  ob_start();
  new Header;
}

function unloader() {
  switchConf('restore');
  trashCachedFiles();
  Header::flush_cache(false);
  ob_end_clean();
}

spl_autoload_register('loader');
register_shutdown_function('unloader');