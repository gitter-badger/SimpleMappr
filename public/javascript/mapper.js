/*global $, jQuery, window, document, self, XMLHttpRequest, setTimeout, Raphael, alert */

var Mappr = Mappr || { 'settings': {} };

$(function () {

   "use strict";

  Mappr.vars = {
    newPointCount      : 0,
    newRegionCount     : 0,
    newFreehandCount   : 0,
    maxTextareaCount   : 10,
    zoom               : true,
    fileDownloadTimer  : {},
    fillColor          : {}
  };

  $.ajaxSetup({
    xhr:function () { return new XMLHttpRequest(); }
  });

  $(window).resize(function () {
    var arrPageSizes = Mappr.getPageSize(),
        arrPageScroll = Mappr.getPageScroll();

    $('#mapper-overlay').css({
      width :  arrPageSizes[0],
      height:  arrPageSizes[1]
    });

    $('#mapper-message').css({
      top     : arrPageScroll[1] + (arrPageSizes[3] / 10),
      left    : arrPageScroll[0],
      position: 'fixed',
      zIndex  : 1001,
      margin  : '0px auto',
      width   : '100%'
    });
  });

  Mappr.getPageSize = function () {
    var xScroll, yScroll, windowWidth, windowHeight, pageHeight, pageWidth;

    if (window.innerHeight && window.scrollMaxY) {
      xScroll = window.innerWidth + window.scrollMaxX;
      yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
      xScroll = document.body.scrollWidth;
      yScroll = document.body.scrollHeight;
    } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
      xScroll = document.body.offsetWidth;
      yScroll = document.body.offsetHeight;
    }

    if (self.innerHeight) { // all except Explorer
      if(document.documentElement.clientWidth) {
        windowWidth = document.documentElement.clientWidth;
      } else {
        windowWidth = self.innerWidth;
      }
      windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
      windowWidth = document.documentElement.clientWidth;
      windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
      windowWidth = document.body.clientWidth;
      windowHeight = document.body.clientHeight;
    }
    // for small pages with total height less then height of the viewport
    if(yScroll < windowHeight) {
      pageHeight = windowHeight;
    } else {
      pageHeight = yScroll;
    }
    // for small pages with total width less then width of the viewport
    if(xScroll < windowWidth) {
      pageWidth = xScroll;
    } else {
      pageWidth = windowWidth;
    }

    return [pageWidth,pageHeight,windowWidth,windowHeight];

  }; /** end Mappr.getPageSize **/


  Mappr.getPageScroll = function () {
    var xScroll, yScroll;

    if (self.pageYOffset) {
      yScroll = self.pageYOffset;
      xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {// Explorer 6 Strict
      yScroll = document.documentElement.scrollTop;
      xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
      xScroll = document.body.scrollLeft;
    }

    return [xScroll,yScroll];

  }; /** end Mappr.getPageScroll **/

  Mappr.showCoords = function (c) {
    var x = parseInt(c.x, 10),
        y = parseInt(c.y, 10),
       x2 = parseInt(c.x2, 10),
       y2 = parseInt(c.y2, 10);

    $('.jcrop-holder div:first').css('backgroundColor', 'white');
    $('#bbox_rubberband').val(x+','+y+','+x2+','+y2);
  };

  Mappr.showCoordsQuery = function (c) {
    var x = parseInt(c.x, 10),
        y = parseInt(c.y, 10),
       x2 = parseInt(c.x2, 10),
       y2 = parseInt(c.y2, 10);

    $('#bbox_query').val(x+','+y+','+x2+','+y2);
  };

  Mappr.tabSelector = function (tab) {
    $("#tabs").tabs('select',tab);
  };

  Mappr.RGBtoHex = function (R,G,B) {
    return this.toHex(R)+this.toHex(G)+this.toHex(B);
  };

  Mappr.toHex = function (N) {
    if (N === null) { return "00"; }
    N = parseInt(N, 10);
    if (N === 0 || isNaN(N)) { return "00"; }
    N = Math.max(0,N);
    N = Math.min(N,255);
    N = Math.round(N);
    return "0123456789ABCDEF".charAt((N-N%16)/16) + "0123456789ABCDEF".charAt(N%16);
  };

  Mappr.bindToolbar = function () {
    var self = this;

    $("ul.dropdown li").hover(function () {
      $(this).addClass("ui-state-hover");
      $('ul:first',this).css('visibility', 'visible');
    }, function () {
      $(this).removeClass("ui-state-hover");
      $('ul:first',this).css('visibility', 'hidden');
    });

    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");

    $('.toolsZoomIn').click(function () {
      $('#mapCropMessage').hide();
      if($('#mapCropMessage').is(':hidden')) {
        self.initJzoom();
        self.vars.zoom = true;
      }
      return false;
    });

    $('.toolsZoomOut').click(function () {
      $('#mapCropMessage').hide();
      $('#zoom_out').val(1);
      self.showMap();
      $('#zoom_out').val('');
      return false;
    });

    $('.toolsRotate').click(function () {
      $('#rotation').val(parseInt($('#rendered_rotation').val(), 10)+parseInt($(this).attr("data-rotate"), 10));
      self.showMap();
      return false;
    });

    $('.toolsCrop').click(function () {
      if($('#mapCropMessage').is(':hidden')) {
        self.initJcrop();
        self.vars.zoom = false;
        $('#mapCropMessage').show();
      }
      return false;
    });

    $('.toolsQuery').ColorPicker({
      onBeforeShow: function () {
        $(this).ColorPickerSetColor(Mappr.RGBtoHex(150, 150, 150));
      },
      onShow: function (colpkr) {
        $(colpkr).show();
        Mappr.destroyJcrop();
        return false;
      },
      onHide: function (colpkr) {
        $(colpkr).hide();
        return false;
      },
      onSubmit: function (hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).ColorPickerHide();
        $('#mapCropMessage').hide();
        Mappr.vars.fillColor = rgb;
        Mappr.initJquery();
        Mappr.vars.zoom = false;
      }
    }).click(function () {
      return false;
    });

    $('.toolsDraw').click(function () {
      $('mapCropMessage').hide();
      self.initDraw();
      self.vars.zoom = false;
      return false;
    });

    $('.toolsRefresh').click(function () {
      self.resetJbbox();
      self.showMap();
      return false;
    });

    $('.toolsRebuild').click(function () {
      $('#bbox_map').val('');
      $('#projection_map').val('');
      $('#bbox_rubberband').val('');
      $('#rotation').val('');
      $('#projection').val('');
      $('#pan').val('');
      self.showMap();
      return false;
    });

  }; /** end Mappr.bindToolbar **/

  Mappr.bindArrows = function () {
    var self = this;

    $('.arrows').click(function () {
      $('#pan').val($(this).attr("data-pan"));
      self.showMap();
      return false;
    });
  };

  Mappr.bindSettings = function () {
    var self = this;

    $('.layeropt').click(function () {
      self.showMap();
    });

    $('.gridopt').click(function () {
      if(!$('#graticules').is(':checked')) { $('#graticules').attr('checked', true); }
      self.showMap();
    });

    $('#projection').change(function () {
      if($(this).val() !== "") { self.showMap(); }
    });
  };

  Mappr.bindColorPickers = function () {
    $('.colorPicker').ColorPicker({
      element : $(this),
      onBeforeShow: function () {
        var color = $(this).val().split(" ");
        $(this).ColorPickerSetColor(Mappr.RGBtoHex(color[0], color[1], color[2]));
      },
      onHide: function (colpkr) {
        $(colpkr).hide();
        return false;
      },
      onSubmit: function (hsb, hex, rgb, el) {
        hsb = null;
        hex = null;
        $(el).val(rgb.r + ' ' + rgb.g + ' ' + rgb.b);
        $(el).ColorPickerHide();
      }
    }).bind('keyup', function () {
      var color = $(this).val().split(" ");
      $(this).ColorPickerSetColor(Mappr.RGBtoHex(color[0], color[1], color[2]));
    });
  };

  Mappr.bindClearButtons = function () {
    $('.clearLayers, .clearRegions, .clearFreehand').click(function () {
      var fieldsets = $(this).parent().prev().prev().children();

      $(fieldsets).find('.m-mapTitle').val('');
      $(fieldsets).find('textarea').val('');
      if($(fieldsets).find('.m-mapShape').length > 0) {
        $(fieldsets).find('.m-mapShape')[0].selectedIndex = 3;
      }
      if($(fieldsets).find('.m-mapSize').length > 0) {
        $(fieldsets).find('.m-mapSize')[0].selectedIndex = 3;
      }
      if($(this).hasClass("clearLayers")) {
        $(fieldsets).find('.colorPicker').val('0 0 0');
      } else {
        $(fieldsets).find('.colorPicker').val('150 150 150');
      }

      return false;
    });

    $('.clearself').click(function () {
      Mappr.clearSelf($(this));
      return false;
    });

  }; /** end Mappr.bindClearButtons **/

  Mappr.clearSelf = function(el) {
    var box = $(el).parent();

    $(box).find('.m-mapTitle').val('');
    $(box).find('textarea').val('');
    if($(box).find('.m-mapShape').length > 0) {
      $(box).find('.m-mapShape')[0].selectedIndex = 3;
    }
    if($(box).find('.m-mapSize').length > 0) {
      $(box).find('.m-mapSize')[0].selectedIndex = 3;
    }
    if($(box).parent().hasClass("fieldset-points")) {
      $(box).find('.colorPicker').val('0 0 0');
    } else {
      $(box).find('.colorPicker').val('150 150 150');
    }
  };

  Mappr.destroyJcrop = function () {
    var vars = this.vars;

    if(typeof vars.jzoomAPI !== "undefined") { vars.jzoomAPI.destroy(); }
    if(typeof vars.jcropAPI !== "undefined") { vars.jcropAPI.destroy(); }
    if(typeof vars.jqueryAPI !== "undefined") { vars.jqueryAPI.destroy(); }

    $('.jcrop-holder').css('background-color', 'none');
  };

  Mappr.resetJbbox = function () {
    $('#bbox_rubberband').val('');
    $('#bbox_query').val('');
  };

  Mappr.initJcrop = function () {
    var self = this, vars = this.vars, color = 'black';

    self.destroyJcrop();
    self.resetJbbox();

    if($('#mapOutput img').attr("src") === "public/images/basemap.png") {
      color = 'grey';
    }

    vars.jcropAPI = $.Jcrop('#mapOutput img', {
      bgColor   : color,
      bgOpacity :0.5,
      onChange  : self.showCoords,
      onSelect  : self.showCoords
    });

    $('.jcrop-tracker').unbind('mouseup', self.aZoom );
  };

  Mappr.initJzoom = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    vars.jzoomAPI = $.Jcrop('#mapOutput img', {
      addClass      : "customJzoom",
      sideHandles   : false,
      cornerHandles : false,
      dragEdges     : false,
      bgOpacity     : 1,
      bgColor       : "white",
      onChange      : self.showCoords,
      onSelect      : self.showCoords
    });

    $('.jcrop-tracker').bind('mouseup', self.aZoom );
  };

  Mappr.initJquery = function () {
    var self = this, vars = this.vars;

    self.destroyJcrop();
    self.resetJbbox();

    vars.jqueryAPI = $.Jcrop('#mapOutput img', {
      addClass      : "customJzoom",
      sideHandles   : false,
      cornerHandles : false,
      dragEdges     : false,
      bgOpacity     : 1,
      bgColor       :'white',
      onChange      : self.showCoordsQuery,
      onSelect      : self.showCoordsQuery
    });

    $('.jcrop-tracker').bind('mouseup', self.aQuery);
  };

  Mappr.initDraw = function () {
    var self = this, raphael = this.raphaelConfig;

    self.destroyJcrop();

    $('#mapOutput').mousedown(function (e) {
      var pos     = raphael.position(e),
          color   = $('input[name="freehand[0][color]"]').val();

      color = color.split(" ");
      raphael.path = [['M', pos.x, pos.y]];
      raphael.wkt = [[pos.x + " " + pos.y]];
      raphael.color = "#" + self.RGBtoHex(color[0], color[1], color[2]);
      raphael.size = raphael.selectedSize;
      raphael.line = raphael.draw(self.path, self.color, self.size);
      $('#mapOutput').bind('mousemove', raphael.mouseMove);
    });

    $('#mapOutput').mouseup(function () {
      var wkt = "";

      $('#mapOutput').unbind('mousemove', raphael.mouseMove);
      $('input[name="freehand[0][title]"]').val("Freehand Drawing");

      $.ajax({
        url     : self.settings.baseUrl + '/query/',
        type    : 'POST',
        data    : { freehand : raphael.wkt },
        async   : false,
        success : function (results) {
          if(!results) { return; }
          switch(raphael.selectedTool) {
            case 'pencil':
              wkt = "LINESTRING(" + results + ")";
            break;
            case 'rectangle':
              wkt = "POLYGON((" + results + "))";
            break;
            case 'circle':
            break;
            case 'line':
              wkt = "LINESTRING(" + results + ")";
            break;
          }
          $('textarea[name="freehand[0][data]"]').val(wkt);
        },
        error : function () { return false; }
      });

    });

  };  /** end Mappr.initDraw **/

  Mappr.aZoom = function () {
    Mappr.showMap();
  };

  Mappr.aQuery = function () {

    var i = 0,
        fillColor = Mappr.vars.fillColor.r + " " + Mappr.vars.fillColor.g + " " + Mappr.vars.fillColor.b,
        formData  = {
          bbox           : $('#rendered_bbox').val(),
          bbox_query     : $('#bbox_query').val(),
          projection     : $('#projection').val(),
          projection_map : $('#projection_map').val(),
          qlayer         : ($('#stateprovince').is(':checked')) ? 'stateprovinces_polygon' : 'base'
        };

    Mappr.destroyJcrop();

    $.post(Mappr.settings.baseUrl + "/query/", formData, function (data) {

      if(data.length > 0) {
        var regions  = "",
            num_fieldsets = $('.fieldset-regions').length;

        for(i = 0; i < data.length; i += 1) {
            regions += data[i];
            if(i < data.length-1) { regions += ", "; }
        }

        for(i = 0; i < num_fieldsets; i += 1) {
          if($('input[name="regions['+i+'][title]"]').val() === "" || $('textarea[name="regions['+i+'][data]"]').val() === "") {
            $('input[name="regions['+i+'][title]"]').val("Selected Region " + (i+1).toString());
            $('input[name="regions['+i+'][color]"]').val(fillColor);
            $('textarea[name="regions['+i+'][data]"]').val(regions);
            if(i === (num_fieldsets-1) && !$('button[data-type="regions"]').is(':disabled')) {
              Mappr.addAccordionPanel('regions');
            }
            break;
          } else {
            if(i === (num_fieldsets-1)) { Mappr.addAccordionPanel('regions'); num_fieldsets += 1; }
            continue;
          }
        }

        Mappr.showMap();
      }
    });

  }; /** end Mappr.aQuery **/

  Mappr.textareaCounter = function (type, action) {
    var self = this;

    switch(action) {
      case 'get':
        switch(type) {
          case 'coords':
            return self.vars.newPointCount;
          case 'regions':
            return self.vars.newRegionCount;
          case 'freehand':
            return self.vars.newFreehandCount;
        }
        break;

      case 'increase':
        switch(type) {
          case 'coords':
            return (self.vars.newPointCount += 1);
          case 'regions':
            return (self.vars.newRegionCount += 1);
          case 'freehands':
            return (self.vars.newFreehandCount += 1);
        }
        break;

      case 'decrease':
        switch(type) {
          case 'coords':
            return (self.vars.newPointCount -= 1);
          case 'regions':
            return (self.vars.newRegionCount -= 1);
          case 'freehands':
            return (self.vars.newFreehandCount -= 1);
        }
        break;
    }

  }; /** end Mappr.textareaCounter **/

  Mappr.addAccordionPanel = function (data_type) {
    var self    = this,
        counter = self.textareaCounter(data_type, 'get'),
        button  = $(".addmore[data-type='" + data_type + "']"),
        clone   = {},
        color   = (data_type === 'coords') ? "0 0 0" : "150 150 150",
        num     = 0;

    if(button.attr("data-type") === data_type) {
      clone = button.parent().prev().children("div:last").clone();

      num = parseInt($(clone).find("h3 a").text().split(" ")[1],10);

      if(counter < self.vars.maxTextareaCount) {
        counter = self.textareaCounter(data_type, 'increase');

        $(clone).find("h3 a").text($(clone).find("h3 a").text().split(" ")[0] + " " + (num+1).toString());
        $(clone).find("input.m-mapTitle").attr("name", data_type + "["+num.toString()+"][title]").val("");
        $(clone).find("textarea")
                .attr("name", data_type + "["+num.toString()+"][data]")
                .removeClass("textarea-processed")
                .val("")
                .each(function () {
                  self.addGrippies(this);
                });
        $(clone).find("select.m-mapShape").attr("name", data_type + "["+num.toString()+"][shape]").val("circle");
        $(clone).find("select.m-mapSize").attr("name", data_type + "["+num.toString()+"][size]").val("10");
        $(clone).find("input.colorPicker").attr("name", data_type + "["+num.toString()+"][color]").val(color).ColorPicker({
          onBeforeShow: function () {
            var color = $(this).val().split(" ");
            $(this).ColorPickerSetColor(Mappr.RGBtoHex(color[0], color[1], color[2]));
          },
          onHide: function (colpkr) {
            $(colpkr).hide();
            return false;
          },
          onSubmit: function (hsb, hex, rgb, el) {
            hsb = null;
            hex = null;
            $(el).val(rgb.r + " " + rgb.g + " " + rgb.b);
            $(el).ColorPickerHide();
          }
        }).bind('keyup', function () {
          var color = $(this).val().split(" ");
          $(this).ColorPickerSetColor(Mappr.RGBtoHex(color[0], color[1], color[2]));
        });

        $(button).parent().prev().accordion("activate", false).append(clone).children("div:last").accordion({
          header      : 'h3',
          collapsible : true,
          autoHeight  : false,
          active      : true
        }).find("button.removemore").show().click(function () {
          $(clone).remove();
          counter = self.textareaCounter(data_type, 'decrease');
          $(button).removeAttr("disabled");
          return false;
        }).parent().find("button.clearself").click(function () {
          Mappr.clearSelf($(this));
          return false;
        });

      }

      if(counter >= self.vars.maxTextareaCount-3) {
        $(button).attr("disabled","disabled");
      }
    }

  }; /** end Mappr.addAccordionPanel **/

  Mappr.addGrippies = function (obj) {
    var textarea     = $(obj).addClass("textarea-processed"),
        staticOffset = null,
        grippie      = $("div.grippie", $(obj).parent())[0];

    function performDrag(e) {
      textarea.height(Math.max(32, staticOffset + e.pageY) + "px");
      return false;
    }

    function endDrag() {
      $(document).unbind("mousemove", performDrag).unbind("mouseup", endDrag);
      textarea.css("opacity", 1);
    }

    function startDrag(e) {
      staticOffset = textarea.height() - e.pageY;
      textarea.css("opacity", 0.25);
      $(document).bind('mousemove', performDrag).bind('mouseup', endDrag);
      return false;
    }

    $(obj).parent().find(".grippie").bind('mousedown', startDrag);
    grippie.style.marginRight = (parseInt(grippie.offsetWidth,10)-parseInt($(this)[0].offsetWidth,10)).toString() + "px";
  };

  Mappr.bindAddButtons = function () {
    var self = this;

    $('.addmore').click(function () {
      var data_type = $(this).attr("data-type");

      self.addAccordionPanel(data_type);
      return false;
    });

  }; /** end Mappr.bindAddButtons **/

  Mappr.loadMapList = function () {
    var self    = this,
        message = '<div id="usermaps-loading"><span id="mapper-building-map">Loading your maps...</span></div>';

    $('#usermaps').html(message);

    $.get(self.settings.baseUrl + "/usermaps/?action=list", {}, function (data) {
      $('#usermaps').html(data);

      $('.map-load').click(function () {
        self.loadMap(this);
        return false;
      });

      $('.map-delete').click(function () {
        self.deleteConfirmation(this);
        return false;
      });

    }, "html");
  };

  Mappr.removeExtraElements = function () {
    var self         = this,
        i            = 0,
        numPoints    = $('.fieldset-points').size(),
        numRegions   = $('.fieldset-regions').size(),
        numFreehands = $('.fieldset-freehands').size();

    if(numPoints > 3) {
      for(i = numPoints-1; i >= 3; i -= 1) {
        $('#fieldSetsPoints div.fieldset-points:eq('+i.toString()+')').remove();
      }
      self.vars.newPointCount = 0;
    }

    if(numRegions > 3) {
      for(i = numRegions-1; i >= 3; i -= 1) {
        $('#fieldSetsRegions div.fieldset-regions:eq('+i.toString()+')').remove();
      }
      self.vars.newRegionCount = 0;
    }

    if(numFreehands > 3) {
      for(i = numFreehands-1; i >= 3; i -= 1) {
        $('#fieldSetsFreehands div.fieldset-freehands:eq('+i.toString()+')').remove();
      }
      self.vars.newFreehandCount = 0;
    }
  };

  Mappr.loadMap = function (obj) {
    var self   = this,
        id     = $(obj).attr("data-mid"),
        filter = $('#filter-mymaps').val();

    $.get(self.settings.baseUrl + "/usermaps/?action=load&map=" + id, {}, function (data) {

      self.removeExtraElements();
      $('#form-mapper').clearForm();

      $('#filter-mymaps').val(filter);

      self.loadSettings(data);
      self.activateEmbed(id);
      self.loadCoordinates(data);
      self.loadRegions(data);
      self.loadFreehands(data);
      self.loadLayers(data);
      self.showMap();

      $("#tabs").tabs('select',0);

    }, "json");

  };

  Mappr.loadSettings = function (data) {
    var pattern   = /[?*:;{}\\ "']+/g,
        map_title = "",
        i         = 0,
        keyMap    = [],
        key       = "";

    map_title = data.map.save.title;

    $('input[name="save[title]"]').val(map_title);
    $('.m-mapSaveTitle').val(map_title);

    $('#mapTitle').text(map_title);

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);

    $("#projection").val(data.map.projection);
    $('input[name="bbox_map"]').val(data.map.bbox_map);
    $('input[name="projection_map"]').val(data.map.projection_map);
    $('input[name="rotation"]').val(data.map.rotation);

    if(data.map.download_factor !== undefined && data.map.download_factor) {
      $('input[name="download_factor"]').val(data.map.download_factor);
      $('#download-factor-' + data.map.download_factor).attr('checked', true);
    } else {
      $('#download-factor-3').attr('checked', true);
    }

    if(data.map.download_filetype !== undefined && data.map.download_filetype) {
      $('input[name="download_filetype"]').val(data.map.download_filetype);
      $('#download-' + data.map.download_filetype).attr('checked', true);
    } else {
      $('#download-svg').attr('checked', true);
    }

    if(data.map.grid_space !== undefined && data.map.grid_space) {
      $('input[name="gridspace"]').attr('checked', false);
      $('#gridspace-' + data.map.grid_space).attr('checked', true);
    } else {
      $('#gridspace').attr('checked', true);
    }

    if(data.map.options !== undefined) {
      for(key in data.map.options) {
        if(data.map.options.hasOwnProperty(key)) { keyMap[keyMap.length] = key; }
      }
      for(i = 0 ; i < keyMap.length; i += 1) {
        if(keyMap[i] === 'border') {
          $('#border').attr('checked', true);
          $('input[name="options[border]"]').val(1);
        } else if (keyMap[i] === 'legend') {
          $('#legend').attr('checked', true);
          $('input[name="options[legend]"]').val(1);
        } else {
          $('input[name="options['+keyMap[i]+']"]').attr('checked', true);
        }
      }
    }

  }; //** end Mappr.loadSettings **/

  Mappr.loadCoordinates = function (data) {
    var self        = this,
        i           = 0,
        coords      = data.map.coords || [],
        coord_title = "",
        coord_data  = "",
        coord_color = "";

    for(i = 0; i < coords.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('coords');
      }

      coord_title = coords[i].title || "";
      coord_data  = coords[i].data  || "";
      coord_color = coords[i].color || "0 0 0";

      $('input[name="coords['+i.toString()+'][title]"]').val(coord_title);
      $('textarea[name="coords['+i.toString()+'][data]"]').val(coord_data);

      if(coords[i].shape === "") {
        $('select[name="coords['+i.toString()+'][shape]"]')[0].selectedIndex = 3;
      } else {
        $('select[name="coords['+i.toString()+'][shape]"]').val(coords[i].shape);
      }

      if(coords[i].size.toString() === "") {
        $('select[name="coords['+i.toString()+'][size]"]')[0].selectedIndex = 3;
      } else {
        $('select[name="coords['+i.toString()+'][size]"]').val(coords[i].size);
      }

      $('input[name="coords['+i.toString()+'][color]"]').val(coord_color);
    }
  };

  Mappr.loadRegions = function (data) {
    var self         = this,
        i            = 0,
        regions      = data.map.regions || [],
        region_title = "",
        region_data  = "",
        region_color = "";

    for(i = 0; i < regions.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('regions');
      }

      region_title = regions[i].title || "";
      region_data  = regions[i].data  || "";
      region_color = regions[i].color || "150 150 150";

      $('input[name="regions['+i.toString()+'][title]"]').val(region_title);
      $('textarea[name="regions['+i.toString()+'][data]"]').val(region_data);
      $('input[name="regions['+i.toString()+'][color]"]').val(region_color);
    }
  };

  Mappr.loadFreehands = function (data) {
    var self           = this,
        i              = 0,
        freehands      = data.map.freehand || [],
        freehand_title = "",
        freehand_data  = "",
        freehand_color = "";

    for(i = 0; i < freehands.length; i += 1) {
      if(i > 2) {
        self.addAccordionPanel('freehands');
      }

      freehand_title = freehands[i].title || "";
      freehand_data  = freehands[i].data  || "";
      freehand_color = freehands[i].color || "150 150 150";

      $('input[name="freehand['+i.toString()+'][title]"]').val(freehand_title);
      $('textarea[name="freehand['+i.toString()+'][data]"]').val(freehand_data);
      $('input[name="freehand['+i.toString()+'][color]"]').val(freehand_color);
    }
  };

  Mappr.loadLayers = function (data) {
    var i = 0, keyMap = [], key = 0;

    $('input[name="options[border]"]').val("");
    $('input[name="options[legend]"]').val("");
    if(data.map.layers) {
      for(key in data.map.layers) {
        if(data.map.layers.hasOwnProperty(key)) { keyMap[keyMap.length] = key; }
      }
      for(i = 0; i < keyMap.length; i += 1) {
        $('input[name="layers['+keyMap[i]+']"]').attr('checked', true);
      }
    }
  };

  Mappr.activateEmbed = function (mid) {
    var self    = this,
        message = '';

    $('.map-embed').attr("data-mid", mid).click(function () {
      message = 'Use the following HTML snippet to embed a png:';
      message += "<p><input type='text' size='75' value='&lt;img src=\"" + self.settings.baseUrl + "/?map=" + mid + "\" alt=\"\" /&gt;'></input></p>";
      message += "<strong>Additional parameters</strong>:<span class=\"indent\">width, height (<em>e.g.</em> ?map=" + mid + "&amp;width=200&amp;height=150)</span>";

      if($('body').find('#mapper-message').length > 0) {
        $('#mapper-message').html(message).dialog("open");
      } else {
        $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Embed Map">' + message + '</div>');

        $('#mapper-message').dialog({
          height        : (250).toString(),
          width         : (525).toString(),
          autoOpen      : true,
          modal         : true,
          closeOnEscape : false,
          draggable     : false,
          resizable     : false,
          buttons       : {
            Cancel: function () {
              $(this).dialog("destroy").remove();
            }
          }
        });
      }

      return false;
    }).show();

  };

  Mappr.deleteConfirmation = function (obj) {
    var self    = this,
        id      = $(obj).attr("data-mid"),
        message = 'Are you sure you want to delete<p><em>' + $(obj).parent().parent().find(".title").html() + '</em>?</p>';

    $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Delete Map">' + message + '</div>');

    $('#mapper-message').dialog({
      height        : (250).toString(),
      width         : (500).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : {
        "Delete" : function () {
          $.get(self.settings.baseUrl + "/usermaps/?action=delete&map="+id, {}, function () {
            self.loadMapList();
          }, "json");
          $(this).dialog("destroy").remove();
        },
        Cancel: function () {
          $(this).dialog("destroy").remove();
        }
      }
    });

  };

  Mappr.loadUsers = function () {
    var message = '<div id="users-loading"><span id="mapper-building-users">Loading users list...</span></div>';

    $('#userdata').html(message);
    $.get(Mappr.settings.baseUrl + "/usermaps/?action=users", {}, function (data) {
      $('#userdata').html(data);
    }, "html");
  };

  Mappr.bindSave = function () {
    var self = this;

    $(".map-save").click(function () {
      var missingTitle = false;

      $('#mapSave').dialog({
        autoOpen      : true,
        height        : (200).toString(),
        width         : (500).toString(),
        modal         : true,
        closeOnEscape : false,
        draggable     : false,
        resizable     : false,
        buttons       : {
          "Save" : function () {

            if($.trim($('.m-mapSaveTitle').val()) === '') { missingTitle = true; }

            if(missingTitle) {
              $('.m-mapSaveTitle').css({'background-color':'#FFB6C1'}).keyup(function () {
                $(this).css({'background-color':'transparent'});
              });
            } else {
              $('input[name="save[title]"]').val($('.m-mapSaveTitle').val());
              $('input[name="download_factor"]').val($('input[name="download-factor"]:checked').val());
              $('input[name="download_filetype"]').val($('input[name="download-filetype"]:checked').val());
              $('input[name="grid_space"]').val($('input[name="gridspace"]:checked').val());
              if($('#border').is(':checked')) {
                $('input[name="options[border]"]').val(1);
              } else {
                $('input[name="options[border]"]').val("");
              }
              if($('#legend').is(':checked')) {
                $('input[name="options[legend]"]').val(1);
              } else {
                $('input[name="options[legend]"]').val("");
              }

              $.post(self.settings.baseUrl + "/usermaps/?action=save", $("form").serialize(), function (data) {
                $('#mapTitle').text($('.m-mapSaveTitle').val());
                self.activateEmbed(data.mid);
                self.loadMapList();
              }, 'json');
              $(this).dialog("destroy");
            }
          },
          Cancel: function () {
            $(this).dialog("destroy");
          }
        }
      });

      return false;
    });

  }; /** end Mappr.bindSave **/

  Mappr.bindDownload = function () {
    var self = this;

    $(".map-download").click(function () {
      $('#mapExport').dialog({
        autoOpen      : true,
        width         : (500).toString(),
        modal         : true,
        closeOnEscape : false,
        draggable     : false,
        resizable     : false,
        buttons       : {
          Cancel : function () {
            $(this).dialog("destroy");
          },
          Download : function() {
            self.generateDownload();
          }
        }
      });

      return false;
    });

  };

  Mappr.bindSubmit = function () {
    var self = this, missingTitle = false;

    $(".submitForm").click(function () {

      $('.m-mapCoord').each(function () {
        if($(this).val() && $(this).parents().find('.m-mapTitle').val() === '') {
          missingTitle = true;
        }
      });

      if(missingTitle) {
        var message = 'You are missing a legend for at least one of your Point Data or Regions layers';
        self.showMessage(message);
      }
      else {
        self.showMap();
        $("#tabs").tabs('select',0);
      }

      return false;
    });
  };

  Mappr.showMessage = function (message) {

    if($('#mapper-message').length === 0) {
      $('body').append('<div id="mapper-message" class="ui-state-error" title="Warning"></div>');
    }
    $('#mapper-message').html(message).dialog({
      autoOpen      : true,
      height        : (200).toString(),
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons       : {
        Ok : function () {
          $(this).dialog("destroy").remove();
        }
      }
    });
  };

  Mappr.drawLegend = function () {
    var legend_url = $('#legend_url').val();

    if(legend_url) {
      $('#mapLegend').html("<img src=\"" + legend_url + "\" />");
    } else {
      $('#mapLegend').html('<p><em>legend will appear here</em></p>');
    }
  };

  Mappr.drawScalebar = function () {
    var scalebar_url = $('#scalebar_url').val();

    if(scalebar_url) {
      $('#mapScale').html('<img src="' + scalebar_url + '" />');
    } else {
      $('#mapScale').html('');
    }
  };

  Mappr.showBadPoints = function () {
    var bad_points = $('#bad_points').val();

    if(bad_points) {
      $('#badRecords').html(bad_points);
      $('#badRecordsWarning').show();
    }
  };

  Mappr.showMap = function () {
    var self         = this,
        token        = new Date().getTime(),
        formData     = {},
        message      = '<span id="mapper-building-map">Building preview...</span>',
        toolsTabs    = $('#mapTools').tabs(),
        tabIndex     = ($('#selectedtab').val()) ? parseInt($('#selectedtab').val(), 10) : 0;

    self.destroyJcrop();

    $('#output').val('pnga');        // set the preview and output values
    $('#badRecordsWarning').hide();  // hide the bad records warning
    $('#download_token').val(token); // set a token to be used for cookie

    formData = $("form").serialize();

    $('#mapOutput').html(message);
    $('#mapScale').html('');

    $.post(Mappr.settings.baseUrl + "/application/", formData, function (data) {
      $('#mapOutput').html(data);

      self.drawLegend();
      self.drawScalebar();
      self.showBadPoints();

      toolsTabs.tabs('select', tabIndex);

      $('#mapTools').bind('tabsselect', function (event,ui) {
        event = null;
        $('#selectedtab').val(ui.index);
      });

      self.resetJbbox();
      $('#bbox_map').val($('#rendered_bbox').val());             // set extent from previous rendering
      $('#projection_map').val($('#rendered_projection').val()); // set projection from the previous rendering
      $('#rotation').val($('#rendered_rotation').val());         // reset rotation value
      $('#pan').val('');                                         // reset pan value

      self.addBadRecordsViewer();

      $('.toolsBadRecords').click(function () {
        $('#badRecordsViewer').dialog("open");
        return false;
      });

    }, "html");

  }; /** end Mappr.showMap **/

  Mappr.addBadRecordsViewer = function () {
    $('#badRecordsViewer').dialog({
      autoOpen      : false,
      height        : (200).toString(),
      width         : (500).toString(),
      position      : [200, 200],
      modal         : true,
      closeOnEscape : false,
      draggable     : false,
      resizable     : false,
      buttons: {
        Ok: function () {
          $(this).dialog("close");
        }
      }
    });
  };

  Mappr.generateDownload = function () {
    var self        = this,
        pattern     = /[?*:;{}\\ "'\/@#!%\^()<>.]+/g,
        map_title   = $('#file-name').val(),
        token       = new Date().getTime().toString(),
        cookieValue = "",
        formData    = "",
        filetype    = "png";

    map_title = map_title.replace(pattern, "_");
    $('#file-name').val(map_title);
    $('input[name="file_name"]').val(map_title);

    $('input[name="download_factor"]').val($('input[name="download-factor"]:checked').val());

    filetype = $("input[name='download-filetype']:checked").val();

    if($('#border').is(':checked')) {
      $('input[name="options[border]"]').val(1);
    } else {
      $('input[name="options[border]"]').val("");
    }

    if($('#legend').is(':checked')) {
      $('input[name="options[legend]"]').val(1);
    } else {
      $('input[name="options[legend]"]').val("");
    }

    $('#download_token').val(token);

    switch(filetype) {
      case 'kml':
        formData = $("form").serialize();
        $.download(self.settings.baseUrl + "/application/kml/", formData, 'post');
      break;

      default:
        $('#download').val(1);
        $('#output').val(filetype);
        if(self.vars.jcropAPI) { $('#crop').val(1); }
        formData = $("form").serialize();
        $('.download-dialog').hide();
        $('.download-message').show();
        $.download(self.settings.baseUrl + "/application/", formData, 'post');
        $('#download').val('');
        $('#output').val('pnga');
    }

    self.vars.fileDownloadTimer = window.setInterval(function () {
      cookieValue = $.cookie('fileDownloadToken');
      if (cookieValue === token) {
        self.finishDownload();
      }
    }, 1000);

  }; /** end Mappr.generateDownload **/

  Mappr.finishDownload = function () {
    $('.download-message').hide();
    $('.download-dialog').show();
    window.clearInterval(this.vars.fileDownloadTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
  };

  Mappr.bindBulkDownload = function () {
    $("#download-all").click(function () {
      if($(this).is(':checked')) {
        $(".download-checkbox").each(function () {
          if($(this).is(':visible')) {
            $(this).attr('checked', true);
          } else {
            $(this).attr('checked', false);
          }
        });
      } else {
        $(".download-checkbox").attr('checked', false);
      }
    });
    $(".bulkdownload").click(function () {
      Mappr.bulkDownload();
      return false;
    });
  };

  Mappr.bulkDownload = function () {

    var selections = [], match = /^download\[(.*)\]$/, filetype = $("input[name='bulk-download-filetype']:checked").val();

    $('.download-checkbox').each(function() {
      if($(this).is(':checked')) {
        selections.push($(this).attr("name").match(match)[1]);
      }
    });

    //TODO: need Redis and worker process to produce zipped document containing all selected files

//    alert("Selections: " + selections + ", Filetype: " + filetype);

  };

  Mappr.showExamples = function() {
    var message = '<img src="/images/help_data.png" alt="Example Data Entry" />';

    if($('body').find('#mapper-message').length > 0) {
      $('#mapper-message').html(message).dialog("open");
    } else {
      $('body').append('<div id="mapper-message" class="ui-state-highlight" title="Example Coordinates">' + message + '</div>');

      $('#mapper-message').dialog({
        height        : (350).toString(),
        width         : (525).toString(),
        autoOpen      : true,
        modal         : true,
        closeOnEscape : false,
        draggable     : false,
        resizable     : false,
        buttons       : {
          OK: function () {
            $(this).dialog("destroy").remove();
          }
        }
      });
    }
    return false;
  };

  /************************************ 
  ** RAPHAEL: FREEHAND DRAWING TOOLS **
  ************************************/

// Commented out for now because injection of svg breaks flow of document in IE

  Mappr.raphaelConfig = {
/*
    board         : new Raphael('mapOutput', 800, 400),
    line          : null,
    path          : null,
    wkt           : null,
    color         : null,
    size          : null,
    selectedColor : 'mosaic',
    selectedSize  : 4,
    selectedTool  : 'pencil',
    offset        : $('#mapOutput').offset()
*/
  };

  Mappr.raphaelConfig.position = function (e) {
    return {
      x: (parseInt(e.pageX,10)-parseInt(this.offset.left,10)).toString(),
      y: (parseInt(e.pageY,10)-parseInt(this.offset.top,10)).toString()
    };
  };

  Mappr.raphaelConfig.mouseMove = function (e) {
    var self = Mappr.raphaelConfig,
        pos  = self.position(e),
        x    = self.path[0][1],
        y    = self.path[0][2],
        dx   = (pos.x - x),
        dy   = (pos.y - y);

    switch(self.selectedTool) {
      case 'pencil':
        self.path.push(['L', pos.x, pos.y]);
        self.wkt.push([pos.x + " " + pos.y]);
        break;
      case 'rectangle':
        self.path[1] = ['L', x + dx, y     ];
        self.path[2] = ['L', x + dx, y + dy];
        self.path[3] = ['L', x     , y + dy];
        self.path[4] = ['L', x     , y     ];
        self.path[5] = ['L', x,      y     ];
        break;
      case 'line':
        self.path[1] = ['L', pos.x, pos.y];
        self.wkt[1] = [pos.x + " " + pos.y];
        break;
      case 'circle':
        self.path[1] = ['A', (dx / 2), (dy / 2), 0, 1, 0, pos.x, pos.y];
        self.path[2] = ['A', (dx / 2), (dy / 2), 0, 0, 0, x, y];
        break;
    }
    self.line.attr({ path: self.path });

  }; /** end Mappr.raphaelConfig.mouseMove **/

  Mappr.raphaelConfig.forcePaint = function () {
    var self = Mappr.raphaelConfig;
    window.setTimeout(function () {
      var rect = self.board.rect(-99, -99, parseInt(self.board.width,10) + 99, parseInt(self.board.height,10) + 99).attr({stroke: "none"});
      setTimeout(function () { rect.remove(); });
    },1);
  };

  Mappr.raphaelConfig.draw = function (path, color, size) {
    var self   = Mappr.raphaelConfig,
        result = self.board.path(path);

    result.attr({ stroke: color, 'stroke-width': size, 'stroke-linecap': 'round' });
    self.forcePaint();
    return result;
  };

  Mappr.init = function () {
    $('#initial-message').hide();
    $("#tabs").tabs().show();
    $('#mapTools').tabs();
    $('.fieldSets').accordion({
      header : 'h3',
      collapsible : true,
      autoHeight : false
    });
    $(".tooltip").tipsy({gravity: 's'});
    this.bindToolbar();
    this.bindArrows();
    this.bindSettings();
    this.bindColorPickers();
    this.bindAddButtons();
    this.bindClearButtons();
    this.bindSave();
    this.bindDownload();
    this.bindBulkDownload();
    this.bindSubmit();
    $('textarea.resizable:not(.textarea-processed)').TextAreaResizer();
    if($('#usermaps').length > 0) {
      $("#tabs").tabs('select',3);
      this.loadMapList();
    }
    if($('#userdata').length > 0) {
      $("#tabs").tabs('select',4);
      this.loadUsers();
    }
    $("input").keypress(function(event) { if (event.which === 13) { return false; } });
  };

  Mappr.init();

});

/******* jQUERY EXTENSIONS *******/

(function ($) {

  "use strict";

  $.fn.clearForm = function () {
    return this.each(function () {
      var type = this.type, tag = this.tagName.toLowerCase();
      if (tag === 'form') {
        return $(':input',this).clearForm();
      }
      if (type === 'text' || type === 'password' || tag === 'textarea') {
        this.value = '';
      } else if (type === 'checkbox' || type === 'radio') {
       this.checked = false;
      } else if (tag === 'select') {
       this.selectedIndex = 0;
      }
    });
  };
})(jQuery);