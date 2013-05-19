<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root.'/lib/session.class.php');
$locale = Session::select_locale();
$tweet = ($locale['canonical'] == 'en') ? 'Tweet' : 'Tweeter';
?>
<style type="text/css">
#general-feedback .ui-helper-clearfix{margin-left:0;}
#social .button{float:left;margin-right:10px;}
</style>
<div id="map-feedback">
<div id="general-feedback" class="panel ui-corner-all">
<p class="ui-helper-clearfix">
<?php echo _("Used SimpleMappr in a manuscript, poster, PowerPoint presentation or are you making use of the API? Please also drop a note if you have feature requests or bug reports."); ?></p>
</div>
<div id="social">
<div class="button g-plusone" data-size="tall" data-annotation="none"></div>
<div class="button"><a href="https://twitter.com/share" class="twitter-share-button" data-text="@SimpleMappr" data-url="http://<?php echo $_SERVER['HTTP_HOST']; ?>" data-lang="<?php echo $locale['canonical']; ?>" data-count="none"><?php echo $tweet; ?></a></div>
<div class="button"><script type="IN/Share" data-url="http://<?php echo $_SERVER['HTTP_HOST']; ?>" data-counter="none"></script></div>
<div class="button fb-like" data-href="http://<?php echo $_SERVER['HTTP_HOST']; ?>" data-send="false" data-layout="button_count" data-width="120" data-show-faces="false" data-action="like"></div>
</div>
<!-- Disqus BEGIN -->
<div id="disqus_thread"></div>
<div id="fb-root"></div>
<script async>
  var disqus_shortname = 'simplemappr',
  disqus_config = function() { this.language = "<?php echo $locale['canonical']; ?>"; };
  window.___gcfg = {lang: '<?php echo $locale['canonical']; ?>'};
(function(d, s) {
    var js, fjs = d.getElementsByTagName(s)[0], load = function(url, id) {
      if (d.getElementById(id)) { return; }
      js = d.createElement(s); js.src = url; js.id = id;
      fjs.parentNode.insertBefore(js, fjs);
    };
    load('//connect.facebook.net/<?php echo $locale['locale']; ?>/all.js#xfbml=1&appId=283657208313184', 'fbjssdk');
    load('https://apis.google.com/js/plusone.js', 'gplus1js');
    load('//platform.twitter.com/widgets.js', 'tweetjs');
    load('//platform.linkedin.com/in.js', 'linkedinjs');
    load('//' + disqus_shortname + '.disqus.com/embed.js', 'disqusjs');
}(document, 'script'));
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=simplemappr">comments</a>.</noscript>
<!-- Disqus END -->
</div>