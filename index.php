<?php 
/* index.php ( URL generator ) */
require_once './includes/conf.php'; // settings
require_once './includes/url.php'; // classes
setlocale(LC_CTYPE, "en_US.UTF-8");
date_default_timezone_set('America/New_York');
error_reporting(0);

$lilurl = new lilURL();
$msg = '';
$site = $_SERVER['SERVER_NAME'];
$api = false;

if ( API ) {
if ( isset($_REQUEST['api']) && is_numeric($_REQUEST['api']) && ($_REQUEST['api'] == 1) ) {
	$api = true;
	if ( RESTRICTED ) {
	if ( $_REQUEST['key'] != $apikey ) {
		$api = false;
		die;
	}
	}
} 
else {
	$api = false;
} 
}

if ( isset($_REQUEST['thumb']) && !isset($_REQUEST['longurl'])) {
	$_REQUEST['longurl'] = $_REQUEST['thumb'];
}

if ( isset($_REQUEST['longurl']) ) {
	$longurl = trim(mysql_escape_string($_REQUEST['longurl']));
	$protocol_ok = false;
	$status_ok = false;		
	if (substr($longurl, 0, 4) == "www.") {
		$longurl = 'http://' . $longurl;
	}
	if ( count($allowed_protocols) ) {
		foreach ( $allowed_protocols as $ap ) {
			if ( strtolower(substr($longurl, 0, strlen($ap))) == strtolower($ap) ) {
				$protocol_ok = true;
				break;
			}
		}
	}
	else {
		$protocol_ok = true;
	}
	
	if ( checkStatus($longurl) ) { 
		$status_ok = true;
	} else {
		$status_ok = false;
	}
		
	if ( $protocol_ok && $status_ok && $lilurl->add_url($longurl) )	{
		$hash = $lilurl->get_hash($longurl);
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$datum = date("m-d-y H:i:s");
		$info = $datum . " " . $ip . " " . $longurl . " " . $browser . "\n";
		file_put_contents($accesslog, $info, FILE_APPEND);
		
		if ( REWRITE ) {
			if ( DOCROOT ) {
				$url = 'http://'.$site.'/'.$hash;
				$img = 'http://'.$site.dirname($_SERVER['PHP_SELF']).'img/'.$hash.'.jpg';

			} 
			else {
				$url = 'http://'.$site.dirname($_SERVER['PHP_SELF']).'/'.$hash;
				$img = 'http://'.$site.dirname($_SERVER['PHP_SELF']).'/img/'.$hash.'.jpg';
			}
		} 
		else {
			$url = 'http://'.$site.$_SERVER['PHP_SELF'].'?id='.$hash;
		}
		
		if ( API ) {
			if ($api == true) {
				print $url;
				die;
			}
		}
		
		if ( THUMBNAIL ) {
		if ($tmethod == "cutycapt") {		
		if (!file_exists($imgdir.$hash.'.jpg')) { 
			exec(escapeshellcmd('nice -15 xvfb-run --server-args="-screen 0, 800x600x16" cutycapt --url='.$longurl.' --out='.$imgdir.$hash.'.jpg --java=off --plugins=off --private-browsing=on --js-can-open-windows=off --max-wait=5000'));
			exec('nice -15 convert -thumbnail '. $twidth .' '.$imgdir . $hash . '.jpg ' . $imgdir . $hash . '.jpg');
		}
		} 
		elseif ($tmethod == "phantomjs") {		
		if (!file_exists($imgdir.$hash.'.jpg')) {
			exec(escapeshellcmd("nice -15 /usr/local/bin/phantomjs ./includes/rasterize.js '".$longurl."' ".$imgdir.$hash.".png"));
			exec('nice -15 convert -thumbnail '. $twidth .' '.$imgdir . $hash . '.png ' . $imgdir . $hash . '.jpg');
		}
		}

		if (file_exists($imgdir.$hash.'.jpg')) {			
			$msg = '<p class="success">your '.$site.' URL: <a href="'.$url.'" target="_blank" rel="nofollow">'.$url.'<br><br><img src="./img/'.$hash.'.jpg" class="icon"></a></p><div class="attr">you also have a thumbnail: <a href="./img/'.$hash.'.jpg" class="attr">'.$img.'</a></div><br>';
		} 
		else {
			$msg = '<p class="success">your '.$site.' URL: <a href="'.$url.'" target="_blank" rel="nofollow">'.$url.'</a><br><p class="error">thumbnail generation failed.</p><br>';	
		}
		} else {
			$msg = '<p class="success">your '.$site.' URL: <a href="'.$url.'" target="_blank" rel="nofollow">'.$url.'</a><br>';
		}

		if ( isset($_REQUEST['thumb']) ) {
			header('Location: '.$img, true, 301);
		}
	}
	elseif ( !$protocol_ok ) {
		$msg = '<p class="error">invalid URL or protocol! try adding http:// or https://</p>';
	}
	elseif ( !$status_ok ) {
		$msg = '<p class="error">our server was unable to connect to that website. sorry!</p>';
	}
	else {
		if ( API ) { if ($api == true) { die; } }
		$msg = '<p class="error">creation of your '.$site.' URL failed for some reason.</p>';
	}
}
else {
	if ( isset($_GET['id']) ) {
		$id = mysql_escape_string($_GET['id']);
	}
	elseif ( REWRITE ) {
		$explodo = explode('/', $_SERVER['REQUEST_URI']);
		$id = mysql_escape_string($explodo[count($explodo)-1]);
	}
	else {
		$id = '';
	}
	
	if ( $id != '' && $id != basename($_SERVER['PHP_SELF']) ) {
		$location = $lilurl->get_url($id);
		
		if ( $location != -1 ) { 
			if ( LOG ) {
				$ip = $_SERVER['REMOTE_ADDR'];
				$rec = geoip_record_by_name($ip);
				$loc = $rec['city'].", ".$rec['region'].", ".$rec['country_name'];
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$referer = $_SERVER['HTTP_REFERER'];
				logHit($hitlog, $id, $ip, $loc, $agent, $referer);
			}
			header('Location: '.$location, true, 301);			
		}
		else {
			$msg = '<p class="error">sorry, but that '.$site.' URL isn\'t in our database.</p>';
		}
	}
}

function checkStatus($url) {
 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSLVERSION, 3);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);	
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);

	$page = curl_exec($ch);
 
	if(curl_error($ch)) {
		file_put_contents($errorlog, curl_error($ch) . "\n", FILE_APPEND);
	}

	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
	curl_close($ch);
 
	if ($httpcode >= 200 && $httpcode <= 302) return true;
	else return false;
}

function logHit($hitlog, $id, $ip, $location, $agent, $referer) {

	$datum = date("m/d/y h:i:sA");
	$db = new PDO('sqlite:'.$hitlog);
	if (!$db) die ($error);
	$db->exec("INSERT INTO hits (id, date, ip, location, agent, referer) VALUES ('".$id."', '".$datum."', '".$ip."', '".$location."', '".$agent."', '".$referer."');");
	$db = NULL;

}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN">
<html>
	<head>
		<title>URL+thumb generator</title>
		
		<link rel="canonical" href="<?php echo $site; if ( !DOCROOT ) { echo "/url"; } ?>">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<html prefix="og: http://ogp.me/ns#">
		<meta property="og:type" content="website" />
		<meta property="og:title" content="<?php echo $site; ?> URL+thumb generator" />
		<meta property="og:url" content="<?php echo $site; if ( !DOCROOT ) { echo "/url"; } ?>" />
		<meta property="og:image" content="<?php echo $logo; ?>" />
		<link rel="stylesheet" href="./includes/jq.css">
		<link rel="stylesheet" type="text/css" href="./includes/style.css">
		<script src="./includes/jquery-1.10.2.min.js"></script>
		<script src="./includes/jq.js"></script>
		<script>
	$(function() {
		var cache = {},
			lastXhr;
		$( "#longurl" ).autocomplete({
			minLength: 3,
			source: function( request, response ) {
				var term = request.term;
				if ( term in cache ) {
					response( cache[ term ] );
					return;
				}
				lastXhr = $.getJSON( "./includes/search.php", request, function( data, status, xhr ) {
					cache[ term ] = data;
					if ( xhr === lastXhr ) {
						response( data );
					}
				});
			}
		});
	});
	</script>		
	<style type="text/css">		
		</style>
	</head>
	
	<BODY BGCOLOR="#000000" TEXT="#ffffff" STYLE="width:640px" LINK="#00ff00" ALINK="#ff00ff" VLINK="#00ffff"> 
	<body onload="document.getElementById('longurl').focus()">
	
	    <UL><TABLE HEIGHT="100%" ALIGN="CENTER" BORDER="0"><TBODY><TR>
        <TD ALIGN="CENTER"><IMG SRC="<?php echo $logo; ?>"><br>
		
		<h1>URL+thumb generator</h1><br>
		<?php echo $msg; ?>
		<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">		
			<fieldset>
				<label for="longurl">enter a Uniform Resource Locator:</label><br>
				<input type="text" name="longurl" id="longurl" size="36" autocomplete="on" />
				<input type="submit" name="submit" class="submit" id="submit" value="make an <?php echo $site; ?> URL" />
			</fieldset>
		
		</form><br>
</TD>
      </TR></TBODY>	  
    </TABLE></UL>	
	</body>
</html>