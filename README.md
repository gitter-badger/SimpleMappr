SimpleMappr Installation and Configuration Instructions
=======================================================

    Developer: David P. Shorthouse
    Email: davidpshorthouse@gmail.com

### WARNING

Although MapServer 6.0.x is currently available, this application uses PHP mapscript 5.6.7

Configuration Instructions
--------------------------

1. Ensure /tmp, /public/javascript/cache, and /public/stylesheets/cache/ are readable & writable
2. Download map data from Natural Earth Data, [http://www.naturalearthdata.com/][1]:
  [http://bit.ly/NapVvW][2], [http://bit.ly/OHPoml][3], [http://bit.ly/N76XfB][4]
3. Extract each to /lib/mapserver/maps/. I created a greyscale shaded relief geotiff using ogr2ogr on the full color NaturalEarth HYP_HR_SR_W_DR.zip. If you wish to obtain a copy of this geotiff, please contact me directly at davidpshorthouse@gmail.com.
4. Use MapServer's included shptree utility to make *.qix index files (e.g. $ shptree 10m_admin_0_countries.shp) for better performance rendering shapefiles.
5. Make contents of /lib/mapserver/fonts readable & executable
6. Adjust /config/conf.db.php.sample and /config/conf.php.sample and remove .sample extensions. These set db (MySQL) connections and constants, respectively.
7. If you wish to use Janrain's OpenID authentication system, sign-up at [http://rpxnow.com][5] and replace the RPX_KEY in your /config/conf.db.php
8. The jQuery-based front-end assumes clean URLs and operates in a RESTful fashion. If served from Apache, use mod_rewrite as follows:

### Apache Configuration

    <VirtualHost *:80>
      ServerName simplemappr.net
      ServerAlias simplemappr.net
      ServerAdmin dshorthouse@mbl.edu
      DocumentRoot /path/to/your/root
      RewriteEngine on
      RewriteRule ^/map/(.+)\.(kml|json)/?$ /path/to/your/root/index.php?map=$1&format=$2&%{QUERY_STRING}
      RewriteRule ^/map/(.+)/?$ /path/to/your/root/index.php?map=$1&%{QUERY_STRING}
      RewriteRule ^/users/(.*)$ /path/to/your/root/users/index.php/$1
      RewriteRule ^/usermaps/(.*)$ /path/to/your/root/usermaps/index.php/$1
      RewriteRule ^/places/(.*)$ /path/to/your/root/places/index.php/$1
      <Directory "/path/to/your/root">
       Options -Indexes +FollowSymlinks
       AllowOverride None
       Order allow,deny
       Allow from all
       DirectoryIndex index.php index.html
      </Directory>
    </VirtualHost>

MacPorts
--------

    sudo port install php5-gd
    sudo port install xpm
    sudo port install proj
    sudo port install geos
    sudo port install gdal

1. Download MapServer 5 tarball, http://mapserver.org/download.html (e.g. [http://download.osgeo.org/mapserver/mapserver-5.6.7.tar.gz][6])
2. Extract and cd into folder
3. Execute from command line:

### Configuring install

    $ ./configure \
      --prefix=/usr \
      --with-agg \
      --with-proj=/opt/local \
      --with-geos=/opt/local/bin/geos-config \
      --with-gdal=/opt/local/bin/gdal-config \
      --with-threads \
      --with-ogr \
      --with-freetype=/opt/local \
      --with-xpm=/opt/local \
      --with-gd=/opt/local \
      --with-wfs \
      --with-wcs \
      --with-wmsclient \
      --with-wfsclient \
      --with-sos \
      --with-fribidi-config \
      --with-experimental-png \
      --with-php=/opt/local \
      --with-png=/opt/local

4. Execute $ make
5. Verify that mapserv is working $ ./mapserv -v
6. Find php_mapscript.so in mapscripts/php3 and move to PHP extensions directory (usually /opt/local/lib/php/extensions/no-debug-non-zts-20090626/)

Ubuntu Package
--------------

    sudo apt-get install php5-mapscript

Internationalization
--------------------

See: http://onlamp.com/pub/a/php/2002/06/13/php.html

Apache server requires php5_gettext.so extension for PHP5. The following two commands make a message.po file (by reading the index.php file) and a binary message.mo file. Both need to be moved to relevant i18n directory such as i18n/fr_FR.UTF-8/LC_MESSAGES. You'll need to translate the strings in message.po before making the binary of course.

    $ xgettext -n index.php
    $ msgfmt messages.po

Or, use the ruby utility, crawler.rb from the /i18n directory to make a message.po file and move it to i18n/fr_FR.UTF-8/LC_MESSAGES.

    $ cd i18n
    $ ruby crawler.rb ../

Copyright
---------

    Copyright (c) David P. Shorthouse
    License: GPLv2

[1]: http://www.naturalearthdata.com/
[2]: http://bit.ly/NapVvW
[3]: http://bit.ly/OHPoml
[4]: http://bit.ly/N76XfB
[5]: http://rpxnow.com
[6]: http://download.osgeo.org/mapserver/mapserver-5.6.7.tar.gz