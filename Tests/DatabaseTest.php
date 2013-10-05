<?php
abstract class DatabaseTest extends PHPUnit_Framework_TestCase {
  protected static $db;

  public static function setUpBeforeClass() {
    self::$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

    $maps_table = 'CREATE TABLE IF NOT EXISTS `maps` (
      `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `title` varchar(255) CHARACTER SET latin1 NOT NULL,
      `map` longtext CHARACTER SET utf8 COLLATE utf8_bin,
      `created` int(11) NOT NULL,
      `updated` int(11) DEFAULT NULL,
      PRIMARY KEY (`mid`),
      KEY `uid` (`uid`),
      KEY `title` (`title`),
      KEY `idx_created` (`created`),
      KEY `idx_updated` (`updated`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

    $users_table = 'CREATE TABLE IF NOT EXISTS `users` (
      `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `identifier` varchar(255) NOT NULL,
      `username` varchar(50) DEFAULT NULL,
      `givenname` varchar(50) DEFAULT NULL,
      `surname` varchar(100) DEFAULT NULL,
      `email` varchar(50) DEFAULT NULL,
      `role` int(11) DEFAULT 1,
      `created` int(11) DEFAULT NULL,
      `access` int(11) DEFAULT NULL,
      PRIMARY KEY (`uid`),
      KEY `identifier` (`identifier`),
      KEY `idx_username` (`username`),
      KEY `idx_access` (`access`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

    self::$db->query($maps_table);
    self::$db->query($users_table);
    
    $user = self::$db->query_insert('users', array(
      'identifier' => 1,
      'username' => 'admin',
      'givenname' => 'Joe',
      'surname' => 'Smith',
      'email' => 'nowhere@example.com',
      'role' => 2
    ));
    
    self::$db->query_insert('maps', array(
      'uid' => $user,
      'title' => 'Sample Map',
      'map' => '{}'
    ));
  }

  public static function tearDownAfterClass() {
    self::$db->query("DROP TABLE maps");
    self::$db->query("DROP TABLE users");
    self::$db = NULL;
  }

  public function testContent() {
    $map = self::$db->query_first("SELECT * FROM maps WHERE uid = 1");
    $this->assertEquals($map['title'], 'Sample Map');
  }

}
?>