<?php
/**
 *
 * @author drteeth
 *
 */
class ListItem {
	var $ListItemID;
	var $ListItemText;
	var $ListItemURL;
	var $ListItemOwner;
	var $ListItemDate;
	var $OwnerName;
	var $ListItemFile;

	function ListItem ($liid, $lit, $liu, $liow, $lid, $ow, $lif, $lm) {
		$this->ListItemID = $liid;
		$this->ListItemText = $lit;
		$this->ListItemURL = $liu;
		$this->ListItemOwner = $liow;
		$this->ListItemDate = $lid;
		$this->OwnerName = $ow;
		$this->ListItemFile = $lif;
		$this->LastModified = $lm;
	}
}

