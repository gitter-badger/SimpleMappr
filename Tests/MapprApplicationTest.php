<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml
 */

require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
 
class MapprApplicationTest extends WebTestCase {

  protected $app_url;

  public function setUp() {
    $this->app_url = "http://" . MAPPR_DOMAIN . "/application";
  }

  public function tearDown() {
    $root = dirname(dirname(__FILE__));
    $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

  public function test_get_json_response() {
    echo "----> Testing GET on " . $this->app_url . "\n";
    echo "Response:" . "\n";
    echo $this->get($this->app_url) . "\n\n";
    $this->assertMime("application/json");
  }

  public function test_post_json_response() {
    echo "----> Testing POST on " . $this->app_url . "\n";
    echo "Response:" . "\n";
    echo $this->post($this->app_url, array()) . "\n\n";
    $this->assertMime("application/json");
  }

  public function test_width() {
    $post = array('width' => '300');
    echo "----> Testing POST on " . $this->app_url . " with " . json_encode($post) . "\n";
    $response = $this->post($this->app_url, $post);
    $json = json_decode($response);
    $this->assertEqual($json->size[0], 300);
    $this->assertEqual($json->size[1], 150);
  }
}

?>