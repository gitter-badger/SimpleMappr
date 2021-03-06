/**
 * SimpleMappr - create point maps for publications and presentations
 * jQuery SimpleMappr Admin
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link      http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
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
 */
/*global SimpleMappr, jQuery, window, document, self, XMLHttpRequest, alert, encodeURIComponent, _gaq */
var SimpleMapprAdmin = (function($, window, sm) {

  "use strict";

  var _private = {

    citations_list: $('#admin-citations-list'),
    api_list: $('#admin-api-list'),

    init: function() {
      this.loadUserList();
      this.bindTools();
      this.loadCitationList();
      this.bindCreateCitation();
      this.loadAPILogs();
      sm.tabSelector(5);
    },

    getParameterByName: function(name) {
      var cname   = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]"),
          regexS  = "[\\?&]" + cname + "=([^&#]*)",
          regex   = new RegExp(regexS),
          results = regex.exec(window.location.href);

      if(results === null) { return ""; }
      return decodeURIComponent(results[1].replace(/\+/g, " "));
    },

    loadUserList: function(object) {
      var self  = this,
          obj   = object || {},
          data  = {};

      sm.showSpinner();

      data = { locale : this.getParameterByName("locale") };

      if(obj.sort) {
        data.sort = obj.sort.item;
        data.dir = obj.sort.dir;
      }

      if(!data.locale) { delete data.locale; }

      $.ajax({
        type     : 'GET',
        url      : sm.settings.baseUrl + '/user/',
        data     : data,
        dataType : 'html',
        success  : function(response) {
          if(response.indexOf("access denied") !== -1) {
            window.location.reload();
          } else {
            $('#userdata').off().html(response)
              .on('click', 'a.toolsRefresh', function(e) {
                e.preventDefault();
                self.loadUserList();
              })
              .on('click', 'a.ui-icon-triangle-sort', function(e) {
                e.preventDefault();
                data.sort = { item : $(this).attr("data-sort"), dir : "asc" };
                if($(this).hasClass("asc")) { data.sort.dir = "desc"; }
                self.loadUserList(data);
              })
              .on('click', 'a.user-delete', function(e) {
                e.preventDefault();
                self.deleteUserConfirmation(this);
              })
              .on('click', 'a.user-load', function(e) {
                e.preventDefault();
                sm.loadMapList({ uid : $(this).attr("data-uid") });
                sm.tabSelector(3);
            });
            sm.hideSpinner();
          }
        }
      });
    },

    bindTools: function() {
      var self = this;

      $('#map-admin').on('click', 'a.admin-tool', function(e) {
        e.preventDefault();
        sm.showSpinner();
        if($(this).has('#flush-caches')) {
          self.flushCaches();
        }
      });

      $('#citation-doi').on('blur', function() {
        var val = $(this).val();
        if(val.length > 0 && $('#citation-reference').val().length === 0) {
          sm.showSpinner();
          $.getJSON("http://search.crossref.org/dois?q=" + encodeURIComponent(val), function(data) {
            if (data && data.length > 0) {
              $.each(data[0], function(key, value) {
                if(key === "fullCitation") {
                  $('#citation-reference').val(value);
                }
                if(key === "year") {
                  $('#citation-year').val(value);
                }
              });
            }
          });
          sm.hideSpinner();
        }
      });
    },

    flushCaches: function() {
      $.ajax({
        type     : 'GET',
        url      : sm.settings.baseUrl + "/flush_cache/",
        dataType : 'json',
        success  : function(response) {
          if(response.files === true) {
            sm.hideSpinner();
            window.location.reload();
          }
        },
        error    : function() {
          sm.hideSpinner();
          alert("Error flushing caches");
        }
      });
    },

    loadCitationList: function() {
      var self = this, citations = "", doi = "", link = "";

      sm.showSpinner();
      $.ajax({
        type     : 'GET',
        url      : sm.settings.baseUrl + "/citation.json",
        dataType : 'json',
        timeout  : 30000,
        success  : function(data) {
          if(data.status === 'ok') {
            citations += "<h2>Total: " + data.citations.length + "</h2>";
            $.each(data.citations, function() {
              doi = (this.doi) ? ' doi:<a href="http://doi.org/' + this.doi + '">' + this.doi + '</a>.' : "";
              link = (this.link) ? ' (<a href="' + this.link + '">link</a>)' : "";
              citations += '<p class="citation">' + this.reference + link + doi + '<a class="sprites-before citation-delete" data-id="' + this.id + '" href="#">Delete</a></p>';
            });
            self.citations_list.html(citations);
            self.bindDeleteCitations();
            sm.hideSpinner();
          }
        },
        error : function() {
          alert("Error loading citations");
          sm.hideSpinner();
        }
      });
    },

    loadAPILogs: function() {
      var self = this;

      sm.showSpinner();
      $.ajax({
        type     : 'GET',
        url      : sm.settings.baseUrl + "/apilog/",
        dataType : 'html',
        timeout  : 30000,
        success  : function(data) {
          self.api_list.html(data);
          sm.hideSpinner();
        },
        error : function() {
          alert("Error loading API log");
          sm.hideSpinner();
        }
      });
    },

    bindDeleteCitations: function() {
      var self = this;

      this.citations_list.on('click', 'a.citation-delete', function(e) {
        e.preventDefault();
        self.deleteCitationConfirmation(this);
      });
    },

    bindCreateCitation: function() {
      var self = this;

      $('#map-admin').on('click', 'button.addmore', function(e) {
        e.preventDefault();
        if($('#citation-reference').val() !== "" && $('#citation-surname').val() !== "" && $('#citation-year').val() !== "") {
          sm.showSpinner();
          $.ajax({
            type        : 'POST',
            url         : sm.settings.baseUrl + '/citation',
            data        : $("form").serialize(),
            dataType    : 'json',
            success     : function(data) {
              if(data.status === "ok") {
                $('#map-admin').find(".citation").val("");
                $.each(["reference", "surname", "year"], function() {
                  $('#citation-'+this).removeClass('ui-state-error');
                });
                self.loadCitationList();
                sm.hideSpinner();
              }
            }
          });
        } else {
          $.each(["reference", "surname", "year"], function() {
            $('#citation-'+this).addClass('ui-state-error');
          });
        }
      });
    },

    deleteUserConfirmation: function(obj) {
      var self    = this,
          id      = $(obj).attr("data-id"),
          message = '<em>' + $(obj).parent().parent().children("td:first").text() + '</em>';

      $('#mapper-message-delete').find("span").html(message).end().dialog({
        height        : '250',
        width         : '500',
        dialogClass   : 'ui-dialog-title-mapper-message-delete',
        modal         : true,
        closeOnEscape : false,
        draggable     : true,
        resizable     : false,
        buttons       : [
          {
            "text"  : $('#button-titles').find('span.delete').text(),
            "class" : "negative",
            "click" : function() {
              sm.showSpinner();
              $.ajax({
                type    : 'DELETE',
                url     : sm.settings.baseUrl + "/user/" + id,
                success : function() {
                  self.loadUserList();
                  sm.hideSpinner();
                  sm.trackEvent('user', 'delete');
                }
              });
              $(this).dialog("destroy");
            }
          },
          {
            "text"  : $('#button-titles').find('span.cancel').text(),
            "class" : "ui-button-cancel",
            "click" : function() {
              $(this).dialog("destroy");
            }
          }]
      }).show();
    },

    deleteCitationConfirmation: function(obj) {
      var self    = this,
          id      = $(obj).attr("data-id"),
          message = '<p class="citation">' + $(obj).parent().text().replace("Delete", "") + '</p>';

      $('#mapper-message-delete').find("span").html(message).end().dialog({
        height        : '250',
        width         : '500',
        dialogClass   : 'ui-dialog-title-mapper-message-delete',
        modal         : true,
        closeOnEscape : false,
        draggable     : true,
        resizable     : false,
        buttons       : [
          {
            "text"  : $('#button-titles').find('span.delete').text(),
            "class" : "negative",
            "click" : function() {
              sm.showSpinner();
              $.ajax({
                type    : 'DELETE',
                url     : sm.settings.baseUrl + "/citation/" + id,
                success : function() {
                  sm.hideSpinner();
                  self.loadCitationList();
                }
              });
              $(this).dialog("destroy");
            }
          },
          {
            "text"  : $('#button-titles').find('span.cancel').text(),
            "class" : "ui-button-cancel",
            "click" : function() {
              $(this).dialog("destroy");
            }
          }]
      }).show();
    }

  };

  return {
    init: function() {
      _private.init();
    }
  };

}(jQuery, window, SimpleMappr));