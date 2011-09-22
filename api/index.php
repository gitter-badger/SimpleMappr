<?php
require_once('../config/conf.php');
require_once('../lib/mapprservice.api.class.php');

$mappr_api = new MAPPRAPI();
$mappr_api->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
          ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/font/fonts.list")
          ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
          ->set_tmp_url("/tmp");

$mappr_api->get_request()
          ->execute()
          ->get_output();
?>