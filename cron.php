#!/usr/bin/php
<?php
/////////////////////////////////////////////////////////////////////////
//さくら側cronで毎時01分から10分ごとに作動してます
//・各YPからindex.txtの取得
//・解体してDBに格納(yellowpage)
//・掲示板アドレスをちょうどいい形に整形して格納(resolvebbs)
//・スレアドレス、リスナー数、レス数をまとめて格納(momentum)
//
///////////////////////////////////////////////////////////////////////////

require_once "dbinfo.php";

//MySQL関連関数
function ConnectMySQL () {//MySQLに接続
	global $MySQLConnectID;
	$MySQLConnectID = mysqli_connect($GLOBALS["DBHOST_PM"], $GLOBALS["DBUSER_PM"], $GLOBALS["DBPASS_PM"]);
	if (!$MySQLConnectID) {
		echo "データベースに接続できませんでした。";
		exit;
	}
	$result = mysqli_select_db($MySQLConnectID,  $GLOBALS["DBNAME_PM"]);
	if (!$result) {
		echo "データベースを選択できませんでした。";
		exit;
	}
	$result = mysqli_query($MySQLConnectID, 'SET NAMES utf8');
	if (!$result) {
		echo "文字コードを指定できませんでした。";
		exit;
	}
	global $MySQLConnectID;
}
function TruncateSQLresolve (){//データベースを空にするresolvebbs
	global $MySQLConnectID;
	$sql = "TRUNCATE TABLE resolvebbs";
	mysqli_query($MySQLConnectID, $sql);
}
function TruncateSQLmomentum (){//データベースを空にするresolvebbs
	global $MySQLConnectID;
	$sql = "TRUNCATE TABLE momentum";
	mysqli_query($MySQLConnectID, $sql);
}
function TruncateSQLyellow (){//データベースを空にするyellowpage
	global $MySQLConnectID;
	$sql = "TRUNCATE TABLE yellowpage";
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

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function SearchThreadSTRB ($BBSurl) {//したらば用現在使われているスレを探す
	$htmldata = file_get_contents($BBSurl);
	preg_match("/(http:\/\/jbbs\.livedoor\.jp)?\/bbs\/read\.cgi\/[a-z]+\/[0-9]{3,6}\/[0-9]{10,11}\//", $htmldata, $URLdata);
	$ResolvedURL = "http://jbbs.livedoor.jp" . $URLdata[0];
	echo "　　　　■ " . $ResolvedURL . " ゲット！<br>\n";
	return $ResolvedURL;
}
function SearchThreadWIWI ($BBSurl) {//わいわい用現在使われているスレを探す
	$htmldata = file_get_contents($BBSurl);
	if ( preg_match("/http:\/\/yy[0-9]+\.kakiko\.com\/*/", $BBSurl) ) {
		preg_match("/(http:\/\/yy[0-9]+\.kakiko\.com)?\/test\/read\.cgi\/[a-z]+[0-9]+\/[0-9]{10,11}\//", $htmldata, $URLdata);
		$ResolvedURL = $BBSurl . $URLdata[0];
		echo "　　　　■" . $ResolvedURL . " ゲット！<br>\n";
		return $ResolvedURL;
	} else {
		preg_match("/test\/read\.cgi\/[a-z]+[0-9]*\/[0-9]{10,11}\//", $htmldata, $URLdata);
		preg_match("/http:\/\/yy[0-9]+\.[0-9]+\.kg\//", $BBSurl, $BBSurlTemp);
		//$BBSurlResolv = preg_replace("/\.[0-9]+\.kg\/", "", $BBSurlTemp);
		$ResolvedURL = $BBSurlTemp[0] . $URLdata[0];
		echo "　　　　■" . $ResolvedURL . " ゲット！<br>\n";
		return $ResolvedURL;
	}
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



function InsertMySQL ($YPfile) {//テキストから整形してデータベースに格納+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	global $MySQLConnectID;
	$YPdata = file_get_contents( dirname(__FILE__) . "/" . $YPfile );
	$YPdata = str_replace("\n","<>",$YPdata);
	$YPdata = explode( "<>", $YPdata );
	$YPcnt = YPfileLine($YPfile);
	$j = 0;
	for( $i=0;$i<$YPcnt;$i++ ){
		$sql = "INSERT INTO yellowpage (`name`,`streamid`,`ip`,`bbs`,`genre`,`syousai`,`listener`,`relay`,`bandwidth`,`codec`,`null0`,`null1`,`null2`,`null3`,`name_enc`,`time`,`click`,`comment`,`null4`,`null5`,`null6`) VALUES ('{$YPdata[$j]}', '{$YPdata[$j+1]}', '{$YPdata[$j+2]}', '{$YPdata[$j+3]}', '{$YPdata[$j+4]}', '{$YPdata[$j+5]}', '{$YPdata[$j+6]}', '{$YPdata[$j+7]}', '{$YPdata[$j+8]}', '{$YPdata[$j+9]}', '{$YPdata[$j+10]}', '{$YPdata[$j+11]}', '{$YPdata[$j+12]}', '{$YPdata[$j+13]}', '{$YPdata[$j+14]}', '{$YPdata[$j+15]}', '{$YPdata[$j+16]}' , '{$YPdata[$j+17]}' , '{$YPdata[$j+18]}', '{$YPdata[$j+19]}', '0')";
		$result = mysqli_query($MySQLConnectID, $sql);
		$j = $j + 19;
		$sql = "SELECT * FROM yellowpage";
		$result = mysqli_query($MySQLConnectID, $sql);
	}
}

function YPfileLine ($YPfile) {//チャンネル数を数える+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	$data = file_get_contents( dirname(__FILE__) . "/" . $YPfile );
	$data = explode( "\n", $data );
	$cnt = count( $data )-1;
	return $cnt;
}


//関数群おわり+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//ここから処理開始++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

global $MySQLConnectID;
ConnectMySQL ();
TruncateSQLyellow ();//いったんDBを空にする
TruncateSQLresolve ();//いったんDBを空にする
TruncateSQLmomentum ();//いったんDBを空にする

//tpをyellowpageに格納
$YPfile = "indextp.txt";
$InSQL = InsertMySQL ($YPfile);

//spをyellowpageに格納
$YPfile = "indexsp.txt";
$InSQL = InsertMySQL ($YPfile);

//テーブルresolvebbsにデータ挿入
$sql = "INSERT INTO resolvebbs (`id`, `name`, `bbs`) SELECT id, name, bbs FROM yellowpage";
$result = mysqli_query($MySQLConnectID, $sql);

//bbsのurlを解決
$sql = "SELECT * FROM yellowpage";
$result = mysqli_query($MySQLConnectID, $sql);
while ($YPdata = mysqli_fetch_array($result)) {
	echo $YPdata['name'] . " " . $YPdata['bbs'] . " ";
	$BBSurl = $YPdata['bbs'];
	//まずしたらばかワイワイかなしかその他かの判定
	$i = 0;
	$i = $i +1;
	if ( empty($BBSurl) ) {//urlなし（本スレ）
		echo "なし(本スレ)<br>\n";
		$ResSQL = "UPDATE resolvebbs SET domain = 'なし', resolvedbbs = 'http://yy25.60.kg/peercastjikkyou/' WHERE id={$i}";
	} else if ( preg_match("/http:\/\/jbbs\.shitaraba\.net\/*/", $BBSurl) ) {//したらば系列
		echo " ";
		if ( preg_match("/http:\/\/jbbs\.shitaraba\.net\/bbs\/read.cgi\/[a-z]+\/[0-9]{4,6}\/[0-9]{10,11}\//", $BBSurl) ) {
			echo "したらばok<br>\n";
			$BBSurl = preg_replace("/l50$/", "", $BBSurl);//末尾につく最新レス（l50）とかをカット
			$ResSQL = "UPDATE resolvebbs SET domain = 'したらば', resolvedbbs = '{$BBSurl}' WHERE id={$i}";
		} else {
			echo "したらばスレを探す関数へ<br>\n";
			$ResolvedBBS = SearchThreadSTRB ($BBSurl);
			$ResSQL = "UPDATE resolvebbs SET domain = 'したらば', resolvedbbs = '{$ResolvedBBS}' WHERE id={$i}";
		}
	} else if ( preg_match("/http:\/\/yy[0-9]+\.kakiko\.com\/*/", $BBSurl) ) {//ワイワイ系列
		if ( preg_match("/http:\/\/yy33\.kakiko\.com\/test\/read\.cgi\/peercast\/*/", $BBSurl) ) {
			echo "PeerCast特設<br>\n";
			$ResSQL = "UPDATE resolvebbs SET domain = 'PeerCast特設', resolvedbbs = '{$BBSurl}' WHERE id={$i}";
		} else if ( preg_match("/http:\/\/yy[0-9]+\.kakiko\.com\/test\/read\.cgi\/*/", $BBSurl) ){
			echo "ワイワイok<br>\n";
			$BBSurl = preg_replace("/l50$/", "", $BBSurl);//末尾につく最新レス（l50）とかをカット
			$ResSQL = "UPDATE resolvebbs SET domain = 'ワイワイ', resolvedbbs = '{$BBSurl}' WHERE id={$i}";
		} else {
			echo "ワイワイスレを探す関数へ<br>\n";
			SearchThreadWIWI ($BBSurl);
			$ResolvedBBS = SearchThreadWIWI ($BBSurl);
			$ResSQL = "UPDATE resolvebbs SET domain = 'ワイワイ', resolvedbbs = '{$ResolvedBBS}' WHERE id={$i}";
		}
	} else if ( preg_match("/http:\/\/yy[0-9]+\.[0-9]+\.kg\/*/", $BBSurl) ) {
		echo " ";
		if ( preg_match("/http:\/\/yy[0-9]+\.[0-9]+\.kg\/test\/read\.cgi\/*/", $BBSurl) ) {
			echo "ワイワイok<br>\n";
			$BBSurl = preg_replace("/l50$/", "", $BBSurl);//末尾につく最新レス（l50）とかをカット
			$ResSQL = "UPDATE resolvebbs SET domain = 'ワイワイ', resolvedbbs = '{$BBSurl}' WHERE id={$i}";
		} else {
			echo "ワイワイスレを探す関数へ<br>\n";
			SearchThreadWIWI ($BBSurl);
			$ResolvedBBS = SearchThreadWIWI ($BBSurl);
			$ResSQL = "UPDATE resolvebbs SET domain = 'ワイワイ', resolvedbbs = '{$ResolvedBBS}' WHERE id={$i}";
		}
	} else {//その他
		if ( preg_match("/http:\/\/temp\.orz\.hm\/*/", $BBSurl) ) {
			echo "TP公式<br>\n";
		} else if ( preg_match("/http:\/\/twitter\.com\/TemporaryYP/", $BBSurl) ) {
			echo "TP公式<br>\n";
		} else if ( preg_match("/http:\/\/bayonet\.ddo\.jp\/sp\/*/", $BBSurl) ) {
			echo "SP公式<br>\n";
		} else if ( preg_match("/http:\/\/live\.nicovideo\.jp\/*/", $BBSurl) ) {
			echo "ニコ生<br>\n";
		} else if ( preg_match("/http:\/\/www\.ustream\.tv\/*/", $BBSurl) ){
			echo "ustream<br>\n";
		} else if ( preg_match("/http:\/\/[a-z]+\.twitch\.tv\/*/", $BBSurl) ) {
			echo "twitch<br>\n";
		}else {
			echo "その他<br>\n";
		}
		$ResSQL = "UPDATE resolvebbs SET domain = 'その他', resolvedbbs = '{$BBSurl}' WHERE id={$i}";
	}//コンタクトURLの判定ここまで
	$ResolveResult = mysqli_query($MySQLConnectID, $ResSQL);//ドメインと最新スレを格納
}

//テーブルmomentumにデータ挿入
$sql = "INSERT INTO momentum (id, name, resolvedbbs) SELECT id, name, resolvedbbs FROM resolvebbs";
$result = mysqli_query($MySQLConnectID, $sql);
$sql = "SELECT * FROM momentum";
$result = mysqli_query($MySQLConnectID, $sql);
$sql = "UPDATE momentum, yellowpage SET momentum.listener = yellowpage.listener WHERE momentum.id = yellowpage.id";
$result = mysqli_query($MySQLConnectID, $sql);


//MySQLとの接続を閉じて終了
CloseMySQL ($MySQLConnectID);

?>
