<?php

/**
 *
 */
class VotingList {
	var $listID;
	var $listTitle;
	var $listDescription;
	var $listOwner;
	var $createdDate;
	var $listItems;
	var $itemCount;
	var $isPublic;
	var $lastModified;

	/**
	 *
	 *
	 * @param unknown_type $list_id
	 * @return VotingList
	 */
	function VotingList ($list_id) {
		$exists = false;
		$sql = "SELECT * FROM Lists WHERE ListID=" . $list_id;
		$rs = mysql_query ($sql);
		if (mysql_num_rows ($rs) > 0) {
			$row = mysql_fetch_array ($rs);
			$this->listID = $row["ListID"];
			$this->listTitle = $row["ListTitle"];
			$this->listDescription = $row["ListDescription"];
			$this->listOwner = $row["ListOwner"];
			$this->listCreationDate = $row["CreatedDate"];
			$this->isPublic = $rs["IsPublic"];
			$this->lastModified = $rs["LastModified"];
			$this->listItems = getListItems ($this->listID, $this->isPublic);
			$this->itemCount = count($this->listItems);
			$exists = true;
		}
		return $exists;
	}
}
