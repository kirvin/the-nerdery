<?php
	class Form {
		var $formElements;
		var $formtype;
		var $formname;
		var $formaction;
		var $paction;
		var $validate;
		var $formquery;
		var $formheader;
		var $submitlabel;
		var $deletelabel;
		var $editbuttonlabel;
		var $backbuttonlabel;
		var $editaction;
		var $deleteaction;
		var $deleteformaction;
		var $editformaction;
		var $backlabel;
		var $backaction;
		var $nextlabel;
		var $nextaction;
		var $actionlabel;
		var $actionformaction;
		var $actionaction;
		var $selecttype;
		var $selectMask;
		var $valueColumn;
		var $displayColumn;
		var $method = "post";
		var $width = "100%";
		function Form ($ftype, $fname, $faction, $pa) {
			$formElements = array ();
			$this->formname = $fname;
			$this->formtype = $ftype;
			$this->formaction = $faction;
			$this->submitlabel = "Submit";
			$this->deletelabel = "";
			$this->editlabel = "";
			$this->backaction = null;
			$this->enctype = "www/url-encoded";
			$this->paction = $pa;
			$this->validate = false;
		}
		function addFormElement ($n, $t, $l, $v, $r) {
			if ($r == "yes" || $r = true)
				$this->validate = true;
			$this->formElements[count($this->formElements)] = new FormElement ($n, $t, $l, $v, $r);
		}
		function addFormElementObject ($obj) {
			$this->formElements[count($this->formElements)] = $obj;
		}
		function addHidden ($hn, $hv) {
			$this->addFormElement ($hn, "hidden", "", $hv, "no");
		}
		function addSection ($s) {
			$this->formElements[count($this->formElements)] = new FormElement ("", "section", $s, "", "no");
		}
		function addDescription ($d) {
			$this->formElements[count($this->formElements)] = new FormElement ("", "description", $d, "", "no");
		}
		function setBackButton ($blabel, $baction) {
			if ($blabel != "") {
				$this->backlabel = $blabel;
				$this->backaction = $baction;
			}
		}
		function setNextButton ($nlabel, $naction) {
			if ($nlabel != "") {
				$this->nextlabel = $nlabel;
				$this->nextaction = $naction;
			}
		}
		function setActionButton ($al, $aa, $afa) {
			$this->actionlabel = $al;
			$this->actionformaction = $afa;
			$this->actionaction = $aa;
		}
		function setEncType ($e) { $this->enctype = $e; }
		function setSubmit ($s) { $this->submitlabel = $s; }
		function setEdit ($e, $ea, $efa) {
			$this->editbuttonlabel = $e;
			$this->editaction = $ea;
			$this->editformaction = $efa;
		}
		function setDelete ($d, $da, $dfa) {
			$this->deletebuttonlabel = $d;
			$this->deleteaction = $da;
			$this->deleteformaction = $dfa;
		}
		function setPAction ($p) { $this->paction = $p; }
		function setFormHeader ($h) { $this->formheader = $h; }
		function setValidate ($v) {$this->validate = $v;}
		function setQuery ($q) { $this->formquery = $q; }
		// set parameters for a select form
		function setSelectParams ($st, $q, $vc, $dm, $dc, $es) {
			$this->selecttype = $st;
			$this->formquery = $q;
			$this->valueColumn = $vc;
			$this->displayMask = $dm;
			$this->displayColumns = $dc;
			$this->emptySetMessage = $es;
		}
		// set parameters for an image form
		function setImageParams ($q, $id, $gid, $aid) {
			$this->formquery = $q;
			$this->IMAGES_DIR = $id;
			$this->gallery_id = $gid;
			$this->album_id = $aid;
		}
		function draw () {
			if ($this->formheader != "") {
				echo
				"<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"" . $this->width . "\">" .
						"<tr><td class=\"SectionHeader\">" . $this->formheader . "</td></tr>" .
						"<tr><td class=\"SectionContainer\">";
			}
			if ($this->formtype == "field")
				$this->drawFieldForm ();
			else if ($this->formtype == "select")
				$this->drawSelectForm ();
			else if ($this->formtype == "image")
				$this->drawImageForm ();
			if ($this->formheader != "") {
				echo "</td></tr></table><br>";
			}
		}
		//---------------------------------------------------------------
		// Writes out a gallery of images with form support for selecting
		// an image.
		//---------------------------------------------------------------
		function drawImageForm () {
			$rs = mysql_query ($this->formquery) or die ("ERROR: " . mysql_error() .
				"<br>" . $this->formquery);
			echo "<script language=\"javascript\" type=\"text/javascript\">\n" .
				 "    function onImageSelect_" . $this->formname . "(frm, img_id) {\n" .
				 "      frm.AlbumItemID.value = img_id;\n" .
				 "      frm.submit ();\n" .
				 "    }" .
				 "</script>\n";
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"" . $this->width . "\">" .
				 "<form name=\"" . $this->formname . "\" action=\"" . $this->formaction . "\" method=\"post\" " .
				 "onsubmit=\"return validate" . $this->formname . "(this);\">" .
				 "<input type=\"hidden\" name=\"paction\" value=\"editItem\">" .
				 "<input type=\"hidden\" name=\"GalleryID\" value=\"" . $this->gallery_id . "\">" .
				 "<input type=\"hidden\" name=\"AlbumID\" value=\"" . $this->album_id . "\">" .
				 "<input type=\"hidden\" name=\"AlbumItemID\" value=\"\">";
			$counter = 1;
			while ($row = mysql_fetch_array ($rs)) {
				$counter = (($counter > 4) ? 1 : $counter);
				if ($counter == 1)
					echo "<tr>";
				echo "<td align=\"center\"><a href=\"javascript: onImageSelect_" . $this->formname .
					" (document." . $this->formname . "," . $row["AlbumItemID"] .
					");\"><img border=\"0\" src=\"" . $this->IMAGES_DIR . $this->gallery_id . "\\" .
					$this->album_id . "\\thumbs\\" . $row["AlbumItemID"] . "." . $row["FileExtension"] .
					"\"></a><br><span class=\"smallBlackText\">" . $row["AlbumItemTitle"] . "</span></td>";
				if ($counter == 4)
					echo "</tr>";
				$counter++;
			}
			echo "</form></table>";
		}
		//---------------------------------------------------------------
		// Writes out a form for selecting one item from many.
		//---------------------------------------------------------------
		function drawSelectForm () {
			$rs = mysql_query ($this->formquery) or die ("ERROR: " . mysql_error() . "<br>" . $this->formquery);
			echo "<script language=\"javascript\" type=\"text/javascript\">\n" .
				 "    function validate" . $this->formname . "(frm) {\n" .
				 "      var valid = false;\n" .
				 "      if (frm." . $this->valueColumn . ".length) {\n" .
				 "          for (var i=0;i<frm." . $this->valueColumn . ".length;i++) {\n" .
				 "            if (frm." . $this->valueColumn . "[i].checked)\n" .
				 "              valid = true;\n" .
				 "          }\n" .
				 "          if (!valid)\n" .
				 "            alert (\"Please select an item to edit or delete.\");\n" .
				 "          return valid;\n" .
				 "      } else {\n" .
				 "        frm." . $this->valueColumn . ".checked = true;return true;" .
				 "      }" .
				 "    }" .
				 "</script>\n";
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"" . $this->width . "\">" .
				 "<form name=\"" . $this->formname . "\" action=\"" . $this->formaction . "\" method=\"post\" " .
				 "onsubmit=\"return validate" . $this->formname . "(this);\">" .
				 "<input type=\"hidden\" name=\"paction\" value=\"\">";
			for ($i=0; $i<count($this->formElements); $i++) {
				if ($this->formElements[$i]->type == "hidden")
					$this->writeHidden ($this->formElements[$i]);
			}
			if ($this->selecttype == "radio") {
				$class = "oddCell";
				if (mysql_affected_rows() == 0) {
					echo "<tr><td align=\"center\">$this->emptySetMessage</td></tr>";
				}
				else {
					while ($row = mysql_fetch_array ($rs)) {
						$class = (($class == "oddCell") ? "evenCell" : "oddCell");
						if ($this->displayMask != "") {
							$temp = str_replace ("<td>*</td>", "<td class=\"" . $class . "\" width=\"20\"><input type=\"radio\" name=\"" . $this->valueColumn .
								"\" value=\"" . $row[$this->valueColumn] . "\"></td>", $this->displayMask);
							$temp = str_replace ("<td>", "<td class=\"" . $class . "\">", $temp);
							$rep_vals = Array();
							for ($i=0; $i<count($this->displayColumns); $i++)
								$temp = str_replace ("{" . $i . "}", $row[$this->displayColumns[$i]], $temp);
							echo $temp;
						}
						else {
							echo "<tr>";
							echo "<td class=\"$class\"><input type=\"radio\" value=\"" . $row[$this->valueColumn] . "\" name=\"" . $this->valueColumn . "\"></td>";
							for ($i=0;$i<count($this->displayColumns);$i++)
								echo "<td class=\"$class\">" . $row[$this->displayColumns[$i]] . "</td>";
							echo "</tr>";
						}
					}
					echo "<tr><td align=\"center\" colspan=\"" . (count($this->displayColumns)+1) . "\">";
					if ($this->editbuttonlabel != "")
						echo "&nbsp;<input type=\"submit\" value=\"" . $this->editbuttonlabel .
							"\" onclick=\"document." . $this->formname .
							".paction.value='" . $this->editaction . "';\">";
					if ($this->deletebuttonlabel != "")
						echo "&nbsp;<input type=\"submit\" value=\"" . $this->deletebuttonlabel .
							"\" onclick=\"document." . $this->formname .
							".paction.value='" . $this->deleteaction . "';\">";
					$this->writeNextButton ();
					$this->writeActionButton ();
					echo "</td></tr>";
				}
			}
			else if ($this->selecttype == "select") {

			}
			echo "</form></table>";
		}
		//---------------------------------------------------------------
		// Writes out a form of field elements
		//---------------------------------------------------------------
		function drawFieldForm () {
			$file_enc_type = false;
			$rfields = array();
			$ffields = array ();
			$rcnt = 0;
			$fcnt = 0;
			for ($i=0; $i<count($this->formElements); $i++) {
				if ($this->formElements[$i]->required == "yes") {
					if ($this->formElements[$i]->type == "date") {
						$rfields[$rcnt][0] = $this->formElements[$i]->name . "_m";
						$rfields[$rcnt++][1] = $this->formElements[$i]->label . " (Month)";
						$rfields[$rcnt][0] = $this->formElements[$i]->name . "_d";
						$rfields[$rcnt++][1] = $this->formElements[$i]->label . " (Day)";
						$rfields[$rcnt][0] = $this->formElements[$i]->name . "_y";
						$rfields[$rcnt][1] = $this->formElements[$i]->label . " (Year)";
						$rcnt++;
					}
					else if ($this->formElements[$i]->multiple) {
						$rfields[$rcnt][0] = $this->formElements[$i]->name . "[]";
						$rfields[$rcnt][1] = $this->formElements[$i]->label;
						$rcnt++;
					}
					// don't check for a value in a file field if something was passed in
					else if ($this->formElements[$i]->type == "file" &&
									 $this->formElements[$i]->value == "") {
						$rfields[$rcnt][0] = $this->formElements[$i]->name;
						$rfields[$rcnt][1] = $this->formElements[$i]->label;
						$rcnt++;
					}
					else if ($this->formElements[$i]->type != "file") {
						$rfields[$rcnt][0] = $this->formElements[$i]->name;
						$rfields[$rcnt][1] = $this->formElements[$i]->label;
						$rcnt++;
					}
				}
				if ($this->formElements[$i]->type == "file") {
					$file_enc_type = true;
					$ffields[$fcnt][0] = $this->formElements[$i]->name;
					$ffields[$fcnt][1] = $this->formElements[$i]->label;
					$fcnt++;
				}
			}

			echo "<script language=\"javascript\" type=\"text/javascript\">\n";
			echo "var req_fields = new Array (";
			for ($i=0; $i < (count($rfields)-1); $i++) {
				echo "'" . $rfields[$i][0] . "',";
			}
			echo "'" . $rfields[count($rfields)-1][0] . "');\n";
			echo "var req_labels = new Array (";
			for ($i=0; $i < (count($rfields)-1); $i++)
				echo "'" . $rfields[$i][1] . "',";
			echo "'" . $rfields[count($rfields)-1][1] . "');\n";
			echo "var file_fields = new Array (";
			for ($i=0; $i < (count($ffields)-1); $i++)
				echo "'" . $ffields[$i][0] . "',";
			echo "'" . $ffields[count($ffields)-1][0] . "');\n";

			echo "function validate" . $this->formname . "(frmname) {\n" .
				 "  var frm = document.forms[frmname];\n" .
				 "	var valid = true;\n" .
				 "	var msg = \"Please enter the following information:\";\n" .
				 "	var currEl;\n" .
				 "  var tempName;\n" .
				 "	for (var i=0; i<req_fields.length; i++) {\n" .
				 "		currEl = frm.elements[req_fields[i]];\n" .
				 "		if ((currEl.type == \"text\" || currEl.type == \"file\") && currEl.value ==\"\") {\n" .
				 "			valid = false;\n" .
				 "			msg += \"\\n * \" + req_labels[i];\n" .
				 "		}\n" .
				 "		else if (currEl.type == \"textarea\" && currEl.innerText ==\"\") {\n" .
				 "			valid = false;\n" .
				 "			msg += \"\\n * \" + req_labels[i];\n" .
				 "		}\n" .
				 "    else if (currEl.type == \"select-one\" && currEl.selectedIndex < 1) {\n" .
				 "        valid = false;\n" .
				 "        msg += \"\\n * \" + req_labels[i];\n" .
				 "    }\n" .
				 "    else if (currEl.type == \"select-multiple\" && currEl.selectedIndex == -1) {\n" .
				 "        valid = false;\n" .
				 "        msg += \"\\n * \" + req_labels[i];\n" .
				 "    }\n" .
				 "	}\n" .
				 "	if (!valid)\n" .
				 "		alert (msg);\n" .
				 "	return valid;\n" .
				"}" .
			"</script>\n";
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">";
			echo "<form name=\"" . $this->formname . "\" action=\"" . $this->formaction . "\" method=\"$this->method\"";
			if ($this->validate) {
				echo " onsubmit=\"return validate" . $this->formname . "(this.name);\"";
			}
			if ($file_enc_type) {
				echo " enctype=\"multipart/form-data\">" .
					"<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"3000000\">";
			}
			else
				echo " enctype=\"application/x-www-form-urlencoded\">";
			echo	"<input type=\"hidden\" name=\"paction\" value=\"" . $this->paction . "\">";
			for ($i=0; $i<count($this->formElements); $i++) {
				$curr_el = $this->formElements[$i];
				// hidden form element
				if ($curr_el->type == "hidden")
					$this->writeHidden ($curr_el);
				// form section
				else if ($curr_el->type == "section")
					$this->writeSection ($curr_el);
				// form description
				else if ($curr_el->type == "description")
					$this->writeDescription ($curr_el);
				// text form element
				else if ($curr_el->type == "text" || $curr_el->type == "password") {
					$this->writeText ($curr_el);
				}
				// image element
				else if ($curr_el->type == "image")
					$this->writeImage ($curr_el);
				// checkbox form element
				else if ($curr_el->type == "checkbox")
					$this->writeCheckbox ($curr_el);
				// file form element
				else if ($curr_el->type == "file")
					$this->writeFileField ($curr_el);
				// textarea form element
				else if ($curr_el->type == "textarea") {
					echo "<tr><td width=\"30%\" class=\"formLabel\" align=\"right\" valign=\"top\">" . $curr_el->label . ":&nbsp;</td><td>" .
						"<textarea rows=\"7\" cols=\"45\" name=\"" . $curr_el->name . "\">" .
						$curr_el->value . "</textarea></td></tr>";
				}
				// date form element
				else if ($curr_el->type == "date") {
					echo "<tr><td width=\"30%\" class=\"formLabel\">" . $curr_el->label . ":&nbsp;</td><td>";
					echo "<select name=\"" . $curr_el->name . "_m\"><option value=\"-1\"/>";
					for ($j=1; $j<13; $j++) {
						echo "<option value=\"" . $j . "\" ";
						if ($j == date("m", $curr_el->value))
							echo "selected";
						echo ">" . $j;
					}
					echo "</select>&nbsp;/&nbsp;<select name=\"" . $curr_el->name . "_d\"><option value=\"-1\"/>";
					for ($j=1; $j<32; $j++) {
						echo "<option value=\"" . $j . "\" ";
						if ($j == date("d", $curr_el->value))
							echo "selected";
						echo ">" . $j;
					}
					echo "</select>&nbsp;/&nbsp;<select name=\"" . $curr_el->name . "_y\"><option value=\"-1\"/>";
					for ($j=date("Y", $curr_el->startDate); $j<date("Y", $curr_el->endDate); $j++) {
						echo "<option value=\"" . $j . "\" ";
						if ($j == date("Y", $curr_el->value))
							echo "selected";
						echo ">" . $j;
					}
					echo "</select></td></tr>";
				}
				// select form element
				else if ($curr_el->type == "select")
					$this->writeSelectBox ($curr_el);
			}
			echo "<tr><td colspan=\"2\" align=\"center\">";
			$this->writeBackButton ();
			if ($this->deletelabel != "")
				echo "&nbsp;<input type=\"button\" value=\"" . $this->deletelabel . "\" onclick=\"javascript: document." .
					$this->formname . ".paction.value='deleteItem';document." . $this->formname . ".submit();\">";
			echo "&nbsp;<input type=\"submit\" value=\"" . $this->submitlabel . "\">";
			if ($this->editlabel != "")
				echo "&nbsp;<input type=\"button\" value=\"" . $this->editlabel . "\" onclick=\"javascript: document." .
					$this->formname . ".paction.value='editItem';document." . $this->formname . ".submit();\">";
			$this->writeActionButton ();
			$this->writeNextButton ();
			echo "</td></tr>";
			echo "</form></table>";
		}
		//---------------------------------------------------------------
		// Writes out a text field
		//---------------------------------------------------------------
		function writeText ($curr_el) {
			echo "<tr><td width=\"40%\" class=\"formLabel\" align=\"right\">" . $curr_el->label . ":&nbsp;</td><td>" .
				"<input size=\"35\" type=\"" . $curr_el->type . "\" name=\"" . $curr_el->name . "\" value=\"" . $curr_el->value .
				"\"></td></tr>";
		}
		//---------------------------------------------------------------
		// Writes out a hidden field
		//---------------------------------------------------------------
		function writeHidden ($ce) {
			echo "<input type=\"hidden\" name=\"" . $ce->name . "\" value=\"" . $ce->value ."\">";
		}
		/**
		 * Writes out an image
		 */
		function writeImage ($ce) {
			echo "<tr><td valign=\"top\" class=\"formLabel\" align=\"right\">" . $ce->label . ":&nbsp;</td>";
			echo "<td><img src=\"" . $ce->value . "\"></td>";
			echo "</tr>";
		}
		//---------------------------------------------------------------
		// Writes out a file field
		//---------------------------------------------------------------
		function writeFileField ($ce) {
			echo "<tr><td align=\"right\"><strong>$ce->label:&nbsp;</strong></td><td>" .
						"<input type=\"file\" name=\"" . $ce->name . "\" value=\"" . $ce->value ."\">" .
					 "</td></tr>";
		}
		//---------------------------------------------------------------
		// Writes out a checkbox
		//---------------------------------------------------------------
		function writeCheckbox ($ce) {
			echo "<tr><td align=\"right\"><strong>$ce->label:&nbsp;</strong></td><td>" .
						"<input type=\"checkbox\" name=\"" . $ce->name . "\" ";
			if ($ce->value == true)
				echo "checked";
			echo			" >" .
					 "</td></tr>";
		}
		//---------------------------------------------------------------
		// Writes out a next button
		//---------------------------------------------------------------
		function writeNextButton () {
			if ($this->nextaction != "")
				echo "&nbsp;<input type=\"button\" value=\"" . $this->nextlabel . "\" onclick=\"document." .
					$this->formname . ".paction.value='" . $this->nextaction . "';document." . $this->formname .
					".submit();\">";
		}
		//---------------------------------------------------------------
		// Writes out a back button
		//---------------------------------------------------------------
		function writeBackButton () {
			if ($this->backaction != "")
				echo "&nbsp;<input type=\"button\" value=\"" . $this->backlabel . "\" onclick=\"document." .
					$this->formname . ".paction.value='" . $this->backaction . "';document." . $this->formname .
					".submit();\">";
		}
		//---------------------------------------------------------------
		// Writes out an action button, if parameters are correct
		//---------------------------------------------------------------
		function writeActionButton () {
			if ($this->actionlabel != "") {
				echo "&nbsp;<input type=\"submit\" value=\"$this->actionlabel\" onclick=\"javascript: document." .
					$this->formname . ".action='" . $this->actionformaction . "';document." . $this->formname .
					".paction.value='" . $this->actionaction . "';document." . $this->formname . ".submit();\">";
			}
		}
		//---------------------------------------------------------------
		// Writes out form sub-section
		//---------------------------------------------------------------
		function writeSection ($ce) {
			echo "<tr><td colspan=\"2\" class=\"SubSectionHeader\">$ce->label</td></tr>";
		}
		//---------------------------------------------------------------
		// Writes out form help/description
		//---------------------------------------------------------------
		function writeDescription ($de) {
			echo "<tr><td colspan=\"2\"><span class=\"SubSectionHeader\">$de->label</span></td></tr>";
		}
		//---------------------------------------------------------------
		// Writes out a single- or multiple-select box
		//---------------------------------------------------------------
		function writeSelectBox ($curr_el) {
			echo "<tr><td width=\"30%\" align=\"right\" valign=\"top\" class=\"formLabel\">" . $curr_el->label . ":&nbsp;</td><td>";
			if ($curr_el->multiple)
				echo "<select name=\"" . $curr_el->name . "[]\" multiple style=\"width: 230px;\">";
			else
				echo "<select name=\"" . $curr_el->name . "\">";
			if ($curr_el->query != "") {
				if (!$curr_el->multiple)
					echo "<option value=\"-1\"></option>";
				$rs = mysql_query ($curr_el->query) or die ("ERROR: " . mysql_error());
				while ($row = mysql_fetch_array ($rs)) {
					echo "<option value=\"" . $row[$curr_el->valueColumn] . "\" ";
					if (is_array ($curr_el->value) && is_numeric (array_search($row[$curr_el->valueColumn], $curr_el->value)))
						echo "selected>";
					else if (!is_array($curr_el->value) && $row[$curr_el->valueColumn] == $curr_el->value)
						echo "selected>";
					else
						echo ">";
					for ($j=0; $j<count($curr_el->displayColumns); $j++) {
						echo $row[$curr_el->displayColumns[$j]];
					}
				}
			}
			echo "</select>";
			if ($curr_el->instructions != "")
				echo "<br><span class=\"forminstructions\">" . $curr_el->instructions . "</span>";
			echo "</td></tr>";
		}
	}

	class FormElement {
		var $type;
		var $name;
		var $label;
		var $value;
		var $required;
		var $startDate;
		var $endDate;
		var $query;
		var $valueColumn;
		var $displayColumns;
		var $instructions;
		var $valueType;
		var $multiple;
		function FormElement ($n, $t, $l, $v, $r) {
			$this->name = $n;
			$this->type = $t;
			$this->label = $l;
			$this->value = $v;
			$this->required = $r;
		}
		function setValueQuery ($sql) {
			$this->valueType = "array";
			$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
			while ($row = mysql_fetch_array ($rs))
				array_push ($this->value, $row[0]);
		}
	}

	//-------------------------------------------------------------------------
	// Creates a data-driven form based on parameters passed in
	//-------------------------------------------------------------------------
	function createForm ($f_arr, $ftype, $fname, $faction, $validate, $submit_val, $back_val) {
		if (count($f_arr) > 0) {
			$file_enc_type = false;
			// if this is a "field" type form and validation is required, then make the js function
			$rfields = array();
			$ffields = array ();
			$rcnt = 0;
			$fcnt = 0;
			for ($i=0; $i<count($f_arr); $i++) {
				if ($f_arr[$i][4]) {
					$rfields[$rcnt][0] = $f_arr[$i][0];
					$rfields[$rcnt][1] = $f_arr[$i][1];
					$rcnt++;
				}
				if ($f_arr[$i][2] == "file") {
					$file_enc_type = true;
					$ffields[$fcnt][0] = $f_arr[$i][0];
					$ffields[$fcnt][1] = $f_arr[$i][1];
					$fcnt++;
				}
			}

			echo
				"<script language=\"javascript\" type=\"text/javascript\" src=\"/scripts/tiny_mce/tiny_mce.js\"></script>\n" .
				"<script language=\"javascript\" type=\"text/javascript\">\n" .
					"tinyMCE.init({\n" .
					"mode : \"textareas\",\n" .
					"theme : \"advanced\",\n" .
					"theme_advanced_buttons1 : \"bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink\",\n" .
					"theme_advanced_buttons2 : \"\",\n" .
					"theme_advanced_buttons3 : \"\",\n" .
					"theme_advanced_toolbar_location : \"top\",\n" .
					"theme_advanced_toolbar_align : \"left\",\n" .
					"theme_advanced_path_location : \"bottom\",\n" .
					"extended_valid_elements : \"a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]\"\n" .
				"});\n" .
				"</script>\n";
			echo "<script language=\"javascript\" type=\"text/javascript\">";
			echo "var req_fields = new Array (";
			for ($i=0; $i < (count($rfields)-1); $i++)
				echo "'" . $rfields[$i][0] . "',";
			echo "'" . $rfields[count($rfields)-1][0] . "');";
			echo "var req_labels = new Array (";
			for ($i=0; $i < (count($rfields)-1); $i++)
				echo "'" . $rfields[$i][1] . "',";
			echo "'" . $rfields[count($rfields)-1][1] . "');";
			echo "var file_fields = new Array (";
			for ($i=0; $i < (count($ffields)-1); $i++)
				echo "'" . $ffields[$i][0] . "',";
			echo "'" . $ffields[count($ffields)-1][0] . "');";

			echo "function validate" . $fname . "(frm) {" .
				 "	var valid = true;" .
				 "	var msg = \"Please enter the following information:\";" .
				 "	var currEl;" .
				 "	for (var i=0; i<req_fields.length; i++) {" .
				 "		currEl = eval (\"frm.\" + req_fields[i]);" .
				 "		if (currEl.type == \"text\" && currEl.value ==\"\") {" .
				 "			valid = false;" .
				 "			msg += \"\\n * \" + req_labels[i];" .
				 "		}" .
				 "		else if (currEl.type == \"textarea\" && currEl.innerText ==\"\") {" .
				 "			valid = false;" .
				 "			msg += \"\\n * \" + req_labels[i];" .
				 "		}" .
				 "	}" .
				 "	if (!valid)" .
				 "		alert (msg);" .
				 "	return valid;" .
				"}" .
			"</script>";

			// start html for form
			echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\">";
			echo "<form name=\"" . $fname . "\" action=\"" . $faction . "\"";
			if ($validate) {
				echo " onsubmit=\"return validate" . $fname . "(this);\"";
			}
			if ($file_enc_type)
				echo " enctype=\"multipart/form-data\"";
			else
				echo " enctype=\"application/x-www-form-urlencoded\"";
			echo " method=\"post\">";
			// for "field" type form
			if ($ftype == "field") {
				for ($i=0; $i<count($f_arr); $i++) {
					if ($f_arr[$i][2] == "hidden") {
						echo "<input type=\"hidden\" name=\"" . $f_arr[$i][0] . "\" value=\"" . $f_arr[$i][3] . "\">";
					}
					else if ($f_arr[$i][2] == "radio") {
						echo "<tr>";
						echo "<td align=\"right\" width=\"30%\"><b>" . $f_arr[$i][1] . ":&nbsp;</b></td><td>";
						for ($j=0; $j<count($f_arr[$i][3]); $j++) {
							echo "<input name=\"" . $f_arr[$i][0] . "\" type=\"radio\" value=\"" . $f_arr[$i][3][$j][0] . "\"";
							if ($f_arr[$i][3][$j][2])
								echo "checked";
							echo ">" . $f_arr[$i][3][$j][1] . "&nbsp;&nbsp;&nbsp;";
						}
						echo "</td></tr>";
					}
					else if ($f_arr[$i][2] == "text") {
						echo "<tr>";
						echo "<td align=\"right\" width=\"30%\"><b>" . $f_arr[$i][1] . ":&nbsp;</b></td><td>";
						echo "<input name=\"" . $f_arr[$i][0] . "\" type=\"text\" value=\"" . $f_arr[$i][3] . "\" size=\"45\"></td>";
						echo "</tr>";
					}
					else if ($f_arr[$i][2] == "textarea") {
						echo "<tr>";
						echo "<td align=\"right\" valign=\"top\" width=\"30%\"><b>" . $f_arr[$i][1] . ":&nbsp;</b></td><td>";
						echo "<textarea name=\"" . $f_arr[$i][0] . "\" rows=\"8\" cols=\"55\">" . $f_arr[$i][3] . "</textarea></td>";
						echo "</tr>";
					}
					else if ($f_arr[$i][2] == "file") {
						echo "<tr><td align=\"right\" valign=\"top\" width=\"30%\"><b>" . $f_arr[$i][1] . ":&nbsp;</b></td><td>";
						echo "<input size=\"32\" type=\"file\" name=\"" . $f_arr[$i][0] . "\"></td></tr>";
					}
					else if ($f_arr[$i][2] == "password") {
						echo "<tr>";
						echo "<td align=\"right\" width=\"30%\"><b>" . $f_arr[$i][1] . ":&nbsp;</b></td><td>";
						echo "<input name=\"" . $f_arr[$i][0] . "\" type=\"password\" value=\"" . $f_arr[$i][3] . "\" size=\"45\"></td>";
						echo "</tr>";
					}
				}
				// render select, back buttons
				echo "<tr><td colspan=\"2\" align=\"center\">";
				if ($submit_val != "") {
					echo "<input type=\"submit\" value=\"" . $submit_val . "\">&nbsp;";
				}
				if ($back_val != "") {
					echo "<input type=\"submit\" value=\"" . $back_val . "\" onclick=\"javascript: document.location='" . $_SERVER["PHP_SCRIPT"] . "';\">";
				}
				echo "</td></tr>";
			}
			// for "select" type forms
			else if ($ftype == "select") {

			}
			echo "</form></table>";
		}
	}

?>