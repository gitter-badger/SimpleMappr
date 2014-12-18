<?php
namespace SimpleMappr;

$locale = isset($_GET["locale"]) ? $_GET["locale"] : 'en_US';
$header_class = $header[0];
$accepted_locales = $header[1];
$roles = $header[2];
?>
<!DOCTYPE html>
<html lang="<?php echo $accepted_locales[$locale]['canonical']; ?>" prefix="og: http://ogp.me/ns#">
<head>
<meta charset="UTF-8">
<meta name="description" content="<?php echo _("Create free point maps for publications and presentations"); ?>" />
<meta name="keywords" content="<?php echo _("publication,presentation,map,georeference"); ?>" />
<meta name="author" content="David P. Shorthouse" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:site" content="@SimpleMappr" />
<meta name="twitter:creator" content="@dpsSpiders" />
<meta property="og:title" content="SimpleMappr" />
<meta property="og:description" content="<?php echo _("Create free point maps for publications and presentations"); ?>" />
<meta property="og:locale" content="<?php echo $locale; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>" />
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/public/images/logo_og.png" />
<title>SimpleMappr</title>
<?php $header_class->getCSSHeader(); ?>
<?php $header_class->getDNSPrefetch(); ?>
<?php foreach ($accepted_locales as $key => $locales): ?>
<link rel="alternate" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/?locale=<?php echo $key; ?>" hreflang="<?php echo $locales['hreflang']; ?>" />
<?php endforeach; ?>
</head>
<body>
<div itemscope itemtype="http://schema.org/WebApplication" id="header" class="clearfix">
<h1 id="site-title" itemprop="name">SimpleMapp<span>r</span></h1>
<div id="site-tagline" itemprop="description"><?php echo _("create free point maps for publications and presentations"); ?></div>
<meta itemprop="url" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>" />
<meta itemprop="image" content="http://<?php echo $_SERVER['HTTP_HOST']; ?>/public/images/logo_og.png" />
<div id="map-loader"><span class="mapper-loading-spinner"></span></div>
<div id="site-languages">
<ul><?php foreach ($accepted_locales as $key => $locales): ?><?php $selected = ''; if($key == $locale) { $selected = ' class="selected"'; } ?><li><?php echo '<a href="/?locale='.$key.'#tabs=0"'.$selected.'>'.$locales['native'].'</a>'; ?></li><?php endforeach; ?></ul>
</div>
<?php if (isset($_SESSION['simplemappr'])): ?>
<div id="site-user">
  <?php if (!empty($_SESSION['simplemappr']['displayname'])): ?>
    <?php echo $_SESSION['simplemappr']['displayname']; ?>
  <?php else: ?>
    <?php echo $_SESSION['simplemappr']['email']; ?>
  <?php endif; ?>
</div>
<?php endif; ?>
<div id="site-session">
<?php if (isset($_SESSION['simplemappr'])): ?>
<a class="sprites-before logout" href="/logout/"><?php echo _("Sign Out"); ?></a>
<?php else: ?>
<a class="sprites-before login" href="#"><?php echo _("Sign In"); ?></a>
<?php endif; ?>
</div>
</div>
<div id="wrapper">
<noscript>
<div id="noscript"><?php echo _("Sorry, you must enable JavaScript to use this site."); ?></div>
</noscript>
<div id="tabs">
<ul class="navigation">
<li><a href="#map-preview"><?php echo _("Preview"); ?></a></li>
<li><a href="#map-points"><?php echo _("Point Data"); ?></a></li>
<li><a href="#map-regions"><?php echo _("Regions"); ?></a></li>
<li><a href="#map-mymaps" class="sprites-before map-mymaps"><?php if (isset($_SESSION['simplemappr']) && $roles[$_SESSION['simplemappr']['role']] == 'administrator'): ?><?php echo _("All Maps"); ?><?php else: ?><?php echo _("My Maps"); ?><?php endif; ?></a></li>
<?php if (isset($_SESSION['simplemappr'])): ?>
<li><a href="#map-shares"><?php echo _("Shared Maps"); ?></a></li>
<?php endif; ?>
<?php if (isset($_SESSION['simplemappr']) && $roles[$_SESSION['simplemappr']['role']] == 'administrator'): ?>
<li><a href="#map-users" class="sprites-before map-users"><?php echo _("Users"); ?></a></li>
<li><a href="#map-admin"><?php echo _("Administration"); ?></a></li>
<?php endif; ?>
<?php $qlocale  = "?v=" . $header_class->getHash(); ?>
<?php $qlocale .= isset($_GET['locale']) ? "&locale=" . $_GET["locale"] : ""; ?>
<li class="map-extras"><a href="help<?php echo $qlocale; ?>" class="sprites-before map-myhelp"><?php echo _("Help"); ?></a></li>
<li class="map-extras"><a href="about<?php echo $qlocale; ?>"><?php echo _("About"); ?></a></li>
<li class="map-extras"><a href="feedback<?php echo $qlocale; ?>"><?php echo _("Feedback"); ?></a></li>
<li class="map-extras"><a href="apidoc<?php echo $qlocale; ?>"><?php echo _("API"); ?></a></li>
</ul>
<form id="form-mapper" accept-charset="UTF-8" action="application/" method="post" autocomplete="off">

<div id="map-points">
<div id="general-points" class="panel ui-corner-all">
<p><?php echo _("Type geographic coordinates on separate lines in decimal degrees (DD) or DD°MM'SS\" as latitude,longitude separated by a space (DD only), comma, or semicolon"); ?> <a href="#" class="sprites-before help show-examples"><?php echo _("examples"); ?></a></p>
</div>
<div id="upload-panel" class="panel ui-corner-all"><h3><?php echo _("Upload text or csv file"); ?></h3><p><input type="file" id="fileInput" /><a href="public/files/demo.txt"><?php echo _("Example 1"); ?></a>, <a href="public/files/demo2.csv"><?php echo _("Example 2"); ?></a></p></div>
<div id="fieldSetsPoints" class="fieldSets">
<?php $this->partial("point_layers"); ?>
</div>
<div class="addFieldset"><button class="sprites-before addmore positive ui-corner-all" data-type="coords"><?php echo _("Add a layer"); ?></button></div>
<div class="submit"><button class="sprites-before submitForm positive ui-corner-all"><?php echo _("Preview"); ?></button><button id="clearLayers" class="sprites-before clear negative ui-corner-all"><?php echo _("Clear all"); ?></button></div>
</div>

<div id="map-regions">
<div id="regions-introduction" class="panel ui-corner-all">
<?php $tabIndex = (isset($_SESSION['simplemappr']) && $roles[$_SESSION['simplemappr']['role']] == 'administrator') ? 5 : 4; ?>
<p><?php echo _("Type countries as Mexico, Venezuela AND/OR bracket pipe- or space-separated State/Province codes prefixed by 3-letter ISO country code <em>e.g.</em>USA[VA], CAN[AB ON]."); ?> <a href="#" data-tab="<?php echo $tabIndex; ?>" class="sprites-before help show-codes"><?php echo _("codes"); ?></a></p>
</div>
<div id="fieldSetsRegions" class="fieldSets">
<?php $this->partial("regions"); ?>
</div>
<div class="addFieldset"><button class="sprites-before addmore positive ui-corner-all" data-type="regions"><?php echo _("Add a region"); ?></button></div>
<div class="submit"><button class="sprites-before submitForm positive ui-corner-all"><?php echo _("Preview"); ?></button><button id="clearRegions" class="sprites-before clear negative ui-corner-all"><?php echo _("Clear all"); ?></button></div>
</div>

<div id="map-preview">
<div id="mapWrapper">
<div id="actionsBar" class="ui-widget-header ui-corner-all ui-helper-clearfix">
<ul>
<li><a href="#" class="sprites tooltip toolsZoomIn" title="<?php echo _("zoom in +"); ?>"></a></li>
<li class="divider"><a href="#" class="sprites tooltip toolsZoomOut" title="<?php echo _("zoom out -"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsCrop" title="<?php echo _("crop ctrl+x"); ?>"></a></li>
<li class="divider"><a href="#" class="sprites tooltip toolsQuery" title="<?php echo _("fill regions"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsUndoDisabled" title="<?php echo _("undo ctrl+z"); ?>"></a></li>
<li class="divider"><a href="#" class="sprites tooltip toolsRedoDisabled" title="<?php echo _("redo ctrl+y"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsNew" title="<?php echo _("new ctrl+n"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsRefresh" title="<?php echo _("refresh ctrl+r"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsRebuild" title="<?php echo _("rebuild ctrl+b"); ?>"></a></li>
</ul>
<h3 id="mapTitle"></h3>
<ul id="map-saveDialog">
<?php if (isset($_SESSION['simplemappr'])): ?>
<li><a class="sprites-before tooltip map-saveItem toolsSave" href="#" title="<?php echo _("save ctrl+s"); ?>"><?php echo _("Save"); ?></a></li>
<li><a class="sprites-before tooltip map-saveItem toolsEmbed" href="#" title="<?php echo _("embed"); ?>" data-mid=""><?php echo _("Embed"); ?></a></li>
<?php endif; ?>
<li><a class="sprites-before tooltip map-saveItem toolsDownload" href="#" title="<?php echo _("download ctrl+d"); ?>"><?php echo _("Download"); ?></a></li>
</ul>
</div>
<div id="map">
<div id="mapImage">
<div id="mapControls">
<div class="viewport">
<ul class="overview"></ul>
</div>
<div class="dot"></div>
<div id="wheel-overlay">
<a href="#" class="sprites tooltip controls arrows up" data-pan="up" title="<?php echo _("pan up"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows right" data-pan="right" title="<?php echo _("pan right"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows down" data-pan="down" title="<?php echo _("pan down"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows left" data-pan="left" title="<?php echo _("pan left"); ?>"></a>
</div>
<div class="thumb ui-corner-all ui-widget-header"></div>
</div>
<div id="badRecordsWarning"><a href="#" class="sprites-before toolsBadRecords"><?php echo _("Records Out of Range"); ?></a></div>
<div id="mapOutput"></div>
</div>
<div id="mapScale"></div>
<div id="mapToolsCollapse" class="mapTools-default ui-widget-header ui-corner-left"><a href="#" class="sprites tooltip" title="<?php echo _("expand/collapse ctrl+e"); ?>"></a></div>
</div>
<div id="mapTools">
<ul>
<li><a href="#mapOptions"><?php echo _("Settings"); ?></a></li>
<li><a href="#mapLegend"><?php echo _("Legend"); ?></a></li>
</ul>
<div id="mapLegend"><p><em><?php echo _("legend will appear here"); ?></em></p></div>
<div id="mapOptions">

<h2><?php echo _("Layers"); ?></h2>
<?php $this->partial("layers"); ?>

<h2><?php echo _("Labels"); ?></h2>
<?php $this->partial("labels"); ?>

<h2><?php echo _("Options"); ?></h2>
<?php $this->partial("options"); ?>

<h3><?php echo _("Line Thickness"); ?></h3>
<div id="border-slider"></div>

<h2><?php echo _("Projection"); ?>*</h2>
<?php $this->partial("projections", Mappr::$accepted_projections); ?>
<p>*<?php echo _("zoom prior to setting projection"); ?></p>

</div>
</div>
</div>
</div>

<div id="map-mymaps">
<?php if (!isset($_SESSION['simplemappr'])): ?>
<div class="panel ui-corner-all">
<p><?php echo _("Save and reload your map data or create a generic template."); ?></p> 
</div>
<div id="janrainEngageEmbed"></div>
<?php else: ?>
<div id="usermaps"></div>
<?php endif; ?>
</div>

<?php if (isset($_SESSION['simplemappr'])): ?>
<div id="map-shares">
<div id="sharedmaps"></div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['simplemappr']) && $roles[$_SESSION['simplemappr']['role']] == 'administrator'): ?>
<div id="map-users">
<div id="userdata"></div>
</div>
<div id="map-admin">
  <?php $this->partial("admin_tools"); ?>
  <?php $this->partial("admin_api"); ?>
  <p id="admin-api-list"></p>
  <?php $this->partial("admin_citations"); ?>
  <div id="admin-citations-list"></div>
</div>
<?php endif; ?>

<div id="badFile" title="<?php echo _("Unsupported file type"); ?>"><?php echo _("Only files of type text are accepted."); ?></div>
<div id="badRecordsViewer" title="<?php echo _("Records out of range"); ?>"><div id="badRecords"></div></div>
<div id="mapSave" title="<?php echo _("Save"); ?>">
<p>
<label for="m-mapSaveTitle"><?php echo _("Title"); ?><span class="required">*</span></label>
<input type="text" id="m-mapSaveTitle" class="m-mapSaveTitle" size="30" maxlength="30" />
</p>
</div>
<div id="mapExport" title="<?php echo _("Download"); ?>">
<div class="download-dialog">
<p>
<label for="file-name"><?php echo _("File name"); ?></label>
<input type="text" id="file-name" maxlength="30" size="30" />
</p>
<fieldset>
<legend><?php echo _("File type"); ?></legend>
<?php $this->partial("filetypes"); ?>
</fieldset>
<fieldset>
<legend><?php echo _("Options"); ?></legend>
<p id="mapCropMessage" class="sprites-before"><?php echo _("map will be cropped"); ?></p>
<div class="download-options">
<?php $this->partial("scales"); ?>
<div id="scale-measure"><?php echo sprintf(_("Dimensions: %s"), '<span></span>'); ?></div>
</div>
<div class="options-row">
<input type="checkbox" id="border" />
<label for="border"><?php echo _("include border"); ?></label>
</div>
<div class="options-row">
<input type="checkbox" id="scalelinethickness" />
<label for="scalelinethickness"><?php echo _("make line thickness proportional to image scale"); ?></label>
</div>
<div class="options-row">
<input type="checkbox" id="legend" disabled="disabled" />
<label for="legend"><?php echo _("embed legend"); ?></label>
</div>
<div class="options-row">
<input type="checkbox" id="scalebar" disabled="disabled" />
<label for="scalebar"><?php echo _("embed scalebar"); ?></label>
</div>
</fieldset>
<p>*<?php echo _("does not include scalebar, legend, or relief layers"); ?></p>
</div>
<div class="download-message"><?php echo _("Building file for download..."); ?></div>
</div>
<?php $this->partial("hidden_inputs"); ?>
</form>

</div>
</div>
<div id="mapper-message" class="ui-state-error" title="<?php echo _("Warning"); ?>"></div>
<div id="button-titles" class="hidden-message">
  <span class="save"><?php echo _("Save"); ?></span>
  <span class="cancel"><?php echo _("Cancel"); ?></span>
  <span class="download"><?php echo _("Download"); ?></span>
  <span class="delete"><?php echo _("Delete"); ?></span>
</div>
<div id="mapper-loading-error-message" class="hidden-message"><?php echo _("There was a problem loading your map."); ?></div>
<div id="mapper-saving-error-message" class="hidden-message"><?php echo _("There was a problem saving your map."); ?></div>
<div id="mapper-saving-message" class="hidden-message"><?php echo _("Saving..."); ?></div>
<div id="mapper-missing-legend" class="hidden-message"><?php echo _("You are missing a legend for at least one of your Point Data or Regions layers."); ?></div>
<div id="mapper-message-delete" class="ui-state-highlight hidden-message" title="<?php echo _("Delete"); ?>"><?php echo _("Are you sure you want to delete"); ?> <span></span>?</div>
<div id="mapper-legend-message" class="hidden-message"><?php echo _("legend will appear here"); ?></div>
<div id="mapper-message-help" class="ui-state-highlight hidden-message" title="<?php echo _("Example Coordinates"); ?>"></div>
<div id="mapper-message-codes" class="ui-state-highlight hidden-message" title="<?php echo _("State/Province Codes"); ?>"></div>
<div id="mapEmbed" class="ui-state-highlight hidden-message" title="<?php echo _("Embed"); ?>">
  <div class="header"><h2><?php echo _('Image'); ?></h2></div>
  <p><input id="embed-img" type="text" size="65" value="" /></p>
  <p><strong><?php echo _("Additional parameters"); ?></strong>:<br><span class="indent"><?php echo _("width, height, legend"); ?> <br><em>e.g.</em> /map/<span class="mid"></span>?width=200&amp;height=150&amp;legend=true</span></p>
  <div class="header"><h2><?php echo _('KML'); ?></h2></div>
  <p><input id="embed-kml" type="text" size="65" value="" /></p>
  <div class="header"><h2><?php echo _('SVG'); ?></h2></div>
  <p><input id="embed-svg" type="text" size="65" value="" /></p>
  <div class="header"><h2><?php echo _('GeoJSON'); ?></h2></div>
  <p><input id="embed-json" type="text" size="65" value="" /></p>
  <p><strong><?php echo _("Additional parameters"); ?></strong>:<br><span class="indent"><?php echo _("callback"); ?> (<em>e.g.</em> /map/<span class="mid"></span>.json?callback=myCoolCallback)</span></p>
</div>
<div id="colorpicker"><div class="colorpicker colorpicker_background"><div class="colorpicker_color"><div class="colorpicker"><div class="colorpicker"></div></div></div><div class="colorpicker_hue"><div class="colorpicker"></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="sprites-before colorpicker_submit"><?php echo _("Apply"); ?></div></div></div>
<?php $header_class->getJSVars(); ?>
<?php $header_class->getJSFooter(); ?>
</body>
</html>