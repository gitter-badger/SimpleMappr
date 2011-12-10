<?php

/**************************************************************************

File: mapprservice.class.php

Description: Base map class for SimpleMappr. 

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

class MAPPR {

  /* the base map object */
  public $map_obj;

  /* path to shapefiles */
  public $shape_path;

  /* path to the font file */
  public $font_file;

  /* file system temp path to store files produced */ 
  public $tmp_path = '/tmp';

  /* url temp path to retrieve files produced */
  public $tmp_url = '/tmp';

  /* default extent when map first loaded */
  public $max_extent = array(-180,-90,180,90);

  /* default projection when map first loaded */
  public $default_projection = 'epsg:4326';

  /* base image size in pixels (length, height) */
  public $image_size = array(800,400);

  public $image;

  public $scale;

  public $legend;

  /* shapes and their mapfile configurations */
  public $shapes = array();

  /* Initial mapfile as string because outputformat cannot otherwise be set */
  public $mapfile_string = "
      MAP

      OUTPUTFORMAT
        NAME png
        DRIVER AGG/PNG
        IMAGEMODE RGB
        MIMETYPE 'image/png'
        EXTENSION 'png'
        FORMATOPTION 'INTERLACE=OFF'
        FORMATOPTION 'COMPRESSION=9'
        FORMATOPTION 'QUANTIZE_FORCE=ON'
        FORMATOPTION 'QUANTIZE_DITHER=OFF'
        FORMATOPTION 'QUANTIZE_COLORS=256'
      END

      OUTPUTFORMAT
        NAME png_download
        DRIVER AGG/PNG
        IMAGEMODE RGB
        MIMETYPE 'image/png'
        EXTENSION 'png'
        FORMATOPTION 'INTERLACE=OFF'
        FORMATOPTION 'COMPRESSION=9'
        FORMATOPTION 'QUANTIZE_FORCE=ON'
        FORMATOPTION 'QUANTIZE_DITHER=OFF'
        FORMATOPTION 'QUANTIZE_COLORS=256'
      END

      OUTPUTFORMAT
        NAME pnga_download
        DRIVER AGG/PNG
        IMAGEMODE RGB
        MIMETYPE 'image/png'
        EXTENSION 'png'
        FORMATOPTION 'INTERLACE=OFF'
        FORMATOPTION 'COMPRESSION=9'
        FORMATOPTION 'QUANTIZE_FORCE=ON'
        FORMATOPTION 'QUANTIZE_DITHER=OFF'
        FORMATOPTION 'QUANTIZE_COLORS=256'
      END

      OUTPUTFORMAT
        NAME pnga
        DRIVER AGG/PNG
        IMAGEMODE RGB
        MIMETYPE 'image/png'
        EXTENSION 'png'
        FORMATOPTION 'INTERLACE=OFF'
        FORMATOPTION 'COMPRESSION=9'
        FORMATOPTION 'QUANTIZE_FORCE=ON'
        FORMATOPTION 'QUANTIZE_DITHER=OFF'
        FORMATOPTION 'QUANTIZE_COLORS=256'
      END

      OUTPUTFORMAT
        NAME pnga_transparent
        DRIVER AGG/PNG
        IMAGEMODE RGBA
        MIMETYPE 'image/png'
        EXTENSION 'png'
        TRANSPARENT ON
        FORMATOPTION 'INTERLACE=OFF'
        FORMATOPTION 'COMPRESSION=9'
        FORMATOPTION 'QUANTIZE_FORCE=ON'
        FORMATOPTION 'QUANTIZE_DITHER=OFF'
        FORMATOPTION 'QUANTIZE_COLORS=256'
      END

      OUTPUTFORMAT
        NAME jpg
        DRIVER AGG/JPEG
        IMAGEMODE RGB
        MIMETYPE 'image/jpeg'
        EXTENSION 'jpg'
        FORMATOPTION 'QUALITY=95'
      END

      OUTPUTFORMAT
        NAME jpga
        DRIVER AGG/JPEG
        IMAGEMODE RGB
        MIMETYPE 'image/jpeg'
        EXTENSION 'jpg'
        FORMATOPTION 'QUALITY=95'
      END

      OUTPUTFORMAT
        NAME tif
        DRIVER GDAL/GTiff
        IMAGEMODE RGB
        MIMETYPE 'image/tiff'
        EXTENSION 'tif'
        FORMATOPTION 'COMPRESS=JPEG'
        FORMATOPTION 'JPEG_QUALITY=100'
        FORMATOPTION 'PHOTOMETRIC=YCBCR'
      END

      OUTPUTFORMAT
        NAME svg
        DRIVER svg
        MIMETYPE 'image/svg+xml'
        EXTENSION 'svg'
        FORMATOPTION 'COMPRESSED_OUTPUT=FALSE'
        FORMATOPTION 'FULL_RESOLUTION=TRUE'
      END

      END
  ";

  /* acceptable projections in PROJ format */
  /* NOTE: included here for performance reasons AND each have 'over' switch to prevent stateprovince line wraps */
  public static $accepted_projections = array(
    'epsg:4326'   => array(
      'name' => 'Geographic',
      'proj' => 'proj=longlat,ellps=WGS84,datum=WGS84,no_defs'),
    'esri:102009' => array(
      'name' => 'North America Lambert',
      'proj' => 'proj=lcc,lat_1=20,lat_2=60,lat_0=40,lon_0=-96,x_0=0,y_0=0,ellps=GRS80,datum=NAD83,units=m,over,no_defs'),
    'esri:102015' => array(
      'name' => 'South America Lambert',
      'proj' => 'proj=lcc,lat_1=-5,lat_2=-42,lat_0=-32,lon_0=-60,x_0=0,y_0=0,ellps=aust_SA,units=m,over,no_defs'),
    'esri:102014' => array(
      'name' => 'Europe Lambert',
      'proj' => 'proj=lcc,lat_1=43,lat_2=62,lat_0=30,lon_0=10,x_0=0,y_0=0,ellps=intl,units=m,over,no_defs'),
    'esri:102012' => array(
      'name' => 'Asia Lambert',
      'proj' => 'proj=lcc,lat_1=30,lat_2=62,lat_0=0,lon_0=105,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
    'esri:102024' => array(
      'name' => 'Africa Lambert',
      'proj' => 'proj=lcc,lat_1=20,lat_2=-23,lat_0=0,lon_0=25,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
    'epsg:3112'   => array(
      'name' => 'Australia Lambert',
      'proj' => 'proj=lcc,lat_1=-18,lat_2=-36,lat_0=0,lon_0=134,x_0=0,y_0=0,ellps=GRS80,towgs84=0,0,0,0,0,0,0,units=m,over,no_defs'),
    'epsg:102017' => array(
      'name' => 'North Pole Azimuthal',
      'proj' => 'proj=laea,lat_0=90,lon_0=0,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
    'epsg:102019' => array(
      'name' => 'South Pole Azimuthal',
      'proj' => 'proj=laea,lat_0=-90,lon_0=0,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
  );

  /* acceptable shapes */ 
  public static $accepted_shapes = array(
    'plus',
    'cross',
    'opencircle',
    'openstar',
    'opensquare',
    'opentriangle',
    'circle',
    'star',
    'square',
    'triangle'
  );

  /* base download factor to rescale the resultant image */
  private $_download_factor = 1;

  /* url for legend image if produced */
  private $_legend_url;

  /* url for scalebar image if produced */
  private $_scalebar_url;

  /* holding bin for any errors thrown */
  private $_errors = array();

  /* holding bin for any geographic coordinates that fall outside extent of Earth */
  private $_bad_points = array();

  /* post-draw padding for longitude extent to be used as a correction factor on front-end */
  private $_ox_pad = 0;

  /* post-draw padding for latitude extent to be used as a correction factor on front-end */
  private $_oy_pad = 0;

  function __construct() {
    if (!extension_loaded("MapScript")) {
      $this->set_error("php_mapscript.so extension is not loaded");
      exit;
    }
    $this->map_obj = ms_newMapObjFromString($this->mapfile_string);
  }
  
  function __destruct() {
  }

  public function __call($name, $arguments) {
    // set a property
    if (substr($name,0,4) == 'set_') {
      $property = substr($name,4);
      $this->$property = $arguments[0];
    // add to an array property
    } else if (substr($name,0,4) == 'add_') {
      $property = substr($name,4);
      array_push($this->$property, $arguments[0]);
    }
    return $this;
  }

  /**
  * Set the extent of the map
  * @param array $extent
  */
  public function set_max_extent($extent = array()) {
    $extent = explode(',', $extent);
    $this->max_extent = $extent;
    return $this;
  }

  /**
  * Set the image size
  * @param array $image_size
  */
  public function set_image_size($image_size = array()) {
    $image_size = explode(',', $image_size);
    $this->image_size = $image_size;
    return $this;
  }

  /**
  * Flexibly load up all the request parameters
  */
  public function get_request() {
    $this->coords           = $this->load_param('coords', array());
    $this->regions          = $this->load_param('regions', array());

    $this->output           = $this->load_param('output','pnga');
    $this->projection       = $this->load_param('projection', 'epsg:4326');
    $this->projection_map   = $this->load_param('projection_map', 'epsg:4326');

    $this->bbox_map         = $this->load_param('bbox_map', '-180,-90,180,90');

    $this->bbox_rubberband  = $this->load_param('bbox_rubberband', array());

    $this->pan              = $this->load_param('pan', false);

    $this->layers           = $this->load_param('layers', array());

    $this->graticules       = (array_key_exists('grid', $this->layers)) ? true : false;

    $this->watermark        = $this->load_param('watermark', false);

    $this->gridspace        = $this->load_param('gridspace', false);

    $this->download         = $this->load_param('download', false);

    $this->crop             = $this->load_param('crop', false);

    $this->options          = $this->load_param('options', array()); //scalebar, legend

    $this->border_thickness = $this->load_param('border_thickness', 1.25);

    $this->rotation         = $this->load_param('rotation', 0);
    $this->zoom_out         = $this->load_param('zoom_out', false);

    $this->_download_factor = $this->load_param('download_factor', 1);

    $this->file_name        = $this->load_param('file_name', time());

    $this->download_token   = $this->load_param('download_token', md5(time()));
    setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");

    return $this;
  }

  /**
  * Get a request parameter
  * @param string $name
  * @param string $default parameter optional
  * @return string the parameter value or empty string if null
  */
  public function load_param($name, $default = ''){
    if(!isset($_REQUEST[$name]) || !$_REQUEST[$name]) { return $default; }
    $value = $_REQUEST[$name];
    if(get_magic_quotes_gpc() != 1) { $value = $this->add_slashes_extended($value); }
    return $value;
  }

  /**
  * Add slashes to either a string or an array
  * @param string/array $arr_r
  * @return string/array
  */
  private function add_slashes_extended(&$arr_r) {
    if(is_array($arr_r)) {
      foreach ($arr_r as &$val) {
        is_array($val) ? $this->add_slashes_extended($val) : $val = addslashes($val);
      }
      unset($val);
    } else {
      $arr_r = addslashes($arr_r);
    }
    return $arr_r;
  }

  /**
  * Create all the symbol objects
  */
  private function load_symbols() {
    if(!$this->map_obj) {
      $this->set_error('Map object is not loaded');
    } else {
      //star
      $nId = ms_newSymbolObj($this->map_obj, "star");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_TRUE);
      $symbol->set("inmapfile", MS_TRUE);
      $symbol->set("transparent", 100);
      $spoints = array(
          0, 0.375,
          0.35, 0.365,
          0.5, 0,
          0.65, 0.375,
          1, 0.375,
          0.75, 0.625,
          0.875, 1,
          0.5, 0.75,
          0.125, 1,
          0.25, 0.625,
          0, 0.375
      );
      $symbol->setpoints($spoints);

      //openstar
      $nId = ms_newSymbolObj($this->map_obj, "openstar");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_FALSE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          0, 0.375,
          0.35, 0.365,
          0.5, 0,
          0.65, 0.375,
          1, 0.375,
          0.75, 0.625,
          0.875, 1,
          0.5, 0.75,
          0.125, 1,
          0.25, 0.625,
          0, 0.375
      );
      $symbol->setpoints($spoints);

      //triangle
      $nId = ms_newSymbolObj($this->map_obj, "triangle");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_TRUE);
      $symbol->set("inmapfile", MS_TRUE);
      $symbol->set("transparent", 100);
      $spoints = array(
          0, 1,
          0.5, 0,
          1, 1,
          0, 1
      );
      $symbol->setpoints($spoints);

      //opentriangle
      $nId = ms_newSymbolObj($this->map_obj, "opentriangle");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_FALSE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          0, 1,
          0.5, 0,
          1, 1,
          0, 1
      );
      $symbol->setpoints($spoints);

      //square
      $nId = ms_newSymbolObj($this->map_obj, "square");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_TRUE);
      $symbol->set("inmapfile", MS_TRUE);
      $symbol->set("transparent", 100);
      $spoints = array(
          0, 1,
          0, 0,
          1, 0,
          1, 1,
          0, 1
      );
      $symbol->setpoints($spoints);

      //opensquare
      $nId = ms_newSymbolObj($this->map_obj, "opensquare");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_FALSE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          0, 1,
          0, 0,
          1, 0,
          1, 1,
          0, 1
      );
      $symbol->setpoints($spoints);

      //plus
      $nId = ms_newSymbolObj($this->map_obj, "plus");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_FALSE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          0.5, 0,
          0.5, 1,
          -99, -99,
          0, 0.5,
          1, 0.5
      );
      $symbol->setpoints($spoints);

      //cross
      $nId = ms_newSymbolObj($this->map_obj, "cross");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_VECTOR);
      $symbol->set("filled", MS_FALSE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          0, 0,
          1, 1,
          -99, -99,
          0, 1,
          1, 0
      );
      $symbol->setpoints($spoints);

      //circle
      $nId = ms_newSymbolObj($this->map_obj, "circle");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_ELLIPSE);
      $symbol->set("transparent", 100);
      $symbol->set("filled", MS_TRUE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          1, 1
      );
      $symbol->setpoints($spoints);

      //opencircle
      $nId = ms_newSymbolObj($this->map_obj, "opencircle");
      $symbol = $this->map_obj->getSymbolObjectById($nId);
      $symbol->set("type", MS_SYMBOL_ELLIPSE);
      $symbol->set("filled", MS_FALSE);
      $symbol->set("inmapfile", MS_TRUE);
      $spoints = array(
          1, 1
      );
      $symbol->setpoints($spoints);
    }
  }

  /**
  * Load-up all the settings for potential shapes
  */
  private function load_shapes() {
    //shaded relief
    $this->shapes['relief'] = array(
      'shape' => $this->shape_path . "/HYP_HR_SR_W_DR/HYP_HR_SR_W_DR.tif",
      'type'  => MS_LAYER_RASTER,
      'sort'  => 1
    );

    // Geotiff created by David P. Shorthouse using above file.
    $this->shapes['reliefgrey'] = array(
      'shape' => $this->shape_path . "/HYP_HR_SR_W_DR2/HYP_HR_SR_W_DR2.tif",
      'type'  => MS_LAYER_RASTER,
      'sort'  => 1
    );

    //base map
    $this->shapes['base'] = array(
      'shape' => $this->shape_path . "/10m_cultural/10m_admin_0_countries",
      'type'  => MS_LAYER_LINE,
      'sort'  => 2
    );

    //stateprovinces_polygon
    $this->shapes['stateprovinces_polygon'] = array(
      'shape' => $this->shape_path . "/10m_cultural/10m_admin_1_states_provinces_shp",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 3
    );

    //stateprovinces
    $this->shapes['stateprovinces'] = array(
      'shape' => $this->shape_path . "/10m_cultural/10m_admin_1_states_provinces_lines_shp",
      'type'  => MS_LAYER_LINE,
      'sort'  => 4
    );

    //lakes outline
    $this->shapes['lakesOutline'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_lakes",
      'type'  => MS_LAYER_LINE,
      'sort'  => 5
    );

    //lakes
    $this->shapes['lakes'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_lakes",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 6
    );

    //lake names
    $this->shapes['lakenames'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_lakes",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 7
    );

    //rivers
    $this->shapes['rivers'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_rivers_lake_centerlines",
      'type'  => MS_LAYER_LINE,
      'sort'  => 8
    );

    //rivers
    $this->shapes['rivernames'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_rivers_lake_centerlines",
      'type'  => MS_LAYER_LINE,
      'sort'  => 9
    );

    //placename
    $this->shapes['placenames'] = array(
      'shape' => $this->shape_path . "/10m_cultural/10m_populated_places_simple",
      'type'  => MS_LAYER_POINT,
      'sort'  => 11
    );

    //State/Provincial labels
    $this->shapes['stateprovnames'] = array(
      'shape' => $this->shape_path . "/10m_cultural/10m_admin_1_states_provinces_shp",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 12
    );

    //Country labels
    $this->shapes['countrynames'] = array(
      'shape' => $this->shape_path . "/10m_cultural/10m_admin_0_countries",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 13
    );

    //physicalLabels
    $this->shapes['physicalLabels'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_geography_regions_polys",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 14
    );

    //marineLabels
    $this->shapes['marineLabels'] = array(
      'shape' => $this->shape_path . "/10m_physical/10m_geography_marine_polys",
      'type'  => MS_LAYER_POLYGON,
      'sort'  => 15
    );

  }

  /**
  * Execute the process. This is the main worker process that calls other req'd and optional methods.
  */
  public function execute() {
    $this->load_shapes();
    $this->load_symbols();

    $this->map_obj->set("name","simplemappr");
    $this->map_obj->setFontSet($this->font_file);
    $this->map_obj->web->set("template","template.html");
    $this->map_obj->web->set("imagepath",$this->tmp_path);
    $this->map_obj->web->set("imageurl",$this->tmp_url);

    if($this->output == 'tif') {
      $this->map_obj->set("defresolution", 300);
      $this->map_obj->set("resolution", 300);
    }

    $units = (isset($this->projection) && $this->projection == $this->default_projection) ? MS_DD : MS_METERS;
    $this->map_obj->set("units",$units);

    $this->map_obj->imagecolor->setRGB(255,255,255);

    // Set the output format and size
    if(isset($this->output) && $this->output) {
      $output = (($this->output == 'png' || $this->output == 'pnga') && $this->download) ? $this->output . "_download" : $this->output;
      if($output == 'pptx' || $output == 'docx') { $output = 'pnga_transparent'; }
      $this->map_obj->selectOutputFormat($output);
    }

    // Set the map extent
    $this->set_map_extent();

    // Adjust map size
    $this->set_map_size();

    // Add the base layer
    $this->add_base_layer();

    //zoom in
    if(isset($this->bbox_rubberband) && $this->bbox_rubberband && !$this->is_resize()) {
      $this->zoom_in();
    }

    //zoom out
    if(isset($this->zoom_out) && $this->zoom_out) { $this->zoom_out(); }

    //pan
    if(isset($this->pan) && $this->pan) { $this->set_pan(); }

    //rotation
    if(isset($this->rotation) && $this->rotation != 0) { $this->map_obj->setRotation($this->rotation); }
    if(isset($this->rotation) && $this->rotation != 0 && $this->projection == $this->default_projection) {
      $this->reproject_map($this->default_projection, $this->projection);
    }

    //crop
    if(isset($this->crop) && $this->crop && $this->bbox_rubberband && $this->is_resize()) {
      $this->set_crop();
    }

    //add shaded political regions
    $this->add_regions();

    //add other layers as requested
    $this->add_layers();

    $this->add_graticules();

    //add the coordinates
    $this->add_coordinates();

    $this->add_watermark();

    $this->prepare_output();

    return $this;
  }

  /**
  * Add legend and scalebar
  */
  private function add_legend_scalebar() {
    if($this->download && array_key_exists('legend', $this->options) && $this->options['legend']) {
      $this->add_legend();
    } else if (!$this->download) {
      $this->add_legend();
    }
    if($this->download && array_key_exists('scalebar', $this->options) && $this->options['scalebar']) {
      $this->add_scalebar();
    } else if (!$this->download) {
      $this->add_scalebar();
    }
  }

  /**
  * Set the map extent
  */
  private function set_map_extent() {
    $ext = explode(',',$this->bbox_map);
    if(isset($this->projection) && $this->projection != $this->projection_map) {
      $origProjObj = ms_newProjectionObj(self::$accepted_projections[$this->projection_map]['proj']);
      $newProjObj = ms_newProjectionObj(self::$accepted_projections[$this->default_projection]['proj']);

      $poPoint1 = ms_newPointObj();
      $poPoint1->setXY($ext[0], $ext[1]);

      $poPoint2 = ms_newPointObj();
      $poPoint2->setXY($ext[2], $ext[3]);
          
      @$poPoint1->project($origProjObj,$newProjObj);
      @$poPoint2->project($origProjObj,$newProjObj);
          
      $ext[0] = $poPoint1->x;
      $ext[1] = $poPoint1->y;
      $ext[2] = $poPoint2->x;
      $ext[3] = $poPoint2->y;
          
      if($poPoint1->x < $this->max_extent[0] || $poPoint1->y < $this->max_extent[1] || $poPoint2->x > $this->max_extent[2] || $poPoint2->y > $this->max_extent[3] || $poPoint1->x > $poPoint2->x || $poPoint1->y > $poPoint2->y) {
        $ext[0] = $this->max_extent[0];
        $ext[1] = $this->max_extent[1];
        $ext[2] = $this->max_extent[2];
        $ext[3] = $this->max_extent[3];
      }
    }

    $ext0 = (min($ext[0], $ext[2]) == $ext[0]) ? $ext[0] : $ext[2];
    $ext2 = (max($ext[0], $ext[2]) == $ext[2]) ? $ext[2] : $ext[0];
    $ext1 = (min($ext[1], $ext[3]) == $ext[1]) ? $ext[1] : $ext[3];
    $ext3 = (max($ext[1], $ext[3]) == $ext[3]) ? $ext[3] : $ext[1];

    // Set the padding correction factors because final extent produced after draw() is off from setExtent
    $cellsize = max(($ext2 - $ext0)/($this->image_size[0]-1), ($ext3 - $ext1)/($this->image_size[1]-1));

    if($cellsize > 0) {
      $ox = max((($this->image_size[0]-1) - ($ext2 - $ext0)/$cellsize)/2,0);
      $oy = max((($this->image_size[1]-1) - ($ext3 - $ext1)/$cellsize)/2,0);
      $this->_ox_pad = $ox*$cellsize;
      $this->_oy_pad = $oy*$cellsize;
    }

    $this->map_obj->setExtent($ext0, $ext1, $ext2, $ext3);
  }

  /**
  * Set the map size
  */ 
  private function set_map_size() {
    $this->map_obj->setSize($this->image_size[0], $this->image_size[1]);
    if($this->is_resize()) {
      $this->map_obj->setSize($this->_download_factor*$this->image_size[0], $this->_download_factor*$this->image_size[1]);   
    }
  }

  /**
  * Add the base layer
  */
  private function add_base_layer() {
    if(!isset($this->layers['relief']) && !isset($this->layers['reliefgrey'])) {
      $layer = ms_newLayerObj($this->map_obj);
      $layer->set("name","baselayer");
      $layer->set("status",MS_ON);
      $layer->set("data",$this->shapes['base']['shape']);
      $layer->set("type",$this->shapes['base']['type']);
      $layer->setProjection(self::$accepted_projections[$this->default_projection]['proj']);

      // Add new class to new layer
      $class = ms_newClassObj($layer);

      // Add new style to new class
      $style = ms_newStyleObj($class);
      $style->set("width", isset($this->border_thickness) ? $this->border_thickness : 1.25);
      $style->color->setRGB(10,10,10);
    }
  }

  /**
  * Zoom In
  */
  private function zoom_in() {
    $bbox_rubberband = explode(',',$this->bbox_rubberband);
    if($bbox_rubberband[0] == $bbox_rubberband[2] || $bbox_rubberband[1] == $bbox_rubberband[3]) {
      $zoom_point = ms_newPointObj();
      $zoom_point->setXY($bbox_rubberband[0],$bbox_rubberband[1]);
      $this->map_obj->zoompoint(2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent, $this->get_max_extent());
    } else {
      $zoom_rect = ms_newRectObj();
      $zoom_rect->setExtent($bbox_rubberband[0], $bbox_rubberband[3], $bbox_rubberband[2], $bbox_rubberband[1]);
      $this->map_obj->zoomrectangle($zoom_rect, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
      $this->reset_zoom();
    }
  }

  /**
  * Zoom out
  */
  private function zoom_out() {
    $zoom_point = ms_newPointObj();
    $zoom_point->setXY($this->map_obj->width/2,$this->map_obj->height/2);
    $max_extent = $this->get_max_extent();
    $this->map_obj->zoompoint(-2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent, $max_extent);
  }

  /**
  * Determine max extent possible
  * @return obj
  */
  private function get_max_extent() {
    $max_extent = ms_newRectObj();
    $max_extent->setExtent($this->max_extent[0], $this->max_extent[1], $this->max_extent[2], $this->max_extent[3]);
    if($this->projection != $this->default_projection) {
      $origProjObj = ms_newProjectionObj(self::$accepted_projections[$this->default_projection]['proj']);
      $newProjObj = ms_newProjectionObj(self::$accepted_projections[$this->projection]['proj']);
      $max_extent->project($origProjObj,$newProjObj);   
    }
    return $max_extent;
  }

  private function reset_zoom() {
    $extent = $this->map_obj->extent;
    $max_extent = $this->get_max_extent();
    if($extent->minx < $max_extent->minx || $extent->miny < $max_extent->miny || $extent->maxx > $max_extent->maxx || $extent->maxy > $max_extent->maxy) {
      $new_point = ms_newPointObj();
      $new_point->setXY($this->map_obj->width/2,$this->map_obj->height/2);
      $this->map_obj->zoompoint(1, $new_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent, $max_extent);
    }
  }
  
  /**
  * Set the pan direction
  */
  private function set_pan() {
    switch ($this->pan) {
      case 'up':
        $x_offset = 1;
        $y_offset = 0.9;
      break;

      case 'right':
        $x_offset = 1.1;
        $y_offset = 1;
      break;

      case 'down':
        $x_offset = 1;
        $y_offset = 1.1;
      break;

      case 'left':
        $x_offset = 0.9;
        $y_offset = 1;
      break;
    }

    $new_point = ms_newPointObj();
    $new_point->setXY($this->map_obj->width/2*$x_offset,$this->map_obj->height/2*$y_offset);
    $max_extent = $this->get_max_extent();
    $this->map_obj->zoompoint(1, $new_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent, $max_extent);
  }

  /**
  * Set a new extent in the event of a crop action
  */
  private function set_crop() {
    $bbox_rubberband = explode(',',$this->bbox_rubberband);

    //lower-left coordinate
    $ll_point = new stdClass();
    $ll_point->x = $bbox_rubberband[0];
    $ll_point->y = $bbox_rubberband[3];
    $ll_coord = $this->pix2geo($ll_point);

    //upper-right coordinate
    $ur_point = new stdClass();
    $ur_point->x = $bbox_rubberband[2];
    $ur_point->y = $bbox_rubberband[1];
    $ur_coord = $this->pix2geo($ur_point);

    //set the size as selected
    $width = abs($bbox_rubberband[2]-$bbox_rubberband[0]);
    $height = abs($bbox_rubberband[3]-$bbox_rubberband[1]);
    $this->map_obj->setSize($this->_download_factor*$width,$this->_download_factor*$height);

    //set the extent to match that of the crop
    $this->map_obj->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);
  }

  /**
  * Add all coordinates to the map
  */
  public function add_coordinates() {
    if(isset($this->coords) && $this->coords) {
      //do this in reverse order because the legend will otherwise be presented in reverse order
      for($j=count($this->coords)-1; $j>=0; $j--) {

        //clear out previous loop's selection
        $size = '';
        $shape = '';
        $color = '';

        $title = ($this->coords[$j]['title']) ? $this->coords[$j]['title'] : '';
        $size = 8;
        if($this->coords[$j]['size']) {
          $size = $this->coords[$j]['size'];
        }
        if($this->is_resize()) {
          $size = ($this->coords[$j]['size']) ? $this->_download_factor*$this->coords[$j]['size'] : $this->_download_factor*8;
        }
        $shape = ($this->coords[$j]['shape']) ? $this->coords[$j]['shape'] : 'circle';
        $color = ($this->coords[$j]['color']) ? explode(" ",$this->coords[$j]['color']) : explode(" ","0 0 0");
        if(!is_array($color) || !array_key_exists(0, $color) || !array_key_exists(1, $color) || !array_key_exists(2, $color)) {
          $color = array(0,0,0);
        }

        $data = trim($this->coords[$j]['data']);

        if($data) {

          $layer = ms_newLayerObj($this->map_obj);
          $layer->set("name","layer_".$j);
          $layer->set("status",MS_ON);
          $layer->set("type",MS_LAYER_POINT);
          $layer->set("tolerance",5);
          $layer->set("toleranceunits",6);
          $layer->setProjection(self::$accepted_projections[$this->default_projection]['proj']);

          $class = ms_newClassObj($layer);
          if($title != "") { $class->set("name",$title); }

          $style = ms_newStyleObj($class);
          $style->set("symbolname",$shape);
          $style->set("size",$size);

          if(substr($shape, 0, 4) == 'open') {
            $style->color->setRGB($color[0],$color[1],$color[2]);
          } else {
            $style->color->setRGB($color[0],$color[1],$color[2]);
            $style->outlinecolor->setRGB(85,85,85);
          }

          $new_shape = ms_newShapeObj(MS_SHAPE_POINT);
          $new_line = ms_newLineObj();

          $row = explode("\n",$this->remove_empty_lines($data));  //split the lines that have data
          $points = array(); //create an array to hold unique locations
      
          foreach ($row as $loc) {
            $loc = trim(preg_replace('/[^\d\s,;.-]/', '', $loc));
            $coord_array = preg_split("/[\s,;]+/",$loc); //split the coords by a space, comma, semicolon, or \t
            $coord = new stdClass();
            $coord->x = array_key_exists(1, $coord_array) ? trim($coord_array[1]) : "nil";
            $coord->y = array_key_exists(0, $coord_array) ? trim($coord_array[0]) : "nil";
            //only add point when data are good & a title
            if($this->check_coord($coord) && $title != "") {
              $points[$coord->x.$coord->y] = array($coord->x, $coord->y); //unique locations
            } else {
              $this->_bad_points[] = $this->coords[$j]['title'] . ' : ' . $coord->y . ',' . $coord->x;
            }
          }
          foreach($points as $point) {
            $new_point = ms_newPointObj();
            $new_point->setXY($point[0], $point[1]);
            $new_line->add($new_point);
          }
          $new_shape->add($new_line);
          $layer->addFeature($new_shape);
        }
      }
    }
  }

  /**
  * Add shaded regions to the map
  */
  public function add_regions() {
    if(isset($this->regions) && $this->regions) {  
      for($j=count($this->regions)-1; $j>=0; $j--) {

        //clear out previous loop's selection
        $color = '';
        $title = ($this->regions[$j]['title']) ? $this->regions[$j]['title'] : '';
        $color = ($this->regions[$j]['color']) ? explode(" ",$this->regions[$j]['color']) : explode(" ","0 0 0");
        if(!is_array($color) || !array_key_exists(0, $color) || !array_key_exists(1, $color) || !array_key_exists(2, $color)) {
            $color = array(0,0,0);
        }

        $data = trim($this->regions[$j]['data']);

        if($data) {
          $baselayer = true;
          //grab the textarea for regions & split
          $rows = explode("\n",$this->remove_empty_lines($data));
          $qry = array();
          foreach($rows as $row) {
            $regions = preg_split("/[,;]+/", $row); //split by a comma, semicolon
            foreach($regions as $region) {
              if($region) {
                $pos = strpos($region, '[');
                if($pos !== false) {
                  $baselayer = false;
                  $split = explode("[", str_replace("]", "", trim(strtoupper($region))));
                  $states = preg_split("/[\s|]+/", $split[1]);
                  $statekey = array();
                  foreach($states as $state) {
                    $statekey[] = "'[HASC_1]' =~ /".$state."$/";
                  }
                  $qry['stateprovince'][] = "'[ISO]' = '".trim($split[0])."' AND (".implode(" OR ", $statekey).")";
                  $qry['country'][] = "'[ISO_A3]' = '".trim($split[0])."'";
                } else {
                  $region = addslashes(trim($region));
                  $qry['stateprovince'][] = "'[NAME_0]' =~ /".$region."$/ OR '[NAME_1]' =~ /".$region."$/ OR '[ADM0_A3]' =~ /".$region."$/";
                  $qry['country'][] = "'[NAME]' =~ /".$region."$/ OR '[NAME_FORMA]' =~ /".$region."$/";
                }
              }
            }
          }

          $layer = ms_newLayerObj($this->map_obj);
          $layer->set("name","query_layer_".$j);

          if($baselayer) {
            $layer->set("data",$this->shapes['base']['shape']);
            $layer->set("type",MS_LAYER_POLYGON);
          } else {
            $layer->set("data",$this->shapes['stateprovinces_polygon']['shape']);
            $layer->set("type",$this->shapes['stateprovinces_polygon']['type']);
          }

          $layer->set("template", "template.html");
          $layer->setProjection(self::$accepted_projections[$this->default_projection]['proj']);

          $query = ($baselayer) ? $qry['country'] : $qry['stateprovince'];

          $layer->setFilter("(".implode(" OR ", $query).")");
          $class = ms_newClassObj($layer);
          $class->set("name", $title);

          $style = ms_newStyleObj($class);
          $style->color->setRGB($color[0],$color[1],$color[2]);
          $style->outlinecolor->setRGB(30,30,30);
          $style->set("opacity", 75);

          $layer->set("status",MS_ON);
        }

      }
    }
  }

  /**
  * Add all selected layers to the map
  */
  private function add_layers() {
    if(isset($this->layers) && $this->layers) {

      unset($this->layers['grid']);
      $sort = array();

      if(isset($this->layers['relief']) || isset($this->layers['reliefgrey'])) { $this->layers['base'] = 'on'; }
      if(isset($this->output) && $this->output == 'svg') { unset($this->layers['relief'], $this->layers['reliefgrey']); }

      foreach($this->layers as $key => $row) {
        $sort[$key] = (isset($this->shapes[$key])) ? $this->shapes[$key]['sort'] : $row;
      }
      array_multisort($sort, SORT_ASC, $this->layers);

      $srs_projections = implode(array_keys(self::$accepted_projections), " ");
                          
      foreach($this->layers as $name => $status) {
        //make the layer
        if(array_key_exists($name, $this->shapes)) {
          $layer = ms_newLayerObj($this->map_obj);
          $layer->set("name", $name);
          $layer->setMetaData("wfs_title", $name);
          $layer->setMetaData("wfs_typename", $name);
          $layer->setMetaData("wfs_srs", $srs_projections);
          $layer->setMetaData("wfs_extent", "-180 -90 180 90");
          $layer->setMetaData("wfs_encoding", "UTF-8");
          $layer->setMetaData("gml_include_items", "all");
          $layer->setMetaData("gml_featureid", "OBJECTID");
          $layer->set("type", $this->shapes[$name]['type']);
          $layer->set("status",MS_ON);
          $layer->setConnectionType(MS_SHAPEFILE);
          $layer->set("data", $this->shapes[$name]['shape']);
          $layer->setProjection(self::$accepted_projections[$this->default_projection]['proj']);
          $layer->set("template", "template.html");
          $layer->set("dump", true);

          switch($name) {
            case 'lakesOutline':
              $class = ms_newClassObj($layer);
              $style = ms_newStyleObj($class);
              $style->color->setRGB(80,80,80);
            break;
            case 'rivers':
            case 'lakes':
              $class = ms_newClassObj($layer);
              $style = ms_newStyleObj($class);
              $style->color->setRGB(120,120,120);
            break;

            case 'rivernames':
            case 'lakenames':
              $layer->set("tolerance", 1);
              $layer->set("toleranceunits", "pixels");
              $layer->set("labelitem", "Name1");

              $class = ms_newClassObj($layer);
              $class->label->set("font", "arial");
              $class->label->set("type", MS_TRUETYPE);
              $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
              $class->label->set("position", MS_UR);
              $class->label->set("offsetx", 3);
              $class->label->set("offsety", 3);
              $class->label->set("partials", MS_FALSE);
              $class->label->color->setRGB(10, 10, 10);
            break;
            
            case 'base':
            case 'stateprovinces':
              $class = ms_newClassObj($layer);
              $style = ms_newStyleObj($class);
              $style->set("width",isset($this->border_thickness) ? $this->border_thickness : 1.25);
              $style->color->setRGB(10,10,10);
            break;

            case 'countrynames':
              $layer->set("tolerance", 5);
              $layer->set("toleranceunits", "pixels");
              $layer->set("labelitem", "NAME");

              $class = ms_newClassObj($layer);
              $class->label->set("font", "arial");
              $class->label->set("type", MS_TRUETYPE);
              $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*9 : 12);
              $class->label->set("position", MS_CC);
              $class->label->set("offsetx", 3);
              $class->label->set("offsety", 3);
              $class->label->set("partials", MS_FALSE);
              $class->label->color->setRGB(10, 10, 10);
            break;

            case 'placenames':
              $layer->set("tolerance", 5);
              $layer->set("toleranceunits", "pixels");
              $layer->set("labelitem", "NAMEASCII");

              $class = ms_newClassObj($layer);
              $class->label->set("font", "arial");
              $class->label->set("type", MS_TRUETYPE);
              $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
              $class->label->set("position", MS_UR);
              $class->label->set("offsetx", 3);
              $class->label->set("offsety", 3);
              $class->label->set("partials", MS_FALSE);
              $class->label->color->setRGB(10, 10, 10);
              $style = ms_newStyleObj($class);
              $style->set("symbolname","circle");
              $style->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 6);
              $style->color->setRGB(100,100,100);
            break;

            case 'stateprovnames':
              $layer->set("tolerance", 5);
              $layer->set("toleranceunits", "pixels");
              $layer->set("labelitem", "NAME_1");

              $class = ms_newClassObj($layer);
              $class->label->set("font", "arial");
              $class->label->set("type", MS_TRUETYPE);
              $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*8 : 10);
              $class->label->set("position", MS_CC);
              $class->label->set("offsetx", 3);
              $class->label->set("offsety", 3);
              $class->label->set("partials", MS_FALSE);
              $class->label->color->setRGB(10, 10, 10);
            break;

            case 'physicalLabels':
              $layer->set("tolerance", 5);
              $layer->set("toleranceunits", "pixels");
              $layer->set("labelitem", "Name");

              $class = ms_newClassObj($layer);
              $class->label->set("font", "arial");
              $class->label->set("type", MS_TRUETYPE);
              $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
              $class->label->set("position", MS_UR);
              $class->label->set("offsetx", 3);
              $class->label->set("offsety", 3);
              $class->label->set("partials", MS_FALSE);
              $class->label->color->setRGB(10, 10, 10);
            break;
            
            case 'marineLabels':
              $layer->set("tolerance", 5);
              $layer->set("toleranceunits", "pixels");
              $layer->set("labelitem", "Name");

              $class = ms_newClassObj($layer);
              $class->label->set("font", "arial");
              $class->label->set("type", MS_TRUETYPE);
              $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
              $class->label->set("position", MS_UR);
              $class->label->set("offsetx", 3);
              $class->label->set("offsety", 3);
              $class->label->set("partials", MS_FALSE);
              $class->label->color->setRGB(10, 10, 10);
            break;

            default:
          }
        }
      }
    }
  }

  private function add_watermark() {
    if(isset($this->watermark) && $this->watermark) {
      $layer = ms_newLayerObj($this->map_obj);
      $layer->set("name", 'watermark');
      $layer->set("type", MS_LAYER_ANNOTATION);
      $layer->set("status", MS_ON);
      $layer->set("transform", MS_FALSE);
      $layer->set("sizeunits", MS_PIXELS);

      $class = ms_newClassObj($layer);
      $class->settext("http://www.simplemappr.net");
      $class->label->set("font", "arial");
      $class->label->set("type", MS_TRUETYPE);
      $class->label->set("size", 8);
      $class->label->set("position", MS_XY);
      $class->label->color->setRGB(10, 10, 10);

      $shape = ms_newShapeObj(MS_SHAPE_POINT);
      $line = ms_newLineObj();

      $point = ms_newPointObj();
      $point->setXY(2, $this->map_obj->height-2);

      $line->add($point);
      $shape->add($line);
      $layer->addFeature($shape);
    }
  }

  /**
  * Add graticules (or grid lines) to map
  */
  public function add_graticules() {
    if(isset($this->graticules) && $this->graticules) {
      $layer = ms_newLayerObj($this->map_obj);
      $layer->set("name", 'grid');
      $layer->set("type", MS_LAYER_LINE);
      $layer->set("status",MS_ON);
      $layer->setProjection(self::$accepted_projections[$this->default_projection]['proj']);

      $class = ms_newClassObj($layer);
      $class->label->set("font", "arial");
      $class->label->set("type", MS_TRUETYPE);
      $class->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*9 : 10);
      $class->label->set("position", MS_CC);
      $class->label->color->setRGB(30, 30, 30);

      $style = ms_newStyleObj($class);
      $style->color->setRGB(200,200,200);

      ms_newGridObj($layer);
      $minx = $this->map_obj->extent->minx;
      $maxx = $this->map_obj->extent->maxx;

      //project the extent back to default such that we can work with proper tick marks
      if($this->projection != $this->default_projection) {
        $origProjObj = ms_newProjectionObj(self::$accepted_projections[$this->projection]['proj']);
        $newProjObj = ms_newProjectionObj(self::$accepted_projections[$this->default_projection]['proj']);

        $poPoint1 = ms_newPointObj();
        $poPoint1->setXY($this->map_obj->extent->minx, $this->map_obj->extent->miny);

        $poPoint2 = ms_newPointObj();
        $poPoint2->setXY($this->map_obj->extent->maxx, $this->map_obj->extent->maxy);

        @$poPoint1->project($origProjObj,$newProjObj);
        @$poPoint2->project($origProjObj,$newProjObj);

        $minx = $poPoint1->x;
        $maxx = $poPoint2->x;
      }

      $ticks = abs($maxx-$minx)/24;

      if($ticks >= 5) { $labelformat = "DD"; }
      if($ticks < 5) { $labelformat = "DDMM"; }
      if($ticks <= 1) { $labelformat = "DDMMSS"; }

      $layer->grid->set("labelformat", ($this->gridspace) ? "DD" : $labelformat);
      $layer->grid->set("maxarcs", $ticks);
      $layer->grid->set("maxinterval", ($this->gridspace) ? $this->gridspace : $ticks);
      $layer->grid->set("maxsubdivide", 2);
    }
  }
  
  /**
  * Create the legend file
  */
  private function add_legend() {
    $this->map_obj->legend->set("keysizex", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*15 : 20);
    $this->map_obj->legend->set("keysizey", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*15 : 20);
    $this->map_obj->legend->set("keyspacingx", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*3 : 5);
    $this->map_obj->legend->set("keyspacingy", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*3 : 5);
    $this->map_obj->legend->set("postlabelcache", 1);
    $this->map_obj->legend->set("transparent", 0);
    $this->map_obj->legend->label->set("font", "arial");
    $this->map_obj->legend->label->set("type", MS_TRUETYPE);
    $this->map_obj->legend->label->set("position", 1);
    $this->map_obj->legend->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*8 : 10);
    $this->map_obj->legend->label->set("antialias", 50);
    $this->map_obj->legend->label->color->setRGB(0,0,0);
    
    //svg format cannot do legends in MapServer
    if($this->download && $this->options['legend'] && $this->output != 'svg') {
      $this->map_obj->legend->set("status", MS_EMBED);
      $this->map_obj->legend->set("position", MS_UR);
      $this->map_obj->drawLegend();
    }
    if(!$this->download) {
      $this->map_obj->legend->set("status", MS_DEFAULT);
      $this->legend = $this->map_obj->drawLegend();
      $this->_legend_url = $this->legend->saveWebImage();
    }
  }

  private function is_resize() {
    if($this->download || $this->output == 'pptx' || $this->output == 'docx') {
      return true;
    }
    return false;
  }
  
  /**
  * Create a scalebar image
  */
  public function add_scalebar() {
    $this->map_obj->scalebar->set("style", 0);
    $this->map_obj->scalebar->set("intervals", 3);
    $this->map_obj->scalebar->set("height", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*4 : 8);
    $this->map_obj->scalebar->set("width", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*100 : 200);
    $this->map_obj->scalebar->color->setRGB(30,30,30);
    $this->map_obj->scalebar->outlinecolor->setRGB(0,0,0);
    $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
    $this->map_obj->scalebar->set("transparent", 1);
    $this->map_obj->scalebar->label->set("font", "arial");
    $this->map_obj->scalebar->label->set("type", MS_TRUETYPE);
    $this->map_obj->scalebar->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*5 : 8);
    $this->map_obj->scalebar->label->set("antialias", 50);
    $this->map_obj->scalebar->label->color->setRGB(0,0,0);
    
    //svg format cannot do scalebar in MapServer
    if($this->download && $this->options['scalebar'] && $this->output != 'svg') {
      $this->map_obj->scalebar->set("status", MS_EMBED);
      $this->map_obj->scalebar->set("position", MS_LR);
      $this->map_obj->drawScalebar();
    }
    if(!$this->download) {
      $this->map_obj->scalebar->set("status", MS_DEFAULT);
      $this->scale = $this->map_obj->drawScalebar();
      $this->_scalebar_url = $this->scale->saveWebImage();
    }
  }

  /**
  * Add a border to a downloaded map image
  */
  private function add_border() {
    if($this->is_resize() && array_key_exists('border', $this->options) && ($this->options['border'] == 1 || $this->options['border'] == 'true')) {
      $outline_layer = ms_newLayerObj($this->map_obj);
      $outline_layer->set("name","outline");
      $outline_layer->set("type",MS_LAYER_POLYGON);
      $outline_layer->set("status",MS_ON);
      $outline_layer->set("transform", MS_FALSE);
      $outline_layer->set("sizeunits", MS_PIXELS);

      // Add new class to new layer
      $outline_class = ms_newClassObj($outline_layer);

      // Add new style to new class
      $outline_style = ms_newStyleObj($outline_class);
      $outline_style->outlinecolor->setRGB(0,0,0);
      $outline_style->set("width",3);

      $polygon = ms_newShapeObj(MS_SHAPE_POLYGON);

      $polyLine = ms_newLineObj();
      $polyLine->addXY(0, 0);
      $polyLine->addXY($this->map_obj->width, 0);
      $polyLine->addXY($this->map_obj->width, $this->map_obj->height);
      $polyLine->addXY(0, $this->map_obj->height);
      $polyLine->addXY(0, 0);

      $polygon->add($polyLine);
      $outline_layer->addFeature($polygon);
    }
  }

  /**
  * Prepare the output
  */
  private function prepare_output() {
    if(isset($this->projection)) {
      if($this->projection != $this->default_projection) {
        $this->reproject_map($this->default_projection, $this->projection);
        $this->add_legend_scalebar();
        $this->add_border();
        $this->image = $this->map_obj->drawQuery();
      } else {
        $this->add_legend_scalebar();
        $this->add_border();
        $this->image = $this->map_obj->draw();
      }
    }
  }
  
  /**
  * Get all the coordinates that fall outside Earth's geographic extent in dd
  * @return string
  */
  private function get_bad_points() {
    return implode('<br />', $this->_bad_points);
  }

  /**
  * Get a user-defined file name, cleaned of illegal characters
  * @return string
  */
  public function get_file_name() {
    return preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $this->file_name) . "." . $this->output;
  }
  
  /**
  * Produce the  final output
  */
  public function get_output() {
    switch($this->output) {
      case 'tif':
        error_reporting(0);
        $this->image_url = $this->image->saveWebImage();
        $image_filename = basename($this->image_url);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); 
        header("Content-Type: image/tiff");
        header("Content-Disposition: attachment; filename=\"" . $this->get_file_name() . "\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($this->tmp_path.$image_filename));
        ob_clean();
        flush();
        readfile($this->tmp_path.$image_filename);
        exit();
      break;

      case 'png':
        error_reporting(0);
        $this->image_url = $this->image->saveWebImage();
        $image_filename = basename($this->image_url);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); 
        header("Content-Type: image/png");
        header("Content-Disposition: attachment; filename=\"" . $this->get_file_name() . "\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($this->tmp_path.$image_filename));
        ob_clean();
        flush();
        readfile($this->tmp_path.$image_filename);
        exit();
      break;

      case 'svg':
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); 
        header("Content-Type: image/svg+xml");
        header("Content-Disposition: attachment; filename=\"" . $this->get_file_name() . "\";" );
        $this->image->saveImage("");
        exit();
      break;

      default:
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: application/json");

        $this->image_url = $this->image->saveWebImage();

        $output = array(
          'mapOutputImage'      => $this->image_url,
          'rendered_bbox'       => ($this->map_obj->extent->minx + $this->_ox_pad) . ',' . ($this->map_obj->extent->miny + $this->_oy_pad) . ',' . ($this->map_obj->extent->maxx - $this->_ox_pad) . ',' . ($this->map_obj->extent->maxy - $this->_oy_pad),
          'rendered_rotation'   => $this->rotation,
          'rendered_projection' => $this->projection,
          'legend_url'          => $this->_legend_url,
          'scalebar_url'        => $this->_scalebar_url,
          'bad_points'          => $this->get_bad_points(),
        );

        echo json_encode($output);
    }
  }
  
  /**
   * Reproject a $map from one projection to another
   * @param obj $map
   * @param string $input_projection
   * @param string $output_projection
   */
  private function reproject_map($input_projection,$output_projection) {
    if(!array_key_exists($output_projection, self::$accepted_projections)) { $output_projection = 'epsg:4326'; }
    
    $origProjObj = ms_newProjectionObj(self::$accepted_projections[$input_projection]['proj']);
    $newProjObj = ms_newProjectionObj(self::$accepted_projections[$output_projection]['proj']);

    $oRect = $this->map_obj->extent;
    @$oRect->project($origProjObj,$newProjObj);
    $this->map_obj->setExtent($oRect->minx,$oRect->miny,$oRect->maxx,$oRect->maxy);
    $this->map_obj->setProjection(self::$accepted_projections[$output_projection]['proj']);
  }

  /**
   * Convert image coordinates to map coordinates
   * @param obj $point, (x,y) coordinates in pixels
   * @return obj $newPoint reprojected point in map coordinates
   */
   public function pix2geo($point) {
     $newPoint = new stdClass();
     $deltaX = abs($this->map_obj->extent->maxx - $this->map_obj->extent->minx);
     $deltaY = abs($this->map_obj->extent->maxy - $this->map_obj->extent->miny);
  
     $newPoint->x = $this->map_obj->extent->minx + ($point->x*$deltaX)/(float)$this->image_size[0];
     $newPoint->y = $this->map_obj->extent->miny + (((float)$this->image_size[1] - $point->y)*$deltaY)/(float)$this->image_size[1];
     return $newPoint;
   }

  /**
  * Remove empty lines from a string
  * @param $string
  * @return string cleansed string with empty lines removed
  */
  public function remove_empty_lines($string) {
    return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
  }

  /**
   * Check a DD coordinate object and return true if it fits on globe, false if not
   * @param obj $coord (x,y) coordinates
   * @return true,false
   */
  public function check_coord($coord) {
    $output = false;
    if((float)$coord->x && (float)$coord->y && $coord->y <= 90 && $coord->y >= -90 && $coord->x <= 180 && $coord->x >= -180) { $output = true; }
    return $output;
  }
  
  /**
  * Test if has errors
  * @return boolean
  */
  private function has_error(){
    return count($this->_errors) > 0;
  }

  /**
  * Set error message
  * @param string $message
  * @param string $layer name
  */
  private function set_error($message, $layer = 'Error'){
    $this->_errors[$layer][] = $message;
  }
  
  /**
  * Print all errors thrown
  */
  private function show_errors() {
    print_r($this->_errors);
  }

}
?>
