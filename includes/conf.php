<?php 
/* conf.php ( config file ) */
// MySQL connection info
define('MYSQL_USER', 'lilurl');
define('MYSQL_PASS', 'XSA9QKHmdfd378M2');
define('MYSQL_DB', 'lilurl');
define('MYSQL_HOST', 'localhost');

// MySQL table
define('URL_TABLE', 'lil_urls');

// use mod_rewrite
define('REWRITE', true);

// set to true if index.php is in document root i.e. http://example.com/index.php
define('DOCROOT', false);

// enable thumbnail generation
define('THUMBNAIL', true);

// enable logging hits to ./includes/hits.sqlite, requires GeoIP
define('LOG', false);

// enable GET/POST API
define('API', true);

// require a key to access the API
define('RESTRICTED', false);

// value of the API access key
$apikey = 'VdZPkSNbMaRZbTZ4';

// location of the access log
$accesslog = './includes/access.log';

// location of the error log
$errorlog = './includes/error.log';

// image to be shown on the main page
$logo = './includes/logo.png';

// allow URLs that begin with these strings
$allowed_protocols = array('http:', 'https:');

// SQLite database to log accesses
$hitlog = './includes/hits.sqlite';

// thumbnail generation method (phantomjs|cutycapt) 
$tmethod = 'phantomjs';

// thumbnail width in pixels
$twidth = 250;

// absolute path of thumbnail directory (requires trailing slash)
$imgdir = '/var/www/htdocs/url/img/';

// list of top sites to use for autocomplete (top1000|top10000|top25000|top50000)
$topsites = 'top50000.txt';

// user agent when validating URLs with curl
$uagent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';

?>