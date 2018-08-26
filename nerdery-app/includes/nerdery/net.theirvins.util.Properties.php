<?php
/**
 * Provides an interface into a collection of properties, with
 * methods to read properties from a text file.
 *
 */
class Properties {

	//public static $DEFAULT_FILE = "http://nerdery.webhop.org/nerdery.properties";
	private $props = array();
	private static $instance = null;

	/**
	 * Constructor for a Properties object
	 *
	 * @return Properties
	 */
	private function __construct () {
	}

	/**
	 * Gets the singleon instance of the Properties class
	 *
	 * @return unknown
	 */
	public static function getInstance () {
		if (self::$instance == null)
			self::$instance = new self();
		return self::$instance;
	}

	/**
	 * Loads properties from the specified file.
	 *
	 * @param String $file
	 */
	function loadFromFile () {
		//if ($file == null)
		//	$file = Properties::$DEFAULT_FILE;
		//$temp = $this->loadFileFromURL (Properties::$DEFAULT_FILE);
		$temp = $this->loadFileFromURL ($this->getDefaultUrl());
		if (!$temp) {
			//printf ("could not load file...");
		}
		else {
			//echo "temp: " . $temp;
			$lines = preg_split("/[\s]+/", $temp, -1);
			//print_r ($lines);
			foreach ($lines as $line) {
				$line = rtrim($line);
				$p = preg_split("/=/", $line);
				if (sizeof($p) > 1) {
					$this->props[$p[0]] = $p[1];
				}
			}
		}
		//print_r ($this->props);
	}

	/**
	 *
	 */
	function getDefaultUrl () {
		return "http://" . $_SERVER["SERVER_NAME"] . "/nerdery.properties";
	}

	/**
	 * Reads the contents of a URL and returns as a string
	 *
	 * @param unknown_type $url
	 */
	private function loadFileFromURL ($url) {
		$cprops = curl_init();
		curl_setopt($cprops, CURLOPT_URL, $url);
		//echo "loading from '" . $url . "'";
		curl_setopt($cprops, CURLOPT_HEADER, 0);
		curl_setopt ($cprops, CURLOPT_RETURNTRANSFER, 1);
		$contents = curl_exec ($cprops);
		//echo "contents: " . $contents;
		curl_close($cprops);
		return $contents;
	}

	/**
	 * Gets the property value associated to the specified key.
	 *
	 * @param string $key
	 * @return string (null if not found)
	 */
	function getProperty ($key) {
		return $this->props[$key];
	}

}


?>
