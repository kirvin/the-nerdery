<?php

	function displayError ($errorMessage) {
		echo '
		<script language="javascript" type="text/javascript">
			var errorMessage = "' . $errorMessage . '";
			$(document).ready(function () {
				if (errorMessage != null) {
					$("#errorDialog").dialog({
						title: "Admire this sweet dialog!",
						height: 140,
						modal: true
					}).html("<p>" + errorMessage + "</p>");
				}
			});
		</script>
		';
	}

	/**
	 *
	 *
	 * @param unknown_type $tab
	 * @param unknown_type $t
	 * @param unknown_type $cbtitle
	 */
	function WriteHeader ($tab = 0, $t = "The Nerdery", $cbtitle = null) {
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
				 <head>
				 <html>
				 	<title>' . $t . '</title>
						<script language="javascript" type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
						<script language="javascript" type="text/javascript" src="/static/jquery/js/jquery-ui-1.8.9.custom.min.js"></script>
						<link rel="stylesheet" type="text/css" href="/static/jquery/css/sunny/jquery-ui-1.8.9.custom.css" />
						<link rel="stylesheet" type="text/css" href="/stylesheets/nerdery.css">
						<script language="javascript" type="text/javascript" src="/scripts/voting.js"></script>
					</head>
					<body class="yui-skin-sam">
						<br>
						<div align="center">
		';
		if ($_SESSION["ValidLogin"] == 1)
			echo		"<map name=\"logomap\">" .
							"<area shape=\"circle\" coords=\"55,65,50\" alt=\"Dr. Teeth's website\" href=\"http://drteeth.theirvins.net\">" .
							"<area id=\"showCoolBar\" shape=\"circle\" coords=\"117,107,13\" alt=\"Cool Bar\" style=\"cursor: pointer;\">" .
						"</map>" .
						"<table width=\"800\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">" .
							"<tr>" .
								"<td width=\"290\" height=\"130\" rowspan=\"3\"><img width=\"290\" height=\"130\" src=\"./images/banner_w_logo_toolbar.gif\" usemap=\"#logomap\" border=\"0\"></td>";
		else
			echo		"<map name=\"logomap\">" .
							"<area shape=\"circle\" coords=\"55,65,50\" alt=\"Dr. Teeth's website\" href=\"http://drteeth.theirvins.net\">" .
						"</map>" .
						"<table width=\"800\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">" .
							"<tr>" .
								"<td width=\"290\" height=\"130\" rowspan=\"3\"><img width=\"290\" height=\"130\" src=\"./images/banner_w_logo.gif\" usemap=\"#logomap\" border=\"0\"></td>";
		echo					"<td width=\"510\" height=\"30\" colspan=\"7\"><img width=\"510\" height=\"30\" src=\"./images/banner_n.gif\"></td>" .
							"</tr>" .
							"<tr>";
		if ($tab == 1)
			echo 			"<td width=\"80\" height=\"70\"><a href=\"index.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/home_on.gif\"></a></td>";
		else
			echo			"<td width=\"80\" height=\"70\"><a href=\"index.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/home_off.gif\"></a></td>";
		if ($tab == 2)
			echo 			"<td width=\"80\" height=\"70\"><a href=\"lists.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/list_on.gif\"></a></td>";
		else
			echo			"<td width=\"80\" height=\"70\"><a href=\"lists.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/list_off.gif\"></a></td>";
		if ($tab == 3)
			echo			"<td width=\"80\" height=\"70\"><a href=\"galleries.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/vote_on.gif\"></a></td>";
		else
			echo			"<td width=\"80\" height=\"70\"><a href=\"galleries.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/vote_off.gif\"></a></td>";
		if ($tab == 4)
			echo			"<td width=\"80\" height=\"70\"><a href=\"discuss.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/talk_on.gif\"></a></td>";
		else
			echo			"<td width=\"80\" height=\"70\"><a href=\"discuss.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/talk_off.gif\"></a></td>";
		if ($tab == 5)
			echo			"<td width=\"80\" height=\"70\"><a href=\"news.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/news_on.gif\"></a></td>";
		else
			echo			"<td width=\"80\" height=\"70\"><a href=\"news.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/news_off.gif\"></a></td>";
		if ($_SESSION["ValidLogin"] == 1)
			echo 			"<td width=\"80\" height=\"70\"><a href=\"/login.php?l=true\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/leave_off.gif\"></a></td>";
		else
			echo 			"<td width=\"80\" height=\"70\"><a href=\"/login.php\"><img border=\"0\" width=\"80\" height=\"70\" src=\"./images/enter.gif\"></a></td>";

		echo 				"<td width=\"30\" height=\"70\"><img border=\"0\" width=\"30\" height=\"70\" src=\"./images/banner_e.gif\"></td>" .
							"</tr>" .
							"<tr>" .
								"<td width=\"510\" height=\"30\" colspan=\"7\"><img width=\"510\" height=\"30\" src=\"./images/banner_s.gif\"></td>" .
							"</tr>" .
							"<tr>" .
								"<td class=\"mainContentFrame\" colspan=\"8\">" .
									"<table border=\"0\" width=\"780\" cellpadding=\"2\" cellspacing=\"0\">" .
										"<tr>" .
											"<td align=\"center\">";
		//if ($cbtitle != null) {
		//	writeCP ($cbtitle);
		//}
		echo								"<div class=\"page_content\">";
	}


	/**
	 *
	 *
	 */
	function WriteFooter () {
		echo "<br>";
		writeSearchBar ();
		echo "<br><br>";
		echo 						"</div>" .
									"</td>" .
								"</tr>" .
							"</table>" .
							"<img src=\"/images/green_line.gif\" width=\"750\" height=\"1\">" .
							"<br>" .
							"<a class=\"smallGreenLink\" href=\"/about.htm\">About this site</a>" .
							"&nbsp;<img src=\"/images/green_bullet.gif\">&nbsp;" .
							"<span class=\"smallGreenText\">Questions/Comments:&nbsp;</span>" .
							"<a class=\"smallGreenLink\" href=\"mailto:  Kelly_Irvin@hotmail.com\">webmaster</a>" .
							"&nbsp;<img src=\"/images/green_bullet.gif\">&nbsp;" .
							"<span class=\"smallGreenText\">";
		$rs = mysql_query ("SELECT COUNT(UserID) AS UserCount FROM UserSessions");
		$row = mysql_fetch_array ($rs);
		if ($row["UserCount"] < 1) {
			echo 				"0 users currently logged in";
		}
		else {
			echo				"<a class=\"smallGreenLink\" href=\"user_report.php\">";
			if ($row["UserCount"] == 1)
				echo 			"1 user currently logged in";
			else
				echo $row["UserCount"] . " users currently logged in";
			echo 				"</a>";
		}
		echo				'</span>
							&nbsp;<img src="/images/green_bullet.gif">&nbsp;
							<a class="smallGreenLink" href="/edit_user.php">Change user info</a>
							<br><br>
						</td>
					</tr>
				</table>
				<br><br>
			</div>
			<div id="errorDialog"></div>
			</body>
			</html>
		';
	}


	/*
	 * Writes a section header with option control panel link
	 */
	class SectionHeader {
		var $title = "";
		var $cp = false;
		function SectionHeader ($t, $cp) {
			$this->title = $t;
			$this->cp = $cp;
		}
		function start () {
			echo
				'<table border="0" cellpadding="0" cellspacing="0" class="sectionHeaderTable">' .
					'<tr>' .
						'<td class="sectionHeaderEndCell" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>' .
						'<td background="images/section.header.bg.gif">' .
							'<span class="sectionHeader">' . $this->title . '</span>' .
						'</td>' .
						'<td nowrap class="sectionHeaderEndCell" background="images/section.header.bg.gif">'
		 	;
		}
		function end () {
			echo
						'</td>';
			if ($this->cp) {
				echo
					'<td class="sectionHeaderContentCell" align="right" background="images/section.header.bg.gif"><a href="javascript: toggleControlPanel (document.all.control_panel);"><img border="0" height="32" width="25" src="images/cp.closed.e.gif"></a></td>'
				;
			}
			else {
				echo
					'<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>'
				;
			}
			echo
					'</tr>' .
				'</table>'
			;
			if ($this->cp) {
				echo
					'<div id="control_panel" class="cp_hidden">' .
						'<table border="0" width="750" cellpadding="0" cellspacing="0">' .
						'<form name="cp_form" action="cool_points.php" method="post" onsubmit="return validatePoints (this);">' .
						'<input type="hidden" name="paction" value="assignPoints">' .
						'<input type="hidden" name="returnTo" value="' . $PHP_SELF . '">' .
							'<tr>' .
								'<td width="23"><img height="23" width="23" src="/images/cp.open.nw.gif"></td>' .
								'<td width="704" background="/images/cp.open.n.gif">&nbsp;</td>' .
								'<td width="23"><img height="23" width="23" src="/images/cp.open.ne.gif"></td>' .
							'</tr>' .
							'<tr>' .
								'<td width="23"><img height="25" width="23" src="/images/cp.open.w.gif"></td>' .
								'<td width="704" bgcolor="#FFFFFF" align="center">' .
									'<select name="plus_minus">' .
										'<option value="-1">' .
										'<option value="add">Give' .
										'<option value="subtract">Take away' .
									'</select>' .
									'&nbsp;&nbsp;' .
									'<select name="number_points">' .
										'<option value="-1">' .
										'<option value="1">1' .
										'<option value="2">2' .
										'<option value="5">5' .
										'<option value="10">10' .
										'<option value="20">20' .
										'<option value="25">25' .
										'<option value="50">50' .
									'</select>' .
									'&nbsp;&nbsp;' .
									'Cool Points to ' .
									'&nbsp;&nbsp;' .
									'<select name="userID">' .
										'<option value="-1">';

									$sql = "SELECT * FROM Users WHERE UserID != '" . $_SESSION["UserID"] . "' ORDER BY DisplayName";
									$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
									while ($row = mysql_fetch_array ($rs)) {
										echo "<option value=\"" . $row["UserID"] . "\">" . $row["DisplayName"] . "</option>";
									}

				echo
									'</select>' .
									'&nbsp;&nbsp;' .
									'because <input type="text" name="reasonFor" size="10" value="">' .
									'&nbsp;&nbsp;' .
									'<input type="submit" value="Make it so">' .
								'</td>' .
								'<td width="23"><img height="25" width="23" src="/images/cp.open.e.gif"></td>' .
							'</tr>' .
							'<tr>' .
								'<td width="23"><img height="23" width="23" src="/images/cp.open.sw.gif"></td>' .
								'<td width="704" background="/images/cp.open.s.gif">&nbsp;</td>' .
								'<td width="23"><img height="23" width="23" src="/images/cp.open.se.gif"></td>' .
							'</tr>' .
						'</form>' .
						'</table>' .
					'</div>'
				;
			}


		}
	}



	/*
	 * Writes a sub section with the specified title
	 */
	function writeSectionHeader ($title, $cp = false) {
		$sh = new SectionHeader ($title, $cp);
		$sh->start ();
		$sh->end ();
	}

	/*
	 * Writes a sub section including a search bar
	 */
	function writeSearchBar () {
		writeSectionHeader ("Search The Nerdery");
		$frm = new Form ("field", "searchForm", "search.php", "runSearch");
		$frm->addFormElement ("search_term", "text", "Search For", "", true);
		$frm->method = "get";
		$frm->draw();
	}

?>