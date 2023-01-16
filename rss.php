<?php

include("classes.php");
if (!defined('INSTALLED')) {
    header("Location: ./install/");
    exit;
}

$sql = "SELECT * FROM ".$_qdbs['tpfx']."settings";
$r = $db->_sql($sql);
$row = $db->fetch_row($r);
$sitetitle = htmlentities($row['title'], ENT_XML1);

$urlscheme = (isset($_SERVER['HTTPS']) ? "https" : "http");
$urlhost = $_SERVER['HTTP_HOST'];
$urlport = "";
if (isset($_SERVER['SERVER_PORT'])) {
	$ports = ["80", "443"];
	if (!in_array($_SERVER['SERVER_PORT'], $ports)) {
		$urlport = ":" . $_SERVER['SERVER_PORT'];
	}
}
$baseurl = sprintf("%s://%s%s", $urlscheme, $urlhost, $urlport);

header("Content-Type: application/xml");

$date = date("r", time());

$content = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
<rss version=\"2.0\">
	<channel>
		<title>" . $sitetitle . " - Latest Quotes</title>
		<link>" . $baseurl . "</link>
		<description>" . $sitetitle . " - Latest Quotes</description>

		<pubDate>".$date."</pubDate>
		<lastBuildDate>".$date."</lastBuildDate>
		<generator>QdbS ".QDBS_VERSION."</generator>
		<ttl>30</ttl>";

$sql = "SELECT * FROM ".$_qdbs['tpfx']."quotes ORDER BY id DESC LIMIT ".intval($pgr->limit)." OFFSET 0";
$r = $db->_sql($sql);
while ($row = $db->fetch_row($r)) {
	$content .="
		<item>
			<title>Quote: ".$row['id']."</title>
			<link>" . $baseurl . "/?".$row['id']."</link>
			<pubDate>".date("r", $row['time'])."</pubDate>
			<description>".htmlentities($row['quote'], ENT_XML1)."</description>
			<guid>" . $baseurl . "/?".$row['id']."</guid>
		</item>";
}

$content .="
	</channel>
</rss>";

echo $content;
