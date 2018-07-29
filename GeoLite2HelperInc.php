<?php

//
include GeoLite2Helper_PATH . '/Db/Reader.php';
include GeoLite2Helper_PATH . '/Db/Reader/Decoder.php';
include GeoLite2Helper_PATH . '/Db/Reader/InvalidDatabaseException.php';
include GeoLite2Helper_PATH . '/Db/Reader/Metadata.php';
include GeoLite2Helper_PATH . '/Db/Reader/Util.php';

use MaxMind\Db\Reader;

class GeoLite2Helper {
//	public $ipAddress;
	
	/*
	function __construct() {
		$this->ipAddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$this->ipAddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$this->ipAddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$this->ipAddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$this->ipAddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $this->ipAddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$this->ipAddress = getenv('REMOTE_ADDR');
		else
			$this->ipAddress = 'UNKNOWN';
	}
	*/
	
	private function ipAddress() {
		if ( isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER ['HTTP_X_FORWARDED_FOR'];
		}
		else if ( isset ( $_SERVER ['HTTP_X_REAL_IP'] ) ) {
			return $_SERVER ['HTTP_X_REAL_IP'];
		}
		else if ( isset ( $_SERVER ['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER ['HTTP_CLIENT_IP'];
		}
		return $_SERVER ['REMOTE_ADDR'];
		
		
		//
		if (getenv('HTTP_CLIENT_IP'))
			$this->ipAddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$this->ipAddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$this->ipAddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$this->ipAddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $this->ipAddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$this->ipAddress = getenv('REMOTE_ADDR');
		else
			$this->ipAddress = 'UNKNOWN';
	}
	
	private function getDB($ip = NULL) {
		/*
		if (!empty($ip)) {
			$this->ipAddress = $ip;
		}
		*/
		if ( $ip == NULL ) {
			$ip = $this->ipAddress();
		}
		// test
//		$ip .= '1';
//		echo $ip . '<br>' . "\n";
		
		// nếu có cấp độ City -> lấy theo cấp độ City
		if ( file_exists( GeoLite2Helper_PATH . '/GeoLite2-City.mmdb' ) ) {
			$reader = new Reader( GeoLite2Helper_PATH . '/GeoLite2-City.mmdb' );
		}
		// mặc định chỉ lấy Country
		else {
			$reader = new Reader( GeoLite2Helper_PATH . '/GeoLite2-Country.mmdb' );
		}
		
		//
//		$result = $reader->get($this->ipAddress);
		$result = $reader->get($ip);
//		print_r( $result );
		
		//
		$reader->close();
		
		return $result;
	}
	
	
	// lấy vị trí theo tỉnh thành hoặc quốc gia
	public function getUserAddressByIp($ip = NULL) {
		$a = $this->getDB( $ip );
		
		//
		$r = array();
		
		if ( isset( $a['city'] ) ) {
			$r[] = $a['city']['names']['en'];
		}
		
		//
		$r[] = $a['country']['names']['en'];
		
		//
		return implode( ', ', $r );
	}
	
	
	// lấy vị trí theo quốc gia
	public function getUserCountryByIp($ip = NULL) {
		$a = $this->getDB( $ip );
		
		//
		return $a['country']['names']['en'];
	}
	
	// lấy vị trí theo quốc gia (mã code)
	public function getUserCountryCodeByIp($ip = NULL) {
		$a = $this->getDB( $ip );
		
		//
		return $a['country']['iso_code'];
	}
	
	
	// lấy vị trí theo tỉnh thành (mã code)
	public function getUserCityByIp($ip = NULL) {
		$a = $this->getDB( $ip );
		
		//
		if ( isset( $a['subdivisions'] ) ) {
			return $a['subdivisions'][0]['names']['en'];
		}
		else if ( isset( $a['city'] ) ) {
			return $a['city']['names']['en'];
		}
		
		return '';
	}
	
	// lấy vị trí theo tỉnh thành (mã code)
	public function getUserCityCodeByIp($ip = NULL) {
		$a = $this->getDB( $ip );
		
		//
		if ( isset( $a['subdivisions'] ) ) {
			return $a['subdivisions'][0]['iso_code'];
		}
		
		return '';
	}
	
	
	// lấy vị trí theo trên bản đồ google
	public function getUserLocByIp($ip = NULL) {
		$a = $this->getDB( $ip );
		
		//
		if ( isset( $a['location'] ) ) {
			return array(
				'lat' => $a['location']['latitude'],
				'latitude' => $a['location']['latitude'],
				'lon' => $a['location']['longitude'],
				'longitude' => $a['location']['longitude']
			);
		}
		
		return array();
	}
}


