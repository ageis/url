<?php /* lilurl.php ( lilURL class file ) */

class lilURL {

	function lilURL() {
		mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die('Could not connect to database');
		mysql_select_db(MYSQL_DB) or die('Could not select database');	
	}

	function get_id($url) {
		$q = 'SELECT id FROM '.URL_TABLE.' WHERE (url="'.$url.'")';
		$result = mysql_query($q);

		if ( mysql_num_rows($result) ) {
			$row = mysql_fetch_array($result);
			return $row['id'];
		}
		else {
			return -1;
		}
	}

	function get_url($hash) {
		$q = 'SELECT url FROM '.URL_TABLE.' WHERE (hash="'.$hash.'")';
		$result = mysql_query($q);

		if ( mysql_num_rows($result) ) {
			$row = mysql_fetch_array($result);
			return $row['url'];
		}
		else {
			return -1;
		}
	}

	 function get_hash($url) {
                $q = 'SELECT hash FROM '.URL_TABLE.' WHERE (url="'.$url.'")';
                $result = mysql_query($q);

                if ( mysql_num_rows($result) ) {
                        $row = mysql_fetch_array($result);
                        return $row['hash'];
                }
                else {
                        return -1;
                }
        }
	
	function add_url($url) {
		$hash = new PseudoCrypt();
		$id = $this->get_id($url);
		
		if ( $id != -1 ) {
			return true;
		}
		else {
			$id = $this->get_next_id($this->get_last_id());
			$q = 'INSERT INTO '.URL_TABLE.' (id, url, date, hash) VALUES ("'.$id.'", "'.$url.'", NOW(),"'.$hash->udihash($id).'")';
			return mysql_query($q);
		}
	}

	function get_last_id() {	
		$q = 'SELECT id FROM '.URL_TABLE.' ORDER BY date DESC LIMIT 1';
		$result = mysql_query($q);

		if ( mysql_num_rows($result) ) {
			$row = mysql_fetch_array($result);
			return $row['id'];
		}
		else {
			return -1;
		}
	}	

	function get_next_id($last_id) { 
	
		if ( $last_id == -1 ) {
			$next_id = 0;
		}
		else {
			$next_id = $last_id + 1;	
		}
		
		$q = 'SELECT id FROM '.URL_TABLE.' WHERE (id="'.$next_id.'")';
		$result = mysql_query($q);
		
		if ( mysql_num_rows($result) ) {
			$next_id = $this->get_next_id($next_id);
		}
		
		return $next_id;
		
	}

}

class PseudoCrypt {

	private static $golden_primes = array(
		1,41,2377,147299,9132313,566201239,35104476161,2176477521929
	);
 
	private static $chars = array(
		0=>48,1=>49,2=>50,3=>51,4=>52,5=>53,6=>54,7=>55,8=>56,9=>57,10=>65,
		11=>66,12=>67,13=>68,14=>69,15=>70,16=>71,17=>72,18=>73,19=>74,20=>75,
		21=>76,22=>77,23=>78,24=>79,25=>80,26=>81,27=>82,28=>83,29=>84,30=>85,
		31=>86,32=>87,33=>88,34=>89,35=>90,36=>97,37=>98,38=>99,39=>100,40=>101,
		41=>102,42=>103,43=>104,44=>105,45=>106,46=>107,47=>108,48=>109,49=>110,
		50=>111,51=>112,52=>113,53=>114,54=>115,55=>116,56=>117,57=>118,58=>119,
		59=>120,60=>121,61=>122
	);
 
	public static function base62($int) {
		$key = "";
		while($int > 0) {
			$mod = $int-(floor($int/62)*62);
			$key .= chr(self::$chars[$mod]);
			$int = floor($int/62);
		}
		return strrev($key);
	}
 
	public static function udihash($num, $len = 5) {
		$ceil = pow(62, $len);
		$prime = self::$golden_primes[$len];
		$dec = ($num * $prime)-floor($num * $prime/$ceil)*$ceil;
		$hash = self::base62($dec);
		return str_pad($hash, $len, "0", STR_PAD_LEFT);
	}
	
}
?>