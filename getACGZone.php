<?

/*
   脚本运行环境:	php
   需要的版本号:	不知道 =-=
   运行要求:		支持 cURL 系列函数
   编写:			jixun66
   声明:			谢绝跨省。
*/

/*
 *   配置脚本使其工作
 */


///////////////////////////////// 必须配置 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

$err_msg    = 'Server error.';		// 错误信息
$useragent  = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6';
$ckfile     = 	dirname(__FILE__) . '/'	. 
				'cookies.txt';		// Cookie 保存文件名, 自己配置 .htaccess 保护它。

////////////////////////////////           \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\


///////////////////////////////// 可选配置 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

$showtime   = true;
// 显示缓存时间
$tformat    = 'l \t\h\e jS F, Y';	// 缓存时间格式，参考 php 官网上的 date 函数

$CookieOnly = false;
// 如果设定 CookieOnly 为 True 则不需要登陆信息
$lUsername  = '';					// Acgzone 登陆账号
$lPassword  = '';					// Acgzone 登陆密码

$usecache   = false;
// 如果设定 usecache 为 True 则需要填写下列信息 ( 缓存是个好东西 >.>
$db_host    = 'localhost';			// 数据库地址
$db_port    = '3306';				// 数据库端口，默认 3306
$db_user    = 'root';				// 用户名
$db_pass    = '';					// 密码
$db_name    = 'db_acgzone';			// 数据库名
$db_table   = 'acgzone_bypass';		// 数据表名
$db_charset = 'UTF-8';				// 数据库编码

////////////////////////////////           \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\


/*
 *   ↓ 代码, 除非你知道你在干什么否则请不要乱改..
 */
$base = 'http://acgzone.us/';
function getEntry ($str) {
	preg_match('/class="entry-content\"(.*?)>(?P<c>[\s\S]*?)<p><hr \/>/i', $str, $matches);
	if (!isset($matches ['c'])) {return;}
	$p = '/<div class="wumii-hook">([\s\S]+)/im';
	if(preg_match($p, $matches ['c'])) {
		$matches ['c'] = preg_replace($p, '', $matches ['c'], 1);
	}
	return (str_replace("\r", '', str_replace("\n", '', str_replace('	', '', $matches ['c']))));
}
function allopt ($ch) {
	global $ckfile, $useragent;
	curl_setopt ($ch, CURLOPT_HEADER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_COOKIEJAR,  $ckfile);
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile);
}
function doLogin ($username, $password, $artID) {
	global $base;
	$postdata = "log=". $username ."&pwd=". $password ."&redirect_to=". $url . "/" . $artID;
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $base . "wp-login.php");
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_REFERER, $base . "wp-login.php");
	allopt ($ch);
	$result = getEntry(curl_exec ($ch));
	$ret = getEntry($result);
	curl_close($ch);
	return ($result);
}

function isEntryInvalid ($instr){
	global $err_msg;
	if (trim($instr) == '') {die($err_msg);}
	return($instr == '<p>和谐期间请 <a href="#loginform">登录</a> 后阅读本文!</p>');
}

$ckfile = dirname(__FILE__) . '/cookies.txt';
$Aid = @$_GET['id'];
if ($Aid == '' | !is_numeric($Aid)) {die('Need article ID!');}

if ($usecache) {
	$conn   = mysql_connect($db_host . ':' . $db_port, $db_user, $db_pass, true) or die($err_msg);
	mysql_select_db($db_name, $conn) or die($err_msg);
	mysql_query("set names " . $db_charset, $conn);
	$result = mysql_query("SELECT * FROM " . $db_table . " WHERE id='" . mysql_real_escape_string($Aid) . "'", $conn);
	if($result === false){
		mysql_close($conn);
		die($err_msg);
	}
	$row = mysql_fetch_array($result);
	if (isset($row[0])) {
		if ($showtime) {echo ('<em>Entry loaded from the cache saved at: ' . date($tformat, $row ['time']) . "</em></br>\n");}
		echo (base64_decode($row ['entry']));
		mysql_close($conn);
		exit();
	}
}

$ch = curl_init ($base . $Aid);
allopt ($ch);
$output = getEntry(curl_exec ($ch));
curl_close($ch);
if (isEntryInvalid($output)) {
	$output = doLogin ($lUsername, $lPassword, $Aid);
}
if (isEntryInvalid($output)) {
	die ($err_msg);
}
if ($usecache) {
	$sql = 'insert into `' . $db_table . '` set ' . 
			'`id`=' . mysql_real_escape_string($Aid) . 
			',`entry`=\'' . mysql_real_escape_string(base64_encode($output)) . '\''.
			',`time`=' . mysql_real_escape_string(time());
	mysql_query($sql, $conn);
	mysql_close($conn);
}
echo ($output);
?>