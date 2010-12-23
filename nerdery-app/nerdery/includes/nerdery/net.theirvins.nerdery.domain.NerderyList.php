<?php
require_once("includes/nerdery/net.theirvins.util.MySQLUtil.php");
require_once("includes/doctrine/Doctrine.php");
require_once("includes/doctrine/Doctrine/Record.php");

/**
 * 
 *
 */
class NerderyList extends Doctrine_Record {

	/*
	private $id = -1;
	private $title = "";
	private $description = "";
	private $createdDate = null;
	private $lastModified = null;
	private $isPublic = false;
	private $owner = null;
	*/


	public function construct() {
		$this->createdDate = date_format(date_create(), MySQLUtil::$MYSQL_DATE_FORMAT);
		$this->lastModified = date_format(date_create(), MySQLUtil::$MYSQL_DATE_FORMAT);
		//echo "lastModified: " . date_format($this->lastModified, MySQLUtil::$MYSQL_DATE_FORMAT);
	}

	/*
	public function getId() {
		return $this->id;
	}
	public function setId ($i) {
		$this->id = $i;
	}

	public function getTitle () {
		return $this->title;
	}
	public function setTitle ($t) {
		$this->title = $t;
	}

	public function getDescription () {
		return $this->description;
	}
	public function setDescrpition ($d) {
		$this->description = $d;
	}

	public function getCreatedDate () {
		return $this->createdDate;
	}
	public function setCreatedDate ($d) {
		$this->createdDate = $d;
	}

	public function getLastModified () {
		return $this->lastModified;
	}
	public function setLastModified ($d) {
		$this->lastModified = $d;
	}

	public function getIsPublic () {
		return $this->isPublic;
	}
	public function setIsPublic ($p) {
		$this->isPublic = $p;
	}

	public function getOwner () {
		return $this->owner;
	}
	public function setOwner ($o) {
		$this->owner = $o;
	}
	*/

	/**
	 * 
	 *
	 */
	public function setTableDefinition() {
		$this->setTableName("lists");
        $this->hasColumn(
        	'ListID as id', 
        	'int', 
        	4,
        	array (
        		'notnull'=>true,
        		'primary'=>true,
        		'unsigned'=>true,
        		'autoincrement'=>true
        	)
        );
        $this->hasColumn (
        	'ListTitle as title',
        	'varchar',
        	150
        );
        $this->hasColumn (
        	'ListDescription as description',
        	'varchar',
        	250
        );
        $this->hasColumn (
        	'CreatedDate as createdDate',
        	'datetime',
        	8,
        	array (
        		'notnull'=>true,
        		'default'=>'now()'
        	)
        );
        $this->hasColumn (
        	'LastModified as lastModified',
        	'datetime',
        	8,
        	array (
        		'notnull'=>true,
        		'default'=>'now()'
        	)
        );
        $this->hasColumn (
        	'IsPublic as isPublic',
        	'bit',
        	1,
        	array (
        		'notnull'=>true,
        		'default'=>0
        	)
        );
      	$this->hasColumn (
        	'ListOwner as owner',
        	'varchar',
        	16,
        	array(
        		'notnull'=>true
        	)
        );
	}

}

?>
