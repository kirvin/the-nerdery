<?php
require_once ("nerdery/net.theirvins.nerdery.util.StringUtils.php");


$MAX_PAGE_COMMENTS = 50;

/**
 * Class to represent a discussion
 *
 */
class Discussion {
	var $discussionID;
	var $discussionTitle;
	var $discussionDescription;
	var $commentCount;
	var $discussionComments;

	function Discussion ($did, $init_comments) {
		$sql = "SELECT * FROM Discussions WHERE DiscussionID=" . $did;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
		$row = mysql_fetch_array ($rs);
		$this->discussionID = $row["DiscussionID"];
		$this->discussionTitle = $row["DiscussionTitle"];
		$this->discussionDescription = $row["DiscussionDescription"];

		$sql = "SELECT COUNT(CommentID) AS CommentCount FROM DiscussionComments WHERE DiscussionID=" . $did;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
		$row = mysql_fetch_array ($rs);
		$this->commentCount = $row["CommentCount"];
		if ($init_comments) {
			$this->discussionComments = getDiscussionComments ($did);
		}
	}
}

/**
 * Class to represent a discussion thread
 *
 */
class DiscussionThread {
	var $discussionID;
	var $threadID;
	var $discussionTitle;
	var $threadTitle;
	var $discussionDescription;
	var $commentCount;
	var $threadComments;
	var $commentLevel;

	function DiscussionThread ($did, $tid, $init_comments) {
		$sql = "SELECT * FROM Discussions WHERE DiscussionID=" . $did;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
		$row = mysql_fetch_array ($rs);
		$this->threadID = $tid;
		$this->discussionID = $row["DiscussionID"];
		$this->discussionTitle = $row["DiscussionTitle"];
		$this->discussionDescription = $row["DiscussionDescription"];
		
		$sql = "SELECT * FROM DiscussionComments WHERE DiscussionID=" . $did . " AND CommentID=" . $tid;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
		$row = mysql_fetch_array ($rs);
		$this->threadTitle = $row["CommentSubject"];
		$this->commentLevel = $row["CommentLevel"];
		
		//$sql = "SELECT COUNT(CommentID) AS CommentsCount FROM DiscussionComments WHERE DiscussionID=" . $did;
		$sql = "SELECT COUNT(CommentID) AS CommentCount FROM DiscussionComments WHERE DiscussionID=" . $did . " AND CommentID > " . floor ($tid) . 
			" AND CommentID < " . (floor ($tid)+1);
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
		$row = mysql_fetch_array ($rs);
		$this->commentCount = $row["CommentCount"];
		if ($init_comments) {
			$this->threadComments = getThreadComments ($did, $tid, $this->commentLevel);
		}
		else
			$this->threadComments = null;
	}
	function getDiscussionComment ($cid) {
		$answer = null;
		if ($this->threadComments != null) {
			$found = false;
			$i = 0;
			while (!$found) {
				if ($this->threadComments[$i]->commentID == $cid) {
					$found = true;
					$answer = $this->threadComments[$i];	
				}
				$i++;
			}
		}
		return $answer;
	}
	
}


class DiscussionComment {
	var $discussionID;
	var $commentID;
	var $commentSubject;
	var $commentText;
	var $commentLevel;
	var $commentDate;
	var $commentAuthor;
	var $commentAuthorName;
	var $authorSignature;
	
	function DiscussionComment ($did, $cid, $csub, $ctxt, $clvl, $cdate, $cauth, $cauthname, $cauthsig) {
		$this->discussionID = $did;
		$this->commentID = $cid;
		$this->commentSubject = $csub;
		$this->commentText = $ctxt;
		$this->commentLevel = $clvl;
		$this->commentDate = $cdate;
		$this->commentAuthor = $cauth;
		$this->commentAuthorName = $cauthname;
		$this->authorSignature = $cauthsig;
	}	
}

/**
 * Gets comments for a discussion
 *
 * 
 * @param unknown_type $did
 * @return unknown
 */
function getDiscussionComments ($did) {
	$sql = "SELECT D.*, A.DisplayName, A.UserID, A.UserSignature FROM DiscussionComments AS D, Users AS A WHERE DiscussionID=" . $did . " AND A.UserID=D.CommentAuthor ORDER BY CommentID";
	$answer = array ();
	$cnt = 0;
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error());
	while ($row = mysql_fetch_array ($rs)) {
		$answer[$cnt++] = new DiscussionComment ($row["DiscussionID"], $row["CommentID"], $row["CommentSubject"], $row["CommentText"],
			$row["CommentLevel"], $row["CommentDate"], $row["CommentAuthor"], $row["DisplayName"], $row["AuthorSignature"]);
	}
	return $answer;
}


function getThreadComments ($did, $tid, $tlevel) {
	$temp_id = ceil($tid);
	$temp_id = (($temp_id == $tid) ? $temp_id+1 : $temp_id);
	$sql = "SELECT MIN(CommentID) AS NextSibling FROM DiscussionComments WHERE DiscussionID=" . $did .
		" AND CommentLevel=" . $tlevel . " AND CommentID > " . $tid . " AND CommentID < " . ceil ($temp_id);
	$rs = mysql_query ($sql) or die ("ERROR:  " . mysql_error() . "<br>" . $sql);
	//echo $sql . "<br>";
	$row = mysql_fetch_array ($rs);
	$next_sibling = $row["NextSibling"];
	$next_sibling = (($next_sibling > 0) ? $next_sibling : $temp_id);
	$sql = "SELECT D.*, A.DisplayName, A.UserID, A.UserSignature FROM DiscussionComments AS D, Users AS A WHERE DiscussionID=" . $did . "  AND A.UserID=D.CommentAuthor AND CommentID >= " . $tid . 
		" AND CommentID < " . $next_sibling;
	//echo $sql;
	$answer = array ();
	$cnt = 0;
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error());
	while ($row = mysql_fetch_array ($rs)) {
		$answer[$cnt++] = new DiscussionComment ($row["DiscussionID"], $row["CommentID"], $row["CommentSubject"], $row["CommentText"],
			$row["CommentLevel"], $row["CommentDate"], $row["CommentAuthor"], $row["DisplayName"], $row["UserSignature"]);
	}
	return $answer;
}

/**
 * 
 * 
 * @param unknown_type $discussion
 * @param unknown_type $start
 * @param unknown_type $page
 */
function writeDiscussionThreads ($discussion, $start, $page = 1) {
	global $MAX_PAGE_COMMENTS;
	$class = "evenCell";
	$curr_comment;
	echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"720\">";
	$i = $start;
	while ($i < count($discussion->discussionComments) && ($i - $start <= $MAX_PAGE_COMMENTS)) {
		$curr_comment = $discussion->discussionComments[$i];
		$class = (($class == "evenCell") ? "oddCell" : "evenCell");
		$link_class = (($curr_comment->commentDate > $_SESSION["PrevVisit"]) ? "boldBlueLink" : "greenLink");
		$sclass = (($curr_comment->commentDate > $_SESSION["PrevVisit"]) ? "smallBlueBoldText" : "smallGreenText");
		echo "<tr>" .
				"<td width=\"150\" class=\"" . $class . "\"><span class=\"$sclass\">" .
					date ("m/d/Y h:m a", strtotime ($curr_comment->commentDate)) .
				"</span></td>" .
				"<td class=\"$class\">" .
					"<table border=\"0\" width=\"460\" cellpadding=\"0\" cellspacing=\"0\">" .
						"<tr>" .
							"<td width=\"" . ($curr_comment->commentLevel * 10) . "\">";
		for ($j=0; $j < $curr_comment->commentLevel; $j++)
			echo "<img src=\"images/blank.gif\" width=\"10\">";
		echo      "</td>" .
							"<td width=\"" . (460 - ($curr_comment->commentLevel * 10)) . "\">" .
			 "<a href=\"view_thread.php?t=" . $curr_comment->commentID . "&p=" . $page . "&d=" . $discussion->discussionID . "\" class=\"" . $link_class . "\">" . $curr_comment->commentSubject . 
			 "</a></td>" .
			 "</tr>" .
			 "</table>" .
			 "</td>" .
			 "<td width=\"110\" class=\"$class\" align=\"right\" valign=\"top\"><span class=\"blueText\">" . 
			 		$curr_comment->commentAuthorName . "</span>" .
			 	"</td>" .
			 "</tr>";
		$i++;
	}
	echo "</table>";
}

/*-----------------------------------------------------------------------------  
 *  Writes out a single discussion thread from the specified DiscussionThread object.
 *-----------------------------------------------------------------------------*/
function writeThread ($discussion, $tid, $application) {
	global $MAX_PAGE_COMMENTS;
	$curr_comment;
	echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"700\">";

	$i = 0;
	$start = $i;
	$done = false;
	
	$class = "evenCell";
	$header_class = "dkgreenhighlighted";
	$curr_comment = $discussion->threadComments[$i];
	while ($i < count($discussion->threadComments)) {
		$class = (($class == "evenCell") ? "oddCell" : "evenCell");
		$header_class = (($header_class == "dkgreenhighlighted") ? "dkyellowhighlighted" : "dkgreenhighlighted");
		$link_class = (($curr_comment->commentDate > $_SESSION["PrevVisit"]) ? "boldBlueLink" : "greenLink");
		echo "<tr>" .
				"<td class=\"" . $header_class . "\" width=\"70\"><span class=\"smallBlueText\">";
		echo 		date("m/d/Y", strtotime ($curr_comment->commentDate));
		echo 	"</span></td>" .
				"<td class=\"" . $header_class . "\" align=\"left\"><span class=\"smallGreenBoldText\">";
		for ($j=0; $j < $curr_comment->commentLevel; $j++)
			echo "<img src=\"images/blank.gif\" width=\"10\">";
		echo 		$curr_comment->commentSubject .
				"</span>&nbsp;<span class=\"smallBlueText\">(" . $curr_comment->commentAuthorName . ")</span></td>" .
				"<td class=\"" . $header_class . "\" align=\"right\">" .
					"<a class=\"smallBoldGreenLink\" href=\"javascript: replyToComment ('" . $curr_comment->commentID . "');\">reply</a>" .
				"</td>" .
			 "</tr>";
				
		echo "<tr>" .
				"<td class=\"" . $class . "\" valign=\"top\">";
		echo 		"<table border=\"0\" width=\"70\" cellpadding=\"0\" cellspacing=\"0\">";
		echo			"<tr>";
		echo				"<td align=\"center\">";
		
		if ($curr_comment->authorSignature == 1)
			echo "<img width=\"50\" height=\"50\" src=\"images/signatures/" . $curr_comment->commentAuthor . ".sig\">";
		else
			echo "<img src=\"images/blank.gif\">";
		echo 					"<br><span class=\"smallGreenText\">" . date ("m/d/Y h:i a", strtotime ($curr_comment->commentDate)) . 
								"</span>";
		echo 					"<div align=\"center\">";
		if ($curr_comment->commentAuthor == $application->getTopTimeWaster()) {
			echo					"<img src=\"/images/top_waster.gif\">";
		}
		if ($curr_comment->commentAuthor == $application->getCoolestUser()) {
			echo					"<img src=\"/images/top_cool.gif\">";
		}
		echo					"</div>";
		echo				"</td>" .
						"</tr>" .
						"<tr>" .
							"<td align=\"center\">" . 
							
							"</td>" .
						"</tr>" .
					"</table>" .
				"<td class=\"" . $class . "\" colspan=\"2\" valign=\"top\">" .
					"<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">" .
						"<tr><td>";
						for ($j=0; $j<$curr_comment->commentLevel; $j++)
							echo "<img src=\"images/blank.gif\" width=\"10\">";
		echo			"</td><td>" .
							StringUtils::formatUserText ($curr_comment->commentText) .
						"</td></tr></table>" .
				"</td>" .
			 "</tr>";
			 
		$curr_comment = $discussion->threadComments[++$i];
	}
	echo "</table>";
}

/*-----------------------------------------------------------------------------  
 *  Writes out a [filtered] list of all discussions.
 *-----------------------------------------------------------------------------*/
function writeDiscussions ($f) {
	if ($f) {
		$date = mktime (0,0,0,date("m"),date("d")-14,date("Y"));
		$sql = "SELECT D.* FROM Discussions AS D WHERE LastModified > '" . date("Y-m-d", $date) . 
			"' ORDER BY DiscussionTitle";
	}
	else
		$sql = "SELECT D.* FROM Discussions AS D ORDER BY DiscussionTitle";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error());
	
	print "<table width=\"95%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">";
	if (mysql_num_rows ($rs) > 0) {
		$curr_class = "evenCell";
		while ($row = mysql_fetch_array ($rs)) {
			$cnt = 0;
			$sql = "SELECT COUNT(CommentID) AS NewComments FROM DiscussionComments WHERE DiscussionID=" . $row["DiscussionID"] .
				" AND CommentDate > '" . $_SESSION["PrevVisit"] . "'";
			$rs2 = mysql_query ($sql);
			$cntrow = mysql_fetch_array($rs2);
			$cnt = $cntrow["NewComments"];

			$curr_class = (($curr_class == "oddCell") ? "evenCell" : "oddCell");
			echo "<tr>" .
					"<td width=\"70%\" colspan=\"2\" valign=\"top\" align=\"left\" class=\"" . $curr_class . "\">";
					if ($row["LastModified"] > $_SESSION["PrevVisit"])
						echo "<a class=\"boldGreenLink\"";
					else
						echo "<a class=\"boldBlueLink\"";
			echo		" href=\"view_discussion.php?discussion_id=" . $row["DiscussionID"] . "\">" .
						$row["DiscussionTitle"] . 
						"</a>&nbsp;<span class=\"blueText\">(" . $cnt . " new)</span>" .
					"</td>" .
					"<td align=\"right\" width=\"30%\" class=\"" . $curr_class . "\">" .
						"<span class=\"boldBlueText\">" . date("m/d/Y", strtotime ($row["CreatedDate"])) . "</span>" .
					"</td>" .
				"</tr>" .
				"<tr>" .
					"<td width=\"5%\" class=\"" . $curr_class . "\"><td colspan=\"2\" class=\"" . $curr_class . "\">" . 
						$row["DiscussionDescription"] . 
					"<br><br></td>" .
				"</tr>";
		}
	}
	else {
		if ($f)
			echo "<tr><td align=\"center\">There are not currently any active discussions.<br><br></td></tr>";
		else
			echo "<tr><td align=\"center\">There are not currently any discussions open.<br><br></td></tr>";
	}
	print "</table>";
}

?>