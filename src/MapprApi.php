<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */
namespace SimpleMappr;

/**
 * API handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class MapprApi extends Mappr
{
    private $_coord_cols = array();
    private $_accepted_output = array('png', 'jpg', 'svg');

    /**
     * Override get_request method in parent class
     *
     * @return object $this
     */
    public function getRequest()
    {
        //ping API to return JSON
        $this->ping             = $this->loadParam('ping', false);

        $this->method           = $_SERVER['REQUEST_METHOD'];

        $this->download         = true;
        $this->watermark        = true;
        $this->options          = array();

        $this->url              = false;
        $this->url_content      = "";
        $url                    = urldecode($this->loadParam('url', false));

        if ($this->method == "POST" && $_FILES) {
            $file = $this->_moveFile();
        } else {
            $file = urldecode($this->loadParam('file', false));
        }

        if ($url) {
            $this->url = $url;
        }
        if ($file) {
            $this->url = $file;
        }

        $this->points           = $this->loadParam('points', array());
        $this->legend           = $this->loadParam('legend', array());
        $this->shape            = (is_array($this->loadParam('shape', array()))) ? $this->loadParam('shape', array()) : array($this->loadParam('shape', array()));
        $this->size             = (is_array($this->loadParam('size', array()))) ? $this->loadParam('size', array()) : array($this->loadParam('size', array()));
        $this->color            = (is_array($this->loadParam('color', array()))) ? $this->loadParam('color', array()) : array($this->loadParam('color', array()));

        $this->outlinecolor     = $this->loadParam('outlinecolor', null);
        $this->border_thickness = (float)$this->loadParam('thickness', 1.25);

        $shaded = $this->loadParam('shade', array());
        $this->regions = array(
            'data' => (array_key_exists('places', $shaded)) ? $shaded['places'] : "",
            'title' => (array_key_exists('title', $shaded)) ? $shaded['title'] : "",
            'color' => (array_key_exists('color', $shaded)) ? str_replace(",", " ", $shaded['color']) : "120 120 120"
        );

        $this->output           = $this->loadParam('output', 'pnga');
        $this->projection       = $this->loadParam('projection', 'epsg:4326');
        $this->projection_map   = 'epsg:4326';
        $this->origin           = (int)$this->loadParam('origin', false);

        $this->bbox_map         = $this->loadParam('bbox', '-180,-90,180,90');
        $this->zoom             = (int)$this->loadParam('zoom', false);

        //convert layers as comma-separated values to an array
        $_layers                = explode(',', $this->loadParam('layers', ""));
        $layers = array();
        $layers['countries']    = true;
        foreach ($_layers as $_layer) {
            if ($_layer) {
                $layers[trim($_layer)] = trim($_layer);
            }
        }
        $this->layers           = $layers;
        $this->graticules       = $this->loadParam('graticules', false);
        $this->gridspace        = $this->loadParam('spacing', false);
        $this->gridlabel        = $this->loadParam('gridlabel', "true");

        if ($this->loadParam('border', false)) {
            $this->options['border'] = true;
        }
        if ($this->loadParam('legend', false)) {
            $this->options['legend'] = true;
        }
        if ($this->loadParam('scalebar', false)) {
            $this->options['scalebar'] = true;
        }

        //set the image size from width & height to array(width, height)
        $this->width            = (float)$this->loadParam('width', 900);
        $this->height           = (float)$this->loadParam('height', (isset($_REQUEST['width']) && !isset($_REQUEST['height'])) ? $this->width/2 : 450);
        if ($this->width == 0 || $this->height == 0) {
            $this->width = 900; $this->height = 450;
        }
        $this->image_size       = array($this->width, $this->height);

        if (!in_array($this->output, $this->_accepted_output)) {
            $this->output = 'png';
        }

        return $this;
    }

    /**
     * Override method in parent class
     *
     * @return void
     */ 
    public function addCoordinates()
    {
        if ($this->url || $this->points) {
            if ($this->url) {
                $this->_parseUrl();
            }
            if ($this->points) {
                $this->_parsePoints();
            }
            if ($this->zoom) {
                $this->_setZoom();
            }
        }

        $col = 0;
        foreach ($this->_coord_cols as $col => $coords) {
            $mlayer = ms_newLayerObj($this->map_obj);
            $mlayer->set("name", isset($this->legend[$col]) ? $this->legend[$col] : "");
            $mlayer->set("status", MS_ON);
            $mlayer->set("type", MS_LAYER_POINT);
            $mlayer->set("tolerance", 5);
            $mlayer->set("toleranceunits", 6);
            $mlayer->setProjection(parent::getProjection($this->default_projection));

            $class = ms_newClassObj($mlayer);
            $class->set("name", isset($this->legend[$col]) ? stripslashes($this->legend[$col]) : "");

            $style = ms_newStyleObj($class);
            $style->set("symbolname", (array_key_exists($col, $this->shape) && in_array($this->shape[$col], parent::$accepted_shapes)) ? $this->shape[$col] : 'circle');
            $style->set("size", (array_key_exists($col, $this->size)) ? $this->size[$col] : 8);

            if (array_key_exists($col, $this->color)) {
                $color = explode(",", $this->color[$col]);
                $style->color->setRGB(
                    (array_key_exists(0, $color)) ? $color[0] : 0,
                    (array_key_exists(1, $color)) ? $color[1] : 0,
                    (array_key_exists(2, $color)) ? $color[2] : 0
                );
            } else {
                $style->color->setRGB(0, 0, 0);
            }

            if ($this->outlinecolor && substr($class->getStyle(0)->symbolname, 0, 4) != 'open') {
                $outlinecolor = explode(",", $this->outlinecolor);
                $style->outlinecolor->setRGB(
                    (array_key_exists(0, $outlinecolor)) ? $outlinecolor[0] : 255,
                    (array_key_exists(1, $outlinecolor)) ? $outlinecolor[1] : 255,
                    (array_key_exists(2, $outlinecolor)) ? $outlinecolor[2] : 255
                );
            }

            $mcoord_shape = ms_newShapeObj(MS_SHAPE_POINT);
            $mcoord_line = ms_newLineObj();

            //add all the points
            foreach ($coords as $coord) {
                $_coord = new \stdClass();
                $_coord->x = array_key_exists(1, $coord) ? parent::cleanCoord($coord[1]) : null;
                $_coord->y = array_key_exists(0, $coord) ? parent::cleanCoord($coord[0]) : null;
                //only add point when data are good
                if (parent::checkOnEarth($_coord)) {
                    $mcoord_point = ms_newPointObj();
                    $mcoord_point->setXY($_coord->x, $_coord->y);
                    $mcoord_line->add($mcoord_point);
                }
            }

            $mcoord_shape->add($mcoord_line);
            $mlayer->addFeature($mcoord_shape);

            $col++;
        }
    }

    /**
     * Override method in the parent class
     *
     * @return void
     */
    public function addRegions()
    {
        if ($this->regions['data']) {            
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", "stateprovinces_polygon");
            $layer->set("data", $this->shapes['stateprovinces_polygon']['shape']);
            $layer->set("type", $this->shapes['stateprovinces_polygon']['type']);
            $layer->set("template", "template.html");
            $layer->setProjection(parent::getProjection($this->default_projection));

            //grab the data for regions & split
            $whole = trim($this->regions['data']);
            $rows = explode("\n", parent::removeEmptyLines($whole));
            $qry = array();
            foreach ($rows as $row) {
                $regions = preg_split("/[,;]+/", $row); //split by a comma, semicolon
                foreach ($regions as $region) {
                    $pos = strpos($region, '[');
                    if ($pos !== false) {
                        $split = explode("[", str_replace("]", "", trim(strtoupper($region))));
                        $states = preg_split("/[\s|]+/", $split[1]);
                        $statekey = array();
                        foreach ($states as $state) {
                            $statekey[] = "'[code_hasc]' ~* '\.".$state."$'";
                        }
                        $qry[] = "'[adm0_a3]' = '".trim($split[0])."' && (".implode(" || ", $statekey).")";
                    } else {
                        $region = addslashes(ucwords(strtolower(trim($region))));
                        $qry[] = "'[name]' ~* '".$region."$' || '[admin]' ~* '".$region."$'";
                    }
                }
            }

            $layer->setFilter("(".implode(" || ", $qry).")");
            $class = ms_newClassObj($layer);
            $class->set("name", stripslashes($this->regions['title']));

            $style = ms_newStyleObj($class);
            $color = ($this->regions['color']) ? explode(' ', $this->regions['color']) : explode(" ", "0 0 0");
            $style->color->setRGB($color[0], $color[1], $color[2]);
            $style->outlinecolor->setRGB(30, 30, 30);

            $layer->set("status", MS_ON);
        }
    }

    /**
     * Override method in parent class
     *
     * @return void
     */
    public function addGraticules()
    {
        if ($this->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(parent::getProjection($this->default_projection));

            $class = ms_newClassObj($layer);

            if ($this->gridlabel == "true") {
                $label = new \labelObj();
                $label->set("encoding", "UTF-8");
                $label->set("font", "arial");
                $label->set("size", 10);
                $label->set("position", MS_UC);
                $label->color->setRGB(30, 30, 30);
                $class->addLabel($label);
            }

            $style = ms_newStyleObj($class);
            $style->color->setRGB(200, 200, 200);

            $minx = $this->map_obj->extent->minx;
            $maxx = $this->map_obj->extent->maxx;

            $maxarcs = abs($maxx-$minx)/24;

            if ($maxarcs >= 5) {
                $labelformat = "DD";
            }
            if ($maxarcs < 5) {
                $labelformat = "DDMM";
            }
            if ($maxarcs <= 1) {
                $labelformat = "DDMMSS";
            }

            $maxinterval = ($this->gridspace) ? $this->gridspace : $maxarcs;
            $maxsubdivide = 2;

            $string  = 'LAYER name "grid"' . "\n";
            $string .= 'GRID' . "\n";
            $string .= 'labelformat "'.$labelformat.'" maxarcs '.$maxarcs.' maxinterval '.$maxinterval.' maxsubdivide '.$maxsubdivide . "\n";
            $string .= 'END' . "\n";
            $string .= 'END' . "\n";
            $layer->updateFromString($string);
        }
    }

    /**
     * Override method in parent class
     *
     * @return void
     */
    public function addScalebar()
    {
        $this->map_obj->scalebar->set("style", 0);
        $this->map_obj->scalebar->set("intervals", ($this->width <= 500) ? 2 : 3);
        $this->map_obj->scalebar->set("height", 8);
        $this->map_obj->scalebar->set("width", ($this->width <= 500) ? 100 : 200);
        $this->map_obj->scalebar->color->setRGB(30, 30, 30);
        $this->map_obj->scalebar->backgroundcolor->setRGB(255, 255, 255);
        $this->map_obj->scalebar->outlinecolor->setRGB(0, 0, 0);
        $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->map_obj->scalebar->label->set("encoding", "UTF-8");
        $this->map_obj->scalebar->label->set("font", "arial");
        $this->map_obj->scalebar->label->set("size", ($this->width <= 500) ? 8 : 10);
        $this->map_obj->scalebar->label->color->setRGB(0, 0, 0);

        //svg format cannot do scalebar in MapServer
        if ($this->output != 'svg') {
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
    }

    /**
     * Override method to add legen in parent class
     *
     * @return void
     */
    public function addLegend()
    {
        $this->map_obj->legend->set("postlabelcache", 1);
        $this->map_obj->legend->label->set("font", "arial");
        $this->map_obj->legend->label->set("position", 1);
        $this->map_obj->legend->label->set("size", ($this->width <= 500) ? 8 : 10);
        $this->map_obj->legend->label->color->setRGB(0, 0, 0);

        //svg format cannot do legends in MapServer
        if ($this->options['legend'] && $this->output != 'svg') {
            $this->map_obj->legend->set("status", MS_EMBED);
            $this->map_obj->legend->set("position", MS_UR);
            $this->map_obj->drawLegend();
        }
    }

    /**
     * Implemented createOutput method
     *
     * @return json_encoded $output
     */
    public function createOutput()
    {
        if ($this->ping) {
            Header::setHeader("json");
            return json_encode(array("status" => "ok"));
        } else {
            if ($this->method == 'GET') {
                Header::setHeader($this->output);
                $this->image->saveImage("");
            } else if ($this->method == 'OPTIONS') { //For CORS requests
                http_response_code(204);
            } else {
                Header::setHeader("json");
                $output = array(
                    'imageURL' => $this->image->saveWebImage(),
                    'expiry'   => date('c', time() + (6 * 60 * 60))
                );
                return json_encode($output);
            }
        }
    }

    /**
     * Move an uploaded file
     *
     * @return string The path of the uploaded file
     */
    private function _moveFile()
    {
        $uploadfile = MAPPR_UPLOAD_DIRECTORY . "/" . md5(time()) . '.txt';
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            if (mime_content_type($uploadfile) == "text/plain") {
                return $uploadfile;
            } else {
                unlink($uploadfile);
            }
        }
        return false;
    }

    /**
     * Set a zoom level
     *
     * @return void
     */
    private function _setZoom()
    {
        if ($this->zoom == 0 || $this->zoom > 10) {
            return;
        }
        $midpoint = $this->_getMidpoint($this->_coord_cols);
        $x = $this->map_obj->width*(($midpoint[0] + 180)/360);
        $y = $this->map_obj->height*((90 - $midpoint[1])/180);
        $zoom_point = ms_newPointObj();
        $zoom_point->setXY($x, $y);
        $this->map_obj->zoompoint($this->zoom*2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
    }

    /**
     * Find the geographic midpoint of a nested array of exploded dd coords
     *
     * @param array $array Array of coordinates
     *
     * @return array(long,lat)
     */
    private function _getMidpoint($array)
    {
        $x = $y = $z = array();
        foreach ($array as $coords) {
            foreach ($coords as $coord) {
                if (isset($coord[0]) && isset($coord[1])) {
                    $rx = deg2rad($coord[1]);
                    $ry = deg2rad($coord[0]);
                    $x[] = cos($ry)*cos($rx);
                    $y[] = cos($ry)*sin($rx);
                    $z[] = sin($ry);
                }
            }
        }
        $X = array_sum($x)/count($x);
        $Y = array_sum($y)/count($y);
        $Z = array_sum($z)/count($z);
        return array(rad2deg(atan2($Y, $X)), rad2deg(atan2($Z, sqrt(pow($X, 2) + pow($Y, 2)))));
    }

    /**
     * Parse all POSTed data into cleaned array of points
     *
     * @return void
     */
    private function _parsePoints()
    {
        $num_cols = (isset($num_cols)) ? $num_cols++ : 0;
        $coord_array = array();
        foreach ($this->points as $rows) {
            $row = preg_split("/[\r\n]|(\\\[rn])/", urldecode(parent::removeEmptyLines($rows)));
            foreach (str_replace("\\", "", $row) as $point) {
                $this->_coord_cols[$num_cols][] = parent::makeCoordinates($point);
            }
            $num_cols++;
        }
    }

    /**
     * Discover format of URL and parse it
     *
     * @return void
     */
    private function _parseUrl()
    {
        if (strstr($this->url, MAPPR_UPLOAD_DIRECTORY)) {
            $this->_parseFile();
            unlink($this->url);
        } else {
            $headers = get_headers($this->url, 1);
            if (array_key_exists('Location', $headers)) {
                $this->url = array_pop($headers['Location']);
            }
            $this->url_content = @file_get_contents($this->url);
            preg_match_all('/[<>{}\[\]]/', $this->url_content, $match);
            if (count($match[0]) >= 4) {
                $this->_parseGeo();
            } else {
                $this->_parseFile();
            }
        }
    }

    /**
     * Parse text file into cleaned array of points
     *
     * @return void
     */
    private function _parseFile()
    {
        if (@$fp = fopen($this->url, 'r')) {
            while ($line = fread($fp, 1024)) {
                $rows = preg_split("/[\r\n]+/", $line, -1, PREG_SPLIT_NO_EMPTY);
                $cols = explode("\t", $rows[0]);
                $num_cols = count($cols);
                $this->legend = explode("\t", $rows[0]);
                unset($rows[0]);
                foreach ($rows as $row) {
                    $cols = explode("\t", $row);
                    for ($i=0;$i<$num_cols;$i++) {
                        if (array_key_exists($i, $cols)) {
                            $cols[$i] = preg_replace('/[\p{Z}\s]/u', ' ', $cols[$i]);
                            $cols[$i] = trim(preg_replace('/[^\d\s,;.\-NSEWO°dms\'"]/i', "", $cols[$i]));
                            if (preg_match('/[NSEWO]/', $cols[$i]) != 0) {
                                $coord = preg_split("/[,;]/", $cols[$i]);
                                $coord = (preg_match('/[EWO]/', $coord[1]) != 0) ? $coord : array_reverse($coord);
                                $this->_coord_cols[$i][] = array(
                                    parent::dmsToDeg(trim($coord[0])),
                                    parent::dmsToDeg(trim($coord[1]))
                                );
                            } else {
                                $this->_coord_cols[$i][] = preg_split("/[\s,;]+/", trim(preg_replace("/[^0-9-\s,;.]/", "", $cols[$i])));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Parse GeoRSS, GeoJSON, WKT, KML into cleaned array of points
     *
     * @return void
     */
    private function _parseGeo()
    {
        $geometries = \geoPHP::load($this->url_content);
        if ($geometries) {
            $num_cols = (isset($num_cols)) ? $num_cols++ : 0;
            $this->legend[$num_cols] = $this->url;
            foreach ($geometries as $geometry) {
                foreach ($geometry as $item) {
                    if ($item->geometryType() == 'Point') {
                        $this->_coord_cols[$num_cols][] = array_reverse($item->coords);
                    }
                }
            }
        }
    }

}