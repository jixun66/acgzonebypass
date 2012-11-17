<?

/*
   脚本运行环境:	php
   需要的版本号:	php5
   运行要求:		支持 cURL 系列函数, 以及 php 5 的 mysqli 系列函数
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
$login_info = array(
	array ( 'un' => 'Acgzone 登陆账号 1', 'pw' => '登录密码 1' ),
	array ( 'un' => 'Acgzone 登陆账号 2', 'pw' => '登录密码 2' ),
	array ( 'un' => 'Acgzone 登陆账号 3', 'pw' => '登录密码 3' ),
	array ( 'un' => 'Acgzone 登陆账号 4', 'pw' => '登录密码 4' ),
);

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
srand ((double)microtime()*1000000);
function getEntry ($str) {
	preg_match('/class="entry-content\"(.*?)>(?P<c>[\s\S]*?)<p><hr \/>/i', $str, $matches);
	if (!isset($matches ['c'])) {return;}
	if(preg_match('/<div class="wumii-hook">([\s\S]+)/im', $matches ['c'])) {
		$matches ['c'] = preg_replace('/<div class="wumii-hook">([\s\S]+)/im', '', $matches ['c'], 1);
	}
	return (str_replace("\r", '', str_replace("\n", '', str_replace('	', '', $matches ['c']))));
}
function getLoginDetail (&$un, &$pw) {
	global $login_info;
	if (count($login_info) == 1) { $num = 0; }
	 else { $num = rand(0, count($login_info)); }
	/*  Simple fix..
	 *  ret = rand(0, 0);
	 *   where ret is 1... wtf?!
	 */
	$userset = $login_info [$num];
	$un = $userset ['un'];
	$pw = $userset ['pw'];
}
function allopt ($ch) {
	global $ckfile;
	curl_setopt ($ch, CURLOPT_HEADER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
	curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_COOKIEJAR,  $ckfile);
	curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile);
}
function doLogin ($artID) {
	$url = "http://acgzone.us/";
	getLoginDetail ($username, $password);
	$postdata = "log=". $username ."&pwd=". $password ."&wp-submit=Log%20In&redirect_to=". $url . "/" . $artID;
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url . "wp-login.php");
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_REFERER, $url . "wp-login.php");
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
// Setup the time.
$cTime = time();

$Aid = @$_GET['id'];
if ($Aid == '' | !is_numeric($Aid)) {die('Need article ID!');}

if ($usesql) {
	$conn = mysqli_connect ($db_host, $db_user, $db_pass, $db_name, $db_port) or die($err_msg);
	// mysqli_select_db($db_name, $conn) or die($err_msg);
	mysqli_query ($conn, "set names " . $db_charset);
	$sqlmode = 'insert into';
	$result = mysqli_query ($conn, "Select * from " . $db_table . " where id='" . mysqli_real_escape_string($conn, $Aid) . "'");
	if($result === false){
		mysqli_close($conn);
		die($err_msg);
	}
	$row = mysqli_fetch_array ($result);
	if (isset($row[0])) {
		// 读取缓存
		if ( ($cTime - $row ['time']) < $cache_exp ) {
			// Not Expired.
			if ($showtime) {echo ('<em>Entry loaded from the cache saved at: ' . date($tformat, $row ['time']) . "</em></br>\n");}
			echo (base64_decode($row ['entry']));
			mysqli_close($conn);
			exit();
		} else {
			// Expired, update exist data.
			$sqlmode = 'update';
		}
	}
}

$ch = curl_init ("http://acgzone.us/" . $Aid);
allopt ($ch);
$output = getEntry(curl_exec ($ch));
curl_close($ch);
if (isEntryInvalid($output)) {
	$output = doLogin ($Aid);
}
if (isEntryInvalid($output)) {
	die ($err_msg);
}
if ($usesql) {
	$sql = $sqlmode . ' `' . $db_table . '` set ' . 
			'`id`=' . mysqli_real_escape_string($conn, $Aid) . 
			',`entry`=\'' . mysqli_real_escape_string($conn, base64_encode($output)) . '\''.
			',`time`=' . mysqli_real_escape_string($conn, time());
	mysqli_query($conn, $sql);
	mysqli_close($conn);
}
echo ($output);
?>