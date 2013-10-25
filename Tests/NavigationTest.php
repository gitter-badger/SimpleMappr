<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */
 
class NavigationTest extends PHPUnit_Extensions_Selenium2TestCase {

  protected $app_url;

  public function setUp() {
    $this->app_url = "http://" . MAPPR_DOMAIN . "/";
    $this->setBrowser('firefox');
    $this->setBrowserUrl($this->app_url);
  }

  public function setUpPage() {
    $this->url($this->app_url);
  }

  public function testTranslation() {
    $link = $this->byLinkText('Français');
    $link->click();
    $tagline = $this->byId('site-tagline');
    $this->assertEquals('cartes point pour la publication et présentation', $tagline->text());
  }

  public function testSignInPage() {
    $link = $this->byLinkText('Sign In');
    $link->click();
    $tagline = $this->byId('map-mymaps');
    $this->assertContains('Save and reload your map data or create a generic template.', $tagline->text());
  }

  public function testAPIPage() {
    $link = $this->byLinkText('API');
    $link->click();
    sleep(1);
    $content = $this->byId('general-api');
    $this->assertContains('A simple, restful API may be used with Internet accessible', $content->text());
  }

  public function testAboutPage() {
    $link = $this->byLinkText('About');
    $link->click();
    sleep(1);
    $content = $this->byId('general-about');
    $this->assertContains('Create greyscale point maps suitable for reproduction on print media', $content->text());
  }

  public function testHelpPage() {
    $link = $this->byLinkText('Help');
    $link->click();
    sleep(1);
    $content = $this->byId('map-help');
    $this->assertContains('This application makes heavy use of JavaScript.', $content->text());
  }
  
  public function testSessionPage() {
    $cookie = $this->setCookie('davidpshorthouse', 1, 'fr_FR');
    $this->url($this->app_url);
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->assertEquals($this->byId('site-user')->text(), 'davidpshorthouse');
    $this->assertEquals($this->byId('site-session')->text(), 'Déconnectez');
    $link = $this->byLinkText('Mes cartes');
    $link->click();
    sleep(1);
    $content = $this->byId('mymaps');
    $this->assertContains('Alternativement, vous pouvez créer et enregistrer un modèle générique sans points de données', $content->text());
  }

  private function setCookie($username, $role, $locale) {
    $cookie = urlencode('{"identifier":"none","username":"' . $username . '","email":"' . $username .  '@gmail.com","locale":"' . $locale . '","role":"' . $role . '"}');
    $cookies = $this->cookie();
    $cookies->add('simplemappr', $cookie)
            ->path('/')
            ->domain(MAPPR_DOMAIN)
            ->set();
    return $cookie;
  }
}

?>