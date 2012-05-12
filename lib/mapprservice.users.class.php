<?php

/**************************************************************************

File: mapprservice.users.class.php

Description: Produces a list of users

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/

require_once('../config/conf.php');
require_once('../config/conf.db.php');
require_once('db.class.php');
require_once('mapprservice.usersession.class.php');

class USERS {

  private $_uid;

  private $_request;

  private $_db;

  function __construct() {
    USERSESSION::select_language();
    $this->set_header()
         ->execute();
  }

  /*
  * Set header to prevent caching
  */
  private function set_header() {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    session_start();
    return $this;
  }

  /*
  * Utility method
  */
  private function execute() {
    if(!isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] !== 1) {
      header("Content-Type: application/json");
      echo '{ "error" : "access denied" }';
      exit;
    } else {
      $this->_uid = $_SESSION['simplemappr']['uid'];
      $this->_request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
      $this->_db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      $this->restful_action();
    }
  }

  /*
  * Detect type of request and perform appropriate method
  */
  private function restful_action() {
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
      case 'GET':
        $this->index_users();
      break;

      case 'POST':
      break;

      case 'DELETE':
        $this->destroy_user();
      break;

      default:
      break;
    }
  }

  /*
  * Index method to produce table of users
  */
  private function index_users() {
    $sql = "
      SELECT
        u.uid, u.username, u.email, u.access, count(m.mid) as num
      FROM
        users u
      LEFT JOIN
        maps m ON (u.uid = m.uid)
      GROUP BY
        u.username
      ORDER BY u.access DESC";

   $rows = $this->_db->query($sql);

   $output = "";

   if($this->_db->affected_rows > 0) {
     $output .= '<table class="grid-users">' . "\n";
     $output .= '<thead>' . "\n";
     $output .= '<tr>' . "\n";
     $output .= '<th class="left-align">'._("Username").'</th>';
     $output .= '<th class="left-align">'._("Email").'</th>';
     $output .= '<th>'._("Maps").'</th>';
     $output .= '<th>'._("Last Access").'</th>';
     $output .= '<th class="actions">'._("Actions").'<a href="#" class="sprites-after toolsRefresh"></a></th>';
     $output .= '</tr>' . "\n";
     $output .= '</thead>' . "\n";
     $output .= '<tbody>' . "\n";
     $i=0;
     while ($record = $this->_db->fetch_array($rows)) {
       $class = ($i % 2) ? 'class="even"' : 'class="odd"';
       $output .= '<tr '.$class.'>';
       $output .= '<td>'.stripslashes($record['username']).'</td>';
       $output .= '<td>'.stripslashes($record['email']).'</td>';
       $output .= '<td class="usermaps-number">'.$record['num'].'</td>';
       $access = ($record['access']) ? gmdate("M d, Y", $record['access']) : '-';
       $output .= '<td class="usermaps-center">'.$access.'</td>';
       $output .= '<td class="actions">';
       if($record['uid'] != 1) {
         $output .= '<a class="sprites-before user-delete" data-uid="'.$record['uid'].'" href="#">'._("Delete").'</a>';
       }
       $output .= '</td>';
       $output .= '</tr>' . "\n";
       $i++;
     }
     $output .= '</tbody>' . "\n";
     $output .= '</table>' . "\n";
     $output .= '<script type="text/javascript">
       $(".toolsRefresh", ".grid-users").click(function(){
         Mappr.loadUserList();
         return false;
       });
       </script>';
   }

   header("Content-Type: text/html");
   echo $output;
  }

  /*
  * Destroy method to delete a user
  */
  private function destroy_user() {
    $sql = "
        DELETE
          u, m
        FROM
          users u
        LEFT JOIN
          maps m ON u.uid = m.uid
        WHERE 
          u.uid=".$this->_db->escape($this->_request[0]);
    $this->_db->query($sql);

    header("Content-Type: application/json");
    echo '{"status":"ok"}';
  }

}

?>