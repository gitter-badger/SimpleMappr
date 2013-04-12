<?php

/**************************************************************************

File: mapprservice.pptx.class.php

Description: Produce a PPTX file from SimpleMappr. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

require_once ('mapprservice.class.php');

/** PHPPowerPoint */
set_include_path(MAPPR_DIRECTORY . '/lib/PHPPowerPoint/');
include_once 'PHPPowerPoint.php';
include_once 'PHPPowerPoint/IOFactory.php';

class MAPPRPPTX extends MAPPR {

  private $_slidepadding = 25;

  /**
  * Get a user-defined file name, cleaned of illegal characters
  * @return string
  */
  public function get_file_name() {
    return preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $this->file_name);
  }

  public function get_output() {
      $objPHPPowerPoint = new PHPPowerPoint();

      // Set properties
      $objPHPPowerPoint->getProperties()->setCreator("SimpleMappr");
      $objPHPPowerPoint->getProperties()->setLastModifiedBy("SimpleMappr");
      $objPHPPowerPoint->getProperties()->setTitle($this->get_file_name());
      $objPHPPowerPoint->getProperties()->setSubject($this->get_file_name() . " point map");
      $objPHPPowerPoint->getProperties()->setDescription($this->get_file_name() . ", generated on SimpleMappr, http://www.simplemappr.net");
      $objPHPPowerPoint->getProperties()->setKeywords($this->get_file_name() . " SimpleMappr");

      // Create slide
      $currentSlide = $objPHPPowerPoint->getActiveSlide();
      $currentSlide->setSlideLayout(PHPPowerPoint_Slide_Layout::TITLE_AND_CONTENT);

      $width = 950;
      $height = 720;

      $files = array();
      $images = array('image', 'scale', 'legend');
      foreach($images as $image) {
        if($this->{$image}) {
          $image_filename = basename($this->{$image}->saveWebImage());
          $files[$image]['file'] = $this->tmp_path . $image_filename;
          $files[$image]['size'] = getimagesize($files[$image]['file']);
        }
      }

      $scale = 1;
      $scaled_w = $files['image']['size'][0];
      $scaled_h = $files['image']['size'][1];
      if($scaled_w > $width || $scaled_h > $height) {
        $scale = ($scaled_w/$width > $scaled_h/$height) ? $scaled_w/$width : $scaled_h/$height;
      }

      foreach($files as $type => $value) {
        $size = getimagesize($value['file']);
        $shape = $currentSlide->createDrawingShape();
        $shape->setName('SimpleMappr ' . $this->get_file_name());
        $shape->setDescription('SimpleMappr ' . $this->get_file_name());
        $shape->setPath($value['file']);
        $shape->setWidth(round($value['size'][0]/$scale));
        $shape->setHeight(round($value['size'][1]/$scale));
        $shape_width = $shape->getWidth();
        $shape_height = $shape->getHeight();
        if($type == 'image') {
          $shape->setOffsetX(($width-$shape_width)/2);
          $shape->setOffsetY(($height-$shape_height)/2);
          $shape->getAlignment()->setHorizontal(PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
        }
        if($type == 'scale') {
          $shape->setOffsetX($width-round($shape_width*1.5)-$this->_slidepadding);
          $shape->setOffsetY($height-round($shape_height*4)-$this->_slidepadding);
        }
        if($type == 'legend') {
          $shape->setOffsetX($width-$shape_width-$this->_slidepadding);
          $shape->setOffsetY(200);
        }
      }

      $shape = $currentSlide->createRichTextShape();
      $shape->setHeight(25);
      $shape->setWidth(450);
      $shape->setOffsetX($width - 450);
      $shape->setOffsetY($height - 10 - $this->_slidepadding);
      $shape->getAlignment()->setHorizontal(PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT);
      $shape->getAlignment()->setVertical(PHPPowerPoint_Style_Alignment::VERTICAL_CENTER);
      $textRun = $shape->createTextRun(_("Created with SimpleMappr, http://www.simplemappr.net"));
      $textRun->getFont()->setBold(true);
      $textRun->getFont()->setSize(12);

      // Output PowerPoint 2007 file
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private",false);
      header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
      header("Content-Disposition: attachment; filename=\"" . $this->get_file_name() . ".pptx\";" );
      header("Content-Transfer-Encoding: binary");
      $objWriter = PHPPowerPoint_IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
      $objWriter->save('php://output');
      exit();
  }

}
?>