<?php

/**************************************************************************

File: mapprservice.wfs.class.php

Description: Extends the base map class for SimpleMappr to support WFS. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  Marine Biological Laboratory

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

require_once ('../includes/mapprservice.class.php');

class MAPPRWFS extends MAPPR {

  /* the request object for WFS and WMS */ 
  private $_req = "";

  /* filter simplification */
  private $_filter_simplify;

  /* columns to filter on */ 
  private $_filter_columns = array();

  /**
  * Override the method in the MAPPR class
  */
  public function get_request() {
    $this->params['VERSION']      = $this->load_param('VERSION', '1.0.0');
    $this->params['REQUEST']      = $this->load_param('REQUEST', 'GetCapabilities');
    $this->params['TYPENAME']     = $this->load_param('TYPENAME', '');
    $this->params['MAXFEATURES']  = $this->load_param('MAXFEATURES', $this->get_max_features());
    $this->params['OUTPUTFORMAT'] = $this->load_param('OUTPUTFORMAT', 'gml2');
    $this->params['FILTER']       = $this->load_param('FILTER', null);

    $input = file_get_contents("php://input");
    if($input) {
      $xml = new XMLReader();
      $xml2 = new XMLReader();
      $xml->XML($input);
      while($xml->read()) {
        if($xml->name == 'wfs:Query') {
          $this->params['REQUEST'] = 'GetFeature';
          $this->params['TYPENAME'] = str_replace("feature:", "",    $xml->getAttribute('typeName'));
        }
        if($xml->name == 'ogc:Filter') {
	      $filter = $xml->readOuterXML();
          $this->params['REQUEST'] = 'GetFeature';
          $this->params['FILTER'] = $filter;
          $xml2->XML($filter);
          while($xml2->read()) {
	        if($xml2->name == 'ogc:PropertyName') {
		      $this->_filter_columns[$xml2->readString()] = $xml2->readString();
	        }
          }
          break;
        }
      }
    }

    $this->layers   = array('stateprovinces_polygon' => 'on');
    $this->bbox_map = $this->load_param('bbox', '-180,-90,180,90');
    $this->download = false;

    return $this;

  }

  /*
  * Set the simplification filter for a WFS request
  * @param integer
  */
  public function set_max_features($int) {
    $this->_filter_simplify = $int;
  }

  private function get_max_features() {
    return $this->_filter_simplify;
  }

  /**
  * Construct metadata for WFS
  */
  public function make_service() {
    $this->map_obj->setMetaData("name", "SimpleMappr Web Feature Service");
    $this->map_obj->setMetaData("wfs_title", "SimpleMappr Web Feature Service");
    $this->map_obj->setMetaData("wfs_onlineresource", "http://" . $_SERVER['HTTP_HOST'] . "/wfs/?");

    $srs_projections = implode(array_keys(MAPPR::$accepted_projections), " ");

    $this->map_obj->setMetaData("wfs_srs", $srs_projections);
    $this->map_obj->setMetaData("wfs_abstract", "SimpleMappr Web Feature Service");
        
    $this->map_obj->setMetaData("wfs_connectiontimeout", "60");

    $this->make_request();

    return $this;
  }

  private function make_request() {
    $this->_req = ms_newOwsRequestObj();
    $this->_req->setParameter("SERVICE", "wfs");
    $this->_req->setParameter("VERSION", $this->params['VERSION']);
    $this->_req->setParameter("REQUEST", $this->params['REQUEST']);

    $this->_req->setParameter('TYPENAME', 'stateprovinces_polygon');
    $this->_req->setParameter('MAXFEATURES', $this->params['MAXFEATURES']);
    if($this->params['REQUEST'] != 'DescribeFeatureType') $this->_req->setParameter('OUTPUTFORMAT', $this->params['OUTPUTFORMAT']);
    if($this->params['FILTER']) $this->_req->setParameter('FILTER', $this->params['FILTER']);

    return $this;
  }

  /**
  * Produce the  final output
  */
  public function get_output() {
    ms_ioinstallstdouttobuffer();
    $this->map_obj->owsDispatch($this->_req);
    $contenttype = ms_iostripstdoutbuffercontenttype();
    $buffer = ms_iogetstdoutbufferstring();
    header('Content-type: application/xml');
    echo $buffer;
    ms_ioresethandlers();
  }

}
  

?>