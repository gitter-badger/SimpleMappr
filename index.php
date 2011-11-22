<?php
require_once('config/conf.php');
$header = set_up();
header('Content-Type: text/html; charset=utf-8');
$language = isset($_GET["lang"]) ? $_GET["lang"] : 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
<meta charset="UTF-8">
<meta name="description" content="<?php echo _("A publication-quality, point map application."); ?>" />
<meta name="keywords" content="<?php echo _("publication,map"); ?>" />
<meta name="author" content="David P. Shorthouse" />
<title>SimpleMappr</title>
<link type="image/x-icon" href="favicon.ico" rel="SHORTCUT ICON" />
<?php $header[0]->getCSSHeader(); ?>
</head>
<?php flush(); ?>
<body>
<div id="header" class="clearfix">
<h1 id="site-title" class="sprites">SimpleMapp<span>r</span></h1>
<div id="site-tagline"><?php echo _("point maps for publication and presentation"); ?></div>
<div id="site-languages">
<!--
<ul><?php foreach($header[1] as $key => $langs): ?><li><?php if($key == 'en'): ?><?php echo '<a href="/" onclick="javascript: Mappr.clearLanguage();">'.$langs['native'].'</a>'; ?><?php else: ?><?php echo '<a href="/?lang='.$key.'">'.$langs['native'].'</a>'; ?><?php endif; ?></li><?php endforeach; ?></ul>
-->
</div>
<?php if(isset($_SESSION['simplemappr'])): ?>
<div id="site-logout"><?php echo $_SESSION['simplemappr']['username']; ?> <span><a class="sprites site-logout" href="/logout/"><?php echo _("Sign Out"); ?></a></span></div>
<?php else: ?>
<div id="site-logout"><span><a class="sprites site-login" href="#" onclick="javascript:Mappr.tabSelector(3);return false;"><?php echo _("Sign In"); ?></a></span></div>
<?php endif; ?>
</div>
<div id="wrapper">
<div id="initial-message" class="ui-corner-all ui-widget-content"><?php echo _("Building application..."); ?></div>
<div id="tabs">
<ul class="navigation">
<li><a href="#map-preview"><?php echo _("Preview"); ?></a></li>
<li><a href="#map-points"><?php echo _("Point Data"); ?></a></li>
<li><a href="#map-regions"><?php echo _("Regions"); ?></a></li>
<li><a href="#map-mymaps" class="sprites map-mymaps"><?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?><?php echo _("All Maps"); ?><?php else: ?><?php echo _("My Maps"); ?><?php endif; ?></a></li>
<?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
<li><a href="#map-users" class="sprites map-users"><?php echo _("Users"); ?></a></li>
<?php endif; ?>
<?php $qlang = isset($_GET['lang']) ? "?lang=" . $_GET["lang"] : ""; ?>
<li class="map-extras"><a href="tabs/help.php<?php echo $qlang; ?>" class="sprites map-myhelp"><?php echo _("Help"); ?></a></li>
<li class="map-extras"><a href="tabs/about.php<?php echo $qlang; ?>"><?php echo _("About"); ?></a></li>
<li class="map-extras"><a href="tabs/feedback.php<?php echo $qlang; ?>"><?php echo _("Feedback"); ?></a></li>
<li class="map-extras"><a href="tabs/api.php<?php echo $qlang; ?>"><?php echo _("API"); ?></a></li>
</ul>
<form id="form-mapper" action="application/" method="post" autocomplete = "off">  

<!-- multipoint tab -->
<div id="map-points">
<div id="general-points" class="panel ui-corner-all">
<p><?php echo _("Type geographic coordinates on separate lines in decimal degrees as latitude longitude (separated by a space, comma, or semicolon)"); ?> <a href="#" onclick="javascript:Mappr.showExamples(); return false;" class="sprites help"><?php echo _("examples"); ?></a></p>
</div>
<div id="fieldSetsPoints" class="fieldSets">
<?php echo partial_layers(); ?>
</div>
<div class="addFieldset"><button class="sprites addmore positive ui-corner-all" data-type="coords"><?php echo _("Add a layer"); ?></button></div>
<div class="submit"><button class="sprites submitForm positive ui-corner-all"><?php echo _("Preview"); ?></button><button class="sprites clear clearLayers negative ui-corner-all"><?php echo _("Clear all"); ?></button></div>
</div>

<!-- shaded regions tab -->
<div id="map-regions">
<div id="regions-introduction" class="panel ui-corner-all">
<?php $tabIndex = (isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1) ? 5 : 4; ?>
<p><?php echo _("Type countries as Mexico, Venezuela AND/OR bracket pipe- or space-separated State/Province codes prefixed by 3-letter ISO country code <em>e.g.</em>USA[VA], CAN[AB ON]."); ?> <a href="#" onclick="javascript:Mappr.tabSelector(<?php echo $tabIndex; ?>);return false;" class="sprites help"><?php echo _("codes"); ?></a></p>
</div>
<div id="fieldSetsRegions" class="fieldSets">
<?php echo partial_regions(); ?>
</div>
<div class="addFieldset"><button class="sprites addmore positive ui-corner-all" data-type="regions"><?php echo _("Add a region"); ?></button></div>
<div class="submit"><button class="sprites submitForm positive ui-corner-all"><?php echo _("Preview"); ?></button><button class="sprites clear clearRegions negative ui-corner-all"><?php echo _("Clear all"); ?></button></div>
</div>

<!-- map preview tab -->
<div id="map-preview">
<div id="mapWrapper">
<div id="actionsBar" class="ui-widget-header ui-corner-all ui-helper-clearfix">
<ul class="dropdown">
<li><a href="#" class="sprites tooltip toolsZoomIn" title="<?php echo _("zoom in ctrl+"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsZoomOut" title="<?php echo _("zoom out ctrl-"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsCrop" title="<?php echo _("crop ctrl+x"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsQuery" title="<?php echo _("fill regions"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsRefresh" title="<?php echo _("refresh ctrl+r"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsRebuild" title="<?php echo _("rebuild ctrl+n"); ?>"></a></li>
</ul>
<h3 id="mapTitle"></h3>
<div id="map-saveDialog">
<?php if(isset($_SESSION['simplemappr'])): ?>
<span><a class="sprites tooltip map-saveItem map-save" href="#" title="save ctrl+s"><?php echo _("Save"); ?></a></span>
<span><a class="sprites tooltip map-saveItem map-embed" href="#" title="embed" data-mid=""><?php echo _("Embed"); ?></a></span>
<?php endif; ?>
<span><a class="sprites map-saveItem map-download tooltip" href="#" title="download ctrl+d"><?php echo _("Download"); ?></a></span>
</div>
</div>
<div id="map">
<div id="mapImage">
<div id="mapControlsTransparency"></div>
<div id="mapControls">
<div class="viewport">
<ul class="overview">
<?php echo rotation_values() . "\n"; ?>
</ul>
</div>
<div class="dot"></div>
<div class="overlay">
<a href="#" class="sprites tooltip controls arrows up" data-pan="up" title="<?php echo _("pan up"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows right" data-pan="right" title="<?php echo _("pan right"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows down" data-pan="down" title="<?php echo _("pan down"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows left" data-pan="left" title="<?php echo _("pan left"); ?>"></a>
</div>
<div class="thumb ui-corner-all ui-widget-header"></div>
</div>
<div id="badRecordsWarning"><a href="#" class="sprites toolsBadRecords"><?php echo _("Records Out of Range"); ?></a></div>
<div id="mapOutput"><span class="mapper-loading-message ui-corner-all ui-widget-content"><?php echo _("Building preview..."); ?></span></div>
</div>
<div id="mapScale"></div>
</div>
<div id="mapTools">
<ul>
<li><a href="#mapOptions"><?php echo _("Settings"); ?></a></li>
<li><a href="#mapLegend"><?php echo _("Legend"); ?></a></li>
</ul>
<div id="mapLegend"><p><em><?php echo _("legend will appear here"); ?></em></p></div>
<div id="mapOptions">
<h2><?php echo _("Layers"); ?></h2>
<ul class="columns ui-helper-clearfix">
<li><input type="checkbox" id="stateprovince" class="layeropt" name="layers[stateprovinces]" /> <?php echo _("State/Provinces"); ?></li>
<li><input type="checkbox" id="lakesOutline" class="layeropt" name="layers[lakesOutline]" /> <?php echo _("lakes (outline)"); ?></li>
<li><input type="checkbox" id="lakes" class="layeropt" name="layers[lakes]" /> <?php echo _("lakes (greyscale)"); ?></li>
<li><input type="checkbox" id="rivers" class="layeropt" name="layers[rivers]" /> <?php echo _("rivers"); ?></li>
<li><input type="checkbox" id="relief" class="layeropt" name="layers[relief]" /> <?php echo _("relief"); ?></li>
<li><input type="checkbox" id="reliefgrey" class="layeropt" name="layers[reliefgrey]" /> <?php echo _("relief (greyscale)"); ?></li>
</ul>
<h2><?php echo _("Labels"); ?></h2>
<ul class="columns ui-helper-clearfix">
<li><input type="checkbox" id="countrynames" class="layeropt" name="layers[countrynames]" /> <?php echo _("Countries"); ?></li>
<li><input type="checkbox" id="stateprovincenames" class="layeropt" name="layers[stateprovnames]" /> <?php echo _("State/Provinces"); ?></li>
<li><input type="checkbox" id="lakenames" class="layeropt" name="layers[lakenames]" /> <?php echo _("lakes"); ?></li>
<li><input type="checkbox" id="rivernames" class="layeropt" name="layers[rivernames]" /> <?php echo _("rivers"); ?></li>
<li><input type="checkbox" id="placenames" class="layeropt" name="layers[placenames]" /> <?php echo _("places"); ?></li>
<li><input type="checkbox" id="physicalLabels" class="layeropt" name="layers[physicalLabels]" /> <?php echo _("physical"); ?></li>
<li><input type="checkbox" id="marineLabels" class="layeropt" name="layers[marineLabels]" /> <?php echo _("marine"); ?></li>
</ul>
<h2><?php echo _("Options"); ?></h2>
<ul>
<li><input type="checkbox" id="graticules"  class="layeropt" name="layers[grid]" /> <?php echo _("graticules (grid)"); ?>
<div id="graticules-selection">
<input type="radio" id="gridspace" class="gridopt" name="gridspace" value="" checked="checked" /> <?php echo _("fixed"); ?>
<input type="radio" id="gridspace-5" class="gridopt" name="gridspace" value="5" /> 5<sup>o</sup>
<input type="radio" id="gridspace-10" class="gridopt" name="gridspace" value="10" /> 10<sup>o</sup>
</div>
</li>
</ul>
<h2><?php echo _("Projection"); ?>*</h2>
<ul>
<li>
<select id="projection" name="projection">
<?php
foreach(MAPPR::$accepted_projections as $key => $value) {
$selected = ($value['name'] == 'Geographic') ? ' selected="selected"': '';
echo '<option value="'.$key.'"'.$selected.'>'.$value['name'].'</option>' . "\n";
}
?>
</select>
</li>
</ul>
<p>*<?php echo _("zoom prior to setting projection"); ?></p>
</div>
</div>
</div>
</div>

<!-- my maps tab -->
<div id="map-mymaps">
<?php if(!isset($_SESSION['simplemappr'])): ?>
<div class="panel ui-corner-all">
<p><?php echo _("Save and reload your map data or create a generic template."); ?></p> 
</div>
<div id="janrainEngageEmbed"></div>
<?php else: ?>
<div id="usermaps"></div>
<?php endif; ?>
</div>

<!-- users tab -->
<?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
<div id="map-users">
<div id="userdata"></div>
</div>
<?php endif; ?>
<div id="badRecordsViewer" title="<?php echo _("Records out of range"); ?>"><div id="badRecords"></div></div>
<div id="mapSave" title="<?php echo _("Save"); ?>">
<p>
<label for="m-mapSaveTitle"><?php echo _("Title"); ?><span class="required">*</span></label>
<input type="text" id="m-mapSaveTitle" class="m-mapSaveTitle" size="30" maxlength="30" />
</p>
</div>
<div id="mapExport" title="Download">
<div class="download-dialog">
<p id="mapCropMessage" class="sprites"><?php echo _("map will be cropped"); ?></p>
<p>
<label for="file-name"><?php echo _("File name"); ?></label>
<input type="text" id="file-name" maxlength="30" size="30" />
</p>
<fieldset>
<legend><?php echo _("Scale"); ?></legend>
<?php echo partial_scales(); ?>
<div id="scale-measure"><?php echo sprintf(_("Dimensions: %s"), '<span></span>')?></div>
</fieldset>
<fieldset>
<legend><?php echo _("File type"); ?></legend>
<?php echo partial_filetypes(); ?>
</fieldset>
<fieldset>
<legend><?php echo _("Options"); ?></legend>
<input type="checkbox" id="border" />
<label for="border"><?php echo _("include border"); ?></label>
<input type="checkbox" id="legend" disabled="disabled" />
<label for="legend"><?php echo _("embed legend"); ?></label>
<input type="checkbox" id="scalebar" disabled="disabled" />
<label for="scalebar"><?php echo _("embed scalebar"); ?></label>
</fieldset>
<p>*<?php echo _("svg does not include scalebar, legend, or relief layers"); ?></p>
</div>
<div class="download-message"><?php echo _("Building file for download..."); ?></div>
</div>
<input type="hidden" name="download" id="download"/>
<input type="hidden" name="output" id="output" />
<input type="hidden" name="download_token" id="download_token"/>
<input type="hidden" name="bbox_map" id="bbox_map" />
<input type="hidden" name="projection_map" id="projection_map" />
<input type="hidden" name="bbox_rubberband" id="bbox_rubberband" />
<input type="hidden" name="bbox_query" id="bbox_query" />
<input type="hidden" name="pan" id="pan" />
<input type="hidden" name="zoom_out" id="zoom_out" />
<input type="hidden" name="crop" id="crop" />
<input type="hidden" name="rotation" id="rotation" />
<input type="hidden" name="selectedtab" id="selectedtab" />
<input type="hidden" name="save[title]" />
<input type="hidden" name="file_name" />
<input type="hidden" name="download_factor" />
<input type="hidden" name="download_filetype" />
<input type="hidden" name="grid_space" />
<input type="hidden" name="options[border]" />
<input type="hidden" name="options[legend]" />
<input type="hidden" name="options[scalebar]" />
<input type="hidden" id="rendered_bbox" value="" />
<input type="hidden" id="rendered_rotation" value="" />
<input type="hidden" id="rendered_projection" value="" />
<input type="hidden" id="legend_url" value="" />
<input type="hidden" id="scalebar_url" value="" />
<input type="hidden" id="bad_points" value="" />
</form>
<!-- close tabs wrapper -->
</div>
</div>
<div id="mapper-message" class="ui-state-error" title="<?php echo _("Warning"); ?>"></div>
<div id="button-titles" class="hidden-message">
  <span class="save"><?php echo _("Save"); ?></span>
  <span class="cancel"><?php echo _("Cancel"); ?></span>
  <span class="download"><?php echo _("Download"); ?></span>
  <span class="delete"><?php echo _("Delete"); ?></span>
</div>
<div id="mapper-loading-message" class="hidden-message"><?php echo _("Building preview..."); ?></div>
<div id="mapper-saving-message" class="hidden-message"><?php echo _("Saving..."); ?></div>
<div id="mapper-missing-legend" class="hidden-message"><?php echo _("You are missing a legend for at least one of your Point Data or Regions layers."); ?></div>
<div class="usermaps-loading hidden-message"><span class="mapper-loading-message ui-corner-all ui-widget-content"><?php echo _("Loading your maps..."); ?></span></div>
<div class="userdata-loading hidden-message"><span class="mapper-loading-message ui-corner-all ui-widget-content"><?php echo _("Loading user list..."); ?></span></div>
<div id="mapper-message-delete" class="ui-state-highlight hidden-message" title="<?php echo _("Delete"); ?>"><?php echo _("Are you sure you want to delete"); ?> <span></span>?</div>
<div id="mapper-legend-message" class="hidden-message"><?php echo _("legend will appear here"); ?></div>
<div id="mapper-message-help" class="ui-state-highlight hidden-message" title="<?php echo _("Example Coordinates"); ?>"></div>
<div id="mapEmbed" class="ui-state-highlight hidden-message" title="<?php echo _("Embed"); ?>">
  <div class="header"><h2><?php echo _('Image'); ?></h2></div>
  <p><input id="embed-img" type="text" size="65" value="" /></p>
  <p><strong><?php echo _("Additional parameters"); ?></strong>:<br><span class="indent"><?php echo _("width, height"); ?> (<em>e.g.</em> /map/<span class="mid"></span>?width=200&amp;height=150)</span></p>
  <div class="header"><h2><?php echo _('KML'); ?></h2></div>
  <p><input id="embed-kml" type="text" size="65" value="" /></p>
  <div class="header"><h2><?php echo _('GeoJSON'); ?></h2></div>
  <p><input id="embed-json" type="text" size="65" value="" /></p>
</div>
<?php $header[0]->getJSHeader();?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
jQuery.extend(Mappr.settings, { "baseUrl": "http://<?php echo $_SERVER['HTTP_HOST']; ?>", "active" : <?php echo (isset($_SESSION['simplemappr'])) ? '"true"' : '"false"'; ?> });
//--><!]]>
</script>
<?php $header[0]->getAnalytics(); ?>
</body>
</html>
<?php

function set_up() {
  if(isset($_GET['map'])) {
    require_once('lib/mapprservice.embed.class.php');
    $mappr_embed = new MAPPREMBED();
    $mappr_embed->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
                ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
                ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
                ->set_tmp_url("/tmp");

    $mappr_embed->get_request()
                ->execute()
                ->get_output();
    exit();
  } else {
    $host = explode(".", $_SERVER['HTTP_HOST']);
    if(ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
      header('Location: http://www.simplemappr.net/');
    } else {
      require_once('lib/mapprservice.usersession.class.php');
      require_once('lib/mapprservice.header.class.php');
      require_once('lib/mapprservice.class.php');

      USERSESSION::update_activity();

      return array(new HEADER, USERSESSION::$accepted_languages);
    }
  }
}

function partial_layers() {
  //marker sizes and shapes
  $marker_size  = '<option value="">'._("--select--").'</option>';
  $marker_size .= '<option value="6">6pt</option>';
  $marker_size .= '<option value="8">8pt</option>';
  $marker_size .= '<option value="10" selected="selected">10pt</option>';
  $marker_size .= '<option value="12">12pt</option>';
  $marker_size .= '<option value="14">14pt</option>';
  $marker_size .= '<option value="16">16pt</option>';

  $marker_shape  = '<option value="">'._("--select--").'</option>';
  $marker_shape .= '<option value="plus">'._("plus").'</option>';
  $marker_shape .= '<option value="cross">'._("cross").'</option>';
  $marker_shape .= '<optgroup label="'._("solid").'">';
  $marker_shape .= '<option value="circle" selected="selected">'._("circle (s)").'</option>';
  $marker_shape .= '<option value="star">'._("star (s)").'</option>';
  $marker_shape .= '<option value="square">'._("square (s)").'</option>';
  $marker_shape .= '<option value="triangle">'._("triangle (s)").'</option>';
  $marker_shape .= '</optgroup>';
  $marker_shape .= '<optgroup label="'._("open").'">';
  $marker_shape .= '<option value="opencircle">'._("circle (o)").'</option>';
  $marker_shape .= '<option value="openstar">'._("star (o)").'</option>';
  $marker_shape .= '<option value="opensquare">'._("square (o)").'</option>';
  $marker_shape .= '<option value="opentriangle">'._("triangle (o)").'</option>';
  $marker_shape .= '</optgroup>';

  $output = '';

  for($i=0;$i<=NUMTEXTAREA-1;$i++) {
    
    $output .= '<div class="form-item fieldset-points">';

    $output .= '<button class="sprites removemore negative ui-corner-all" data-type="coords">'._("Remove").'</button>';
  
    $output .= '<h3><a href="#">'.sprintf(_("Layer %d"),$i+1).'</a></h3>' . "\n";
    $output .= '<div>' . "\n";
    $output .= '<div class="fieldset-taxon">' . "\n";
    $output .= '<span class="fieldset-title">'._("Legend").'<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="coords['.$i.'][title]" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="resizable-textarea">' . "\n";
    $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="coords['.$i.'][data]"></textarea></span>' . "\n";
    $output .= '</div>' . "\n";

    $output .= '<div class="fieldset-extras">' . "\n";
    $output .= '<span class="fieldset-title">'._("Shape").':</span> <select class="m-mapShape" name="coords['.$i.'][shape]">'.$marker_shape.'</select> <span class="fieldset-title">'._("Size").':</span> <select class="m-mapSize" name="coords['.$i.'][size]">'.$marker_size.'</select>' . "\n";
    $output .= '<span class="fieldset-title">'._("Color").':</span> <input class="colorPicker" type="text" size="12" maxlength="11" name="coords['.$i.'][color]" value="0 0 0" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<button class="sprites clear clearself negative ui-corner-all">'._("Clear").'</button>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '</div>' . "\n";
  }

  return $output;
}

function partial_regions() {
  $output = '';

  for($i=0;$i<=NUMTEXTAREA-1;$i++) {
    $output .= '<div class="form-item fieldset-regions">';

    $output .= '<button class="sprites removemore negative ui-corner-all" data-type="regions">'._("Remove").'</button>';

    $output .= '<h3><a href="#">'.sprintf(_("Region %d"), $i+1).'</a></h3>' . "\n";
    $output .= '<div>' . "\n";
    $output .= '<div class="fieldset-taxon">' . "\n";
    $output .= '<span class="fieldset-title">'._("Legend").'<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="regions['.$i.'][title]" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="resizable-textarea">' . "\n";
    $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="regions['.$i.'][data]"></textarea></span>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '<div class="fieldset-extras">' . "\n";
    $output .= '<span class="fieldset-title">'._("Color").':</span> <input type="text" class="colorPicker" size="12" maxlength="11" name="regions['.$i.'][color]" value="150 150 150" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<button class="sprites clear clearself negative ui-corner-all">'._("Clear").'</button>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '</div>' . "\n";
  }

  return $output;
}

function partial_scales() {
  $output = '';

  $file_sizes = array(1,3,4,5);
  foreach($file_sizes as $size) {
    $checked = ($size == 1) ? ' checked="checked"' : '';
    $output .= '<input type="radio" id="download-factor-'.$size.'" class="download-factor" name="download-factor" value="'.$size.'"'.$checked.' />';
    $output .= '<label for="download-factor-'.$size.'">'.$size.'X</label>';
  }

  return $output;
}

function partial_filetypes() {
  $output = '';

  $file_types = array('svg', 'png', 'tif', 'pptx', 'kml');
  foreach($file_types as $type) {
    $checked = ($type == "svg") ? ' checked="checked"': '';
    $asterisk = ($type == "svg") ? '*' : '';
    $output .= '<input type="radio" id="download-'.$type.'" class="download-filetype" name="download-filetype" value="'.$type.'"'.$checked.' />';
    $output .= '<label for="download-'.$type.'">'.$type.$asterisk.'</label>';
  }

  return $output;
}

function rotation_values() {
  $output = "";

  for($i=0;$i<360;$i++) {
    if($i % 5 == 0) {
      $output .= '<li data-rotate="'.$i.'"></li>';
    }
  }
  return $output;
}
?>