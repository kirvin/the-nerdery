<?php
require_once ("includes/global_functions.php");

$dateFormat = "r";

$sql = "SELECT ne.*, et.EventTypeName FROM NerderyEvents ne JOIN NerderyEventType et ON ne.EventTypeID=et.EventTypeID ORDER BY EventDate DESC LIMIT 20";

$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
?>
<rss version="2.0">
  <channel>
    <title>Nerdery News</title>
    <link>http://nerdery.theirvins.net/</link>
    <description>Recent activity on The Nerdery.</description>
    <language>en-us</language>
    <pubDate><?php echo date_format(date_create(), $dateFormat); ?></pubDate>
    <lastBuildDate><?php echo date_format(date_create(), $dateFormat); ?></lastBuildDate>
    <docs>http://nerdery.theirvins.net/nerdery_rss.php</docs>
    <managingEditor>drteeth@theirvins.net</managingEditor>
    <webMaster>drteeth@theirvins.net</webMaster>
    <ttl>1</ttl>

<?php
while ($row = mysql_fetch_array ($rs)) {
?>
	<item>
		<title><?php echo htmlentities($row["EventTitle"]); ?></title>
		<?php
			echo "<link>http://nerdery.theirvins.net" . htmlentities($row["EventURL"]) . "</link>";
		?>
			<description><?php echo htmlentities($row["EventDescription"]); ?></description>
			<pubDate><?php echo date($dateFormat, strtotime($row["EventDate"])); ?></pubDate>
			<guid>http://nerdery.theirvins.net/<?php echo htmlentities($row["EventURL"]) . "&amp;rssEvent=" . $row["EventID"]; ?></guid>
		</item>
<?php
}
?>
	</channel>
</rss>



