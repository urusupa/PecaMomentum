<?php

echo <<<EOT
<!DOCTYPE html>
<html lang="ja">
	<head>
	<meta charset="utf-8">
	<meta name="keywords" content="" />
	<meta name="description" content="ピアキャストの勢いを可視化します。" />
	<meta property="og:title" content="http://pecamomentum.flop.jp/" />
	<meta property="og:description" content="ピアキャストの勢いを可視化します。" />
	<meta property="og:type" content="product" />
	<meta property="og:url" content="http://pecamomentum.flop.jp/" />
	<meta property="og:image" content="http://pecamomentum.flop.jp/img/peercast01.png" />
	<meta property="og:site_name" content="PecaMomentum" />
	<meta property="fb:admins" content="100001225876493" />
	<title>PecaMomentum</title>
	<link rel="shortcut icon" href="favicon.ico">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/usr.css" rel="stylesheet">
	<link href='http://fonts.googleapis.com/css?family=Sonsie+One' rel='stylesheet' type='text/css'>
	</head>
<body>
EOT;

require_once "dbinfo.php";

//MySQL関連関数

function ConnectMySQL () {//MySQLに接続
	global $MySQLConnectID;
	$MySQLConnectID = mysqli_connect($GLOBALS["DBHOST_PM"], $GLOBALS["DBUSER_PM"], $GLOBALS["DBPASS_PM"]);
	if (!$MySQLConnectID) {
		echo "データベースに接続できませんでした。";
		exit;
	}
	$result = mysqli_select_db( $MySQLConnectID, $GLOBALS["DBNAME_PM"]);
	if (!$result) {
		echo "データベースを選択できませんでした。";
		exit;
	}
	$result = mysqli_query( $MySQLConnectID, 'SET NAMES utf8' );
	if (!$result) {
		echo "文字コードを指定できませんでした。";
		exit;
	}
	global $MySQLConnectID;
}

function TruncateSQL (){//データベースを空にする
	$sql = "TRUNCATE TABLE yellowpage";
	mysqli_query($sql);
}

function CloseMySQL ($MySQLConnectID) {//MySQLから切断
	$MySQLConnectID = mysqli_close($MySQLConnectID);
	if (!$MySQLConnectID) {
		echo "データベースとの接続を閉じられませんでした。";
		exit;
	}
}


//チャンネル情報
function YPfileLine ($YPfile) {//チャンネル数を数えて表示。$TotalLineに加算していく。++++++++++++++++++++++++++++++++++++++++++++++++
	$data = file_get_contents( $YPfile );
	$data = explode( "\n", $data );
	$cnt = count( $data )-1;
	return $cnt;
}

function ExportTPinfo () {//TPのお知らせ情報取得
	global $MySQLConnectID;
	$sql = "SELECT `syousai`  FROM `yellowpage` WHERE `name` = 'TPからのお知らせ◆お知らせ'";
	$result = mysqli_query($MySQLConnectID, $sql);
	$row = mysqli_fetch_array($result);
	return $row['syousai'];
}
function TPaddress() {//TPのお知らせアドレス取得
	global $MySQLConnectID;
	$sql = "SELECT `bbs`  FROM `yellowpage` WHERE `name` = 'TPからのお知らせ◆お知らせ'";
	$result = mysqli_query($MySQLConnectID, $sql);
	$row = mysqli_fetch_array($result);
	return $row['bbs'];
}
function ExportSPinfo () {//SPのお知らせ情報取得
	global $MySQLConnectID;
	$sql = "SELECT `syousai`  FROM `yellowpage` WHERE `name` LIKE 'SP_※お知らせ%'";
	$result = mysqli_query($MySQLConnectID, $sql);
	$row = mysqli_fetch_array($result);
	return $row['syousai'];
}
function SPaddress() {//SPののお知らせアドレス取得
	global $MySQLConnectID;
	$sql = "SELECT `bbs`  FROM `yellowpage` WHERE `name` LIKE 'SP_※お知らせ%'";
	$result = mysqli_query($MySQLConnectID, $sql);
	$row = mysqli_fetch_array($result);
	return $row['bbs'];
}

//関数群+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++






function InsertMySQL ($YPfile) {//テキストから整形してデータベースに格納+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	$YPdata = @file_get_contents( $YPfile );
	$YPdata = str_replace("\n","<>",$YPdata);
	$YPdata = explode( "<>", $YPdata );
	$YPcnt = YPfileLine($YPfile);
	$j = 0;
	for( $i=0;$i<$YPcnt;$i++ ){
		$sql = "INSERT INTO yellowpage (`name`,`streamid`,`ip`,`bbs`,`genre`,`syousai`,`listener`,`relay`,`bandwidth`,`codec`,`null0`,`null1`,`null2`,`null3`,`name_enc`,`time`,`click`,`comment`,`null4`,`null5`,`null6`) VALUES ('{$YPdata[$j]}', '{$YPdata[$j+1]}', '{$YPdata[$j+2]}', '{$YPdata[$j+3]}', '{$YPdata[$j+4]}', '{$YPdata[$j+5]}', '{$YPdata[$j+6]}', '{$YPdata[$j+7]}', '{$YPdata[$j+8]}', '{$YPdata[$j+9]}', '{$YPdata[$j+10]}', '{$YPdata[$j+11]}', '{$YPdata[$j+12]}', '{$YPdata[$j+13]}', '{$YPdata[$j+14]}', '{$YPdata[$j+15]}', '{$YPdata[$j+16]}' , '{$YPdata[$j+17]}' , '{$YPdata[$j+18]}', '{$YPdata[$j+19]}', '0')";
		$result = mysqli_query($sql);
		$j = $j + 19;
		$sql = "SELECT * FROM yellowpage";
		$result = mysqli_query($sql);
	}
}








function EchoSQL () {//YP情報を整形して表示+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	global $MySQLConnectID;
	$sql = "SELECT * FROM yellowpage WHERE `streamid` <> '00000000000000000000000000000000' ORDER BY relay DESC,time DESC";
	$result = mysqli_query($MySQLConnectID, $sql);
	echo "<table class='yptable table-hover'><tr><th> </th><th>ch名</th><th>掲示板</th><th>ジャンル</th><th>詳細</th><th>コメント</th><th><font size='-2'>リスナー</font></th><th><font size='-2'>リレー</font></th><th>時間</th></tr><tbody>";
	$PopupCnt = 1;//bbscheck()用のカウンタ
	while ($YPdata = mysqli_fetch_array($result)) {
		echo '<tr><td>　</td><td title="' . $YPdata['name'] . '">'
		 . mb_strimwidth($YPdata['name'], 0, 16, "...", "UTF-8") . '</td><td>';
		$bbsdata = $YPdata['bbs'];
		bbscheck($bbsdata, $PopupCnt);//bbsのドメインで表示する文字を変更
		$PopupCnt = $PopupCnt + 1;
		echo '</td><td title="' . $YPdata['genre'] . '">'
		 . mb_strimwidth($YPdata['genre'], 0, 12, "...", "UTF-8") . '</td><td title="' . preg_replace("/(\s.\s)?&lt;(Open|Free|2M\sOver)&gt;/", "", $YPdata['syousai']) . '">'
		 . mb_strimwidth(preg_replace("/(\s.\s)?&lt;(Open|Free|2M\sOver)&gt;/", "", $YPdata['syousai']), 0, 32, "...", "UTF-8") . '</td><td title="' . $YPdata['comment'] . '">'//置換して文字数制限
		 . mb_strimwidth($YPdata['comment'], 0, 32, "...", "UTF-8") . '</td><td id="listener-num">'
		 . $YPdata['listener'] . '</td><td>'
		 . $YPdata['relay'] . '</td><td>'
		 . preg_replace("/:00$/", "", $YPdata['time']) . '</td></tr>';
	}
	echo "</tbody></table>";
}
function bbscheck($bbsdata, $PopupCnt){//YPdata[bbs]が空欄の時用の分岐。書き換え。
	if ( empty($bbsdata) ) {//urlなし（本スレ）
		echo '<a href="http://yy25.60.kg/peercastjikkyou/" target="_blank">なし(本スレ)</a>';
	} else if ( preg_match("/http:\/\/jbbs\.livedoor\.jp\/*/", $bbsdata) ) {//したらば(livedoor)
		echo '<a href="' . $bbsdata . '" ' . res5popup($PopupCnt) . ' target="_blank">したらば</a>';
	} else if ( preg_match("/http:\/\/jbbs\.shitaraba\.net\/*/", $bbsdata) ) {//したらば(shitaraba)
		echo '<a href="' . $bbsdata . '" ' . res5popup($PopupCnt) . ' target="_blank">したらば</a>';
	} else if ( preg_match("/http:\/\/yy[0-9]+\.kakiko\.com\/*/", $bbsdata) ) {//ワイワイ
		if ( preg_match("/http:\/\/yy33\.kakiko\.com\/test\/read.cgi\/peercast\/*/", $bbsdata) ) {
			echo '<a href="' . $bbsdata . '" ' . res5popup($PopupCnt) . ' target="_blank">peercast特設</a>';
		} else {
			echo '<a href="' . $bbsdata . '" ' . res5popup($PopupCnt) . ' target="_blank">ワイワイ</a>';
		}
	} else if ( preg_match("/http:\/\/yy[0-9]+\.[0-9]+\.kg\/*/", $bbsdata) ) {//ワイワイ
		echo '<a href="' . $bbsdata . '" ' . res5popup($PopupCnt) . ' target="_blank">ワイワイ</a>';
	} else {//その他。ここにどんどん追加していけばおｋ
		if ( preg_match("/http:\/\/www\.ustream\.tv\/*/", $bbsdata) ) {
			echo '<a href="' . $bbsdata . '" target="_blank">ustream</a>';
		} else if ( preg_match("/http:\/\/twitter\.com\/TemporaryYP/", $bbsdata) ) {
			echo "TP公式<br>";
		} else if ( preg_match("/http:\/\/bayonet\.ddo\.jp\/sp\/*/", $bbsdata) ) {
			echo "SP公式<br>";
		} else if ( preg_match("/http:\/\/live\.nicovideo\.jp\/*/", $bbsdata) ) {
			echo '<a href="' . $bbsdata . '" target="_blank">ニコ生</a>';
		} else if ( preg_match("/http:\/\/[a-z]+\.twitch\.tv\/*/", $bbsdata) ) {
			echo '<a href="' . $bbsdata . '" target="_blank">twitch</a>';
		} else {
			echo '<a href="' . $bbsdata . '" target="_blank">その他</a>';
		}
	}
}

function res5popup($PopupCnt){
	global $MySQLConnectID;
	$sql = "SELECT * FROM momentum WHERE id = '{$PopupCnt}'";
	$result = mysqli_query($MySQLConnectID, $sql);
	$res5data = mysqli_fetch_array($result);
	$title = 'title="' . $res5data[3] . '"';
	return $title;
}

echo "<div class='wrapper'><div class='header'>";
echo "<h1><a href='http://nyctea.me/PecaMomentum/pecamo.php' class='h1link'>PecaMomentum</a></h1>";
echo "<a href='about.html'>PecaMomentum とは</a><br><br>";
echo "メンテナンス用　<a href='cron.php'>cron.php</a>　<a href='momentum.php'>momentum.php</a>　<a href='stats.php'>stats.php</a><br><br>";
//ソーシャルボタン
echo '<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://pecamomentum.flop.jp/" data-text="PecaMomentum" data-lang="ja" data-related="peersoldier">ツイート</a>';
echo '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
echo '<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fpecamomentum.flop.jp%2F&amp;send=false&amp;layout=button_count&amp;width=256&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:256px; height:21px;" allowTransparency="true"></iframe>';




	ConnectMySQL ();//MySQL接続++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//TruncateSQL ();


	$YPfile = "indextp.txt";
	$TotalLine = 0;
	//$InSQL = InsertMySQL ($YPfile);
	$ChNum = YPfileLine($YPfile)-2;//YPのステータス表示用のチャンネル分2つマイナス
	if ($ChNum == -2){
		$ChNum = $ChNum + 2;
		$Exportinfo = "YPおちてる？";
	} else {
		$TPfiletime = filemtime("indextp.txt");//ファイルサイズ1以上なら更新時間格納
		$Exportinfo = ExportTPinfo();
	}
	echo "<br><br><div class='ypinfo'><b>YP情報</b><br><b><a href='http://temp.orz.hm/yp/' target='_blank'>TP</a></b> ch数:" . $ChNum . "　index.txt更新時間:" . date("m/d G:i",$TPfiletime) . "<br /><a href='" . TPaddress() . "' target='_blank'>お知らせ</a> : " . $Exportinfo;
	$TotalLine = $TotalLine + $ChNum;
	echo "<br><br>";
	$YPfile = "indexsp.txt";
	//$InSQL = InsertMySQL ($YPfile);
	$ChNum = YPfileLine($YPfile)-2;//YPのステータス表示用のチャンネル分2つマイナス
	if ($ChNum == -2){
		$ChNum = $ChNum + 2;
		$Exportinfo = "YPおちてる？";
	} else {
		$SPfiletime = filemtime("indexsp.txt");//ファイルサイズ1以上なら更新時間格納
		$Exportinfo = ExportSPinfo();
	}
	echo "<b><a href='http://bayonet.ddo.jp/sp/' target='_blank		'>SP</a></b> ch数:" . $ChNum . "　index.txt更新時間:" . date("m/d G:i",$SPfiletime) . "<br /><a href='" . SPaddress() . "' target='_blank'>お知らせ</a> : " . $Exportinfo;
	$TotalLine = $TotalLine + $ChNum;
	echo "<br><br>トータルチャンネル数 : " . $TotalLine . "<br />";
	echo "最新スレ探索時間: " . date("m/d G:i", strtotime("1 minute", $TPfiletime)) . "</div></div><br />";
	echo "ページ更新時間: " . date("m月d日 G:i") . "<br>";

	EchoSQL ();//チャンネル一覧表示

	echo "</div>";

	CloseMySQL ($MySQLConnectID);//MySQL閉じます++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


echo <<<EOT
<br>
<br>
<br>
<br>


	<!--- to the top -->
	<div class="tothetop"><a href="#header"><img src="img/tothetop.png" height="41" width="41"></a></div>
	<script src="js/jquery-1.8.3.min.js"></script>
	<script src="js/usr.js"></script>

</body>
</html>
EOT;


?>

