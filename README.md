tinyURL 0.1
=======
by [@ageis](https://twitter.com/ageis) ( kevin [at] ageispolis [dot] net )

URL shortener employing a psuedo-hash involving base62 encoding of a unique integer & prime numbers near the golden ratio. 
Also features two modes of thumbnail generation plus jQuery autocomplete based on [Alexa top sites](http://s3.amazonaws.com/alexa-static/top-1m.csv.zip).

Core requirements:
	Apache2, PHP, MySQL
	mod_rewrite
	ImageMagick
	[CutyCapt](http://cutycapt.sourceforge.net/) and Xvfb (X virtual framebuffer)
	or [PhantomJS](http://phantomjs.org/)
	[GeoIP](http://dev.maxmind.com/geoip/legacy/install/city/)

Installation instructions:
* Create a MySQL database + user and grant permissions
* Import the ./includes/lilurl.sql file:
		`mysql -u <user> -p <database> < lilurl.sql`
* Clone this repository from the DocumentRoot, so the files end up in /url
* Edit ./includes/conf.php to suit your needs, enter the MySQL credentials from earlier
# Edit .htaccess so that %{HTTP_HOST} matches your domain.
* If you'd rather set this up on the DocumentRoot, you must set DOCROOT to TRUE, adjust $imgdir and modify .htaccess
* Make sure mod_rewrite is enabled: `a2enmod rewrite`
* You can set a logo for the main page at ./includes/logo.png
* Enabling LOG requires the PHP PEAR/PDO database abstraction layer and GeoIP database in /usr/share/GeoIP/GeoIPCity.dat (see link above)
* Enabling THUMBNAIL requires /usr/local/bin/phantomjs by default
* Thumbnail width can be changed in the configuration file.
* If you intend to use all the features, these packages should be installed:
		`imagemagick, cutycapt, php5-geoip, php5-mysql, php-db, php5-sqlite, xvfb, phantomjs`
		
You can retrieve thumbnails only like so:
		`http://example.com/url/index.php?thumb=http://www.google.com`
		
The API will return a shortened URL in plaintext:
		`http://example.com/url/index.php?longurl=http://www.google.com&api=1`
		
If RESTRICTED = true, then you must supply the key with the request:
		`http://example.com/url/index.php?longurl=http://www.google.com&api=1&key=VdZPkSNbMaRZbTZ4`
		
If there is an error or an incorrect key the output will be blank.

The autocomplete suggests https:// because I support the mission to Encrypt the Web / have HTTPS everywhere.

Lists of top sites can be updated with the following logic, ex. 1000:
		`curl -s -O http://s3.amazonaws.com/alexa-static/top-1m.csv.zip ; unzip -q -o top-1m.csv.zip top-1m.csv ; head -1000 top-1m.csv | cut -d, -f2 | cut -d/ -f1 > top1000.txt`
		
Here's my Bitcoin address for donations: `1AgeisUFv9NJ3AGGq7VPJWymGfFpCHDhCw`