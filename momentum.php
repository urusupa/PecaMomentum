<!DOCTYPE html>
<html lang="ja">
	<head>
	<meta charset="utf-8">
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<title>PecaMomentum</title>
	<link rel="shortcut icon" href="favicon.ico">
	<link href="css/usr.css" rel="stylesheet">
	</head>
<body>

<?php

require_once "dbinfo.php";

//MySQL関連関数
function ConnectMySQL () {//MySQLに接続
	global $MySQLConnectID;
	$MySQLConnectID = mysqli_connect($GLOBALS["DBHOST_PM"], $GLOBALS["DBUSER_PM"], $GLOBALS["DBPASS_PM"]);
	if (!$MySQLConnectID) {
		echo "データベースに接続できませんでした。";
		exit;
	}
	$result = mysqli_select_db($MySQLConnectID, $GLOBALS["DBNAME_PM"]);
	if (!$result) {
		echo "データベースを選択できませんでした。";
		exit;
	}
	$result = mysqli_query($MySQLConnectID, 'SET NAMES utf8' );
	if (!$result) {
		echo "文字コードを指定できませんでした。";
		exit;
	}
	global $MySQLConnectID;
}

function TruncateSQL (){//データベースを空にする
	global $MySQLConnectID;
	$sql = "TRUNCATE TABLE temp";
	mysqli_query($MySQLConnectID, $sql);
}

function CloseMySQL ($MySQLConnectID) {//MySQLから切断
	global $MySQLConnectID;
	$MySQLConnectID = mysqli_close($MySQLConnectID);
	if (!$MySQLConnectID) {
		echo "データベースとの接続を閉じられませんでした。";
		exit;
	}
}
//関数群+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++





//30分間のレスを取得してres/min出力
//値をリスナー数で割ってリスナーパワー

require_once 'simple_html_dom.php';

$BBSurl = "http://jbbs.livedoor.jp/bbs/read.cgi/game/42624/1355451338/";




function FuncMomentum ($BBSurl) {//レス数を取得し計算して返す
	global $MySQLConnectID;
	$pagedata = file_get_html($BBSurl);
	$elements = $pagedata->find('dl[id=thread-body] dt');

	foreach($elements as $elements){
		$BBSdata = preg_replace('/<dt><a\shref=\"\/bbs\/read\.cgi\/[a-z]+\/[0-9]+\/[0-9]+\/[0-9]+\">/', "", $elements);
		$BBSdata = preg_replace('/(<\/a>\s.<a\shref=\"mailto.sage\"><b>.+<\/a>.|<\/a>\s.<font\scolor=\".{7}\"><b><font\scolor=.{7}>.+<\/font><\/b><\/font>.)+/u', "<>", $BBSdata);
		$BBSdata = preg_replace('/\(.\)\s/u', "<>", $BBSdata);
		$BBSdata = preg_replace('/\//', "-", $BBSdata);

		echo htmlspecialchars($BBSdata)."<br />";

		$BBSdata = explode( "<>", $BBSdata );

		$ResTime = $BBSdata[1] . " " . $BBSdata[2];
		$sql = "INSERT INTO temp (`resnum`,`name`,`timestamp`) VALUES ('{$BBSdata[0]}', '{$BBSdata[1]}', '{$ResTime}')";
		$result = mysqli_query($MySQLConnectID, $sql);
		$sql = "SELECT * FROM temp";
		$result = mysqli_query($MySQLConnectID, $sql);
	}

	$sql = "SELECT * FROM temp ORDER BY resnum DESC LIMIT 10";
	$result = mysqli_query($MySQLConnectID, $sql);
	while ($BBSdata = mysqli_fetch_array($result)) {
		echo $BBSdata[0] . " " . $BBSdata[2] . "<br>";
	}

	echo "<br /><br />" . $BBSdata[0];


	$pagedata->clear();//メモリの開放
	unset($elements);//メモリの開放
	unset($pagedata);//メモリの開放
}


function Get5ResLoop () {
	global $MySQLConnectID;
	$sql = "SELECT * FROM resolvebbs";
	$result = mysqli_query($MySQLConnectID, $sql);
	$ResolveCnt = 0;
	while ($GetResdata = mysqli_fetch_array($result)) {
		//print_r($GetResdata);
		$SUREurl = $GetResdata[4];
		echo $SUREurl . "<br>";
		$ResolveCnt = $ResolveCnt + 1;
		//Get5Res($SUREurl, $ResolveCnt);
		echo "<br>リゾルブカウント" . $ResolveCnt . "<br><br>";
	}
}

function Get5Res ($SUREurl, $ResolveCnt) {//最新5レスを取得する
	global $MySQLConnectID;
	$BBSurl = $SUREurl . "l5";
	$pagedata = file_get_html($BBSurl);
	$elements = $pagedata->find('dl[id=thread-body] dd');
	$j = 0;
	foreach($elements as $elements){
		$BBSdata = preg_replace('/<dd>\s/', "", $elements);
		$BBSdata = preg_replace('/\s<br><br>\s/', "\n", $BBSdata);
		$BBSdata = preg_replace('/<a\shref=\"\/bbs\/read\.cgi\/[a-z]+\/[0-9]+\/[0-9]+\/[0-9]+\"\starget=\"_blank\">&gt;&gt;[0-9]+<\/a><br>/', "", $BBSdata);
		$BBSdata = preg_replace('/<br>/', "　", $BBSdata);
		$BBSdata = preg_replace('/\s\s<!\-\-\sgoogle_ad_section_end\s\-\->/', "", $BBSdata);
		$BBSdata = mb_strimwidth($BBSdata, 0, 60, "...\n", "UTF-8");
		$Res5[$j] = $BBSdata;
		$j = $j + 1;
	}
	$Res5 = $Res5[1] . $Res5[2] . $Res5[3] . $Res5[4] . preg_replace('/\n/', "", $Res5[5]);
	echo "<br>" . $Res5 . "<br>";


	$sql = "UPDATE momentum SET 5res = {$Res5} WHERE id={$ResolveCnt}";
	$result = mysqli_query($MySQLConnectID, $sql);
	$sql = "SELECT * FROM momentum";
	$result = mysqli_query($MySQLConnectID, $sql);

	//$BBSdata->clear();//メモリの開放
	$elements->clear();//メモリの開放
	$pagedata->clear();//メモリの開放
	//unset($BBSdata);//メモリの開放
	unset($elements);//メモリの開放
	unset($pagedata);//メモリの開放
}




ConnectMySQL ();
TruncateSQL ();

//FuncMomentum ($BBSurl);
Get5ResLoop ();



echo $BBSurl . "<br><br><br><br>";

CloseMySQL ($MySQLConnectID);

?>


</body>
</html>
