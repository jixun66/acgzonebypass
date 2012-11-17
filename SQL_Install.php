<?
/*
	Install the SQL Structure to database.
*/
$db_host    = "localhost";			// 数据库地址
$db_port    = "3306";				// 数据库端口，默认 3306
$db_user    = "root";				// 用户名
$db_pass    = "";					// 密码
$db_name    = "dbname";				// 数据库名
$db_table   = "acgzone_stat"; 		// 数据表名
$db_charset = 'UTF-8';				// 数据库编码



$installSQL = 'CREATE TABLE IF NOT EXISTS `' . $db_table . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);';
$conn = mysqli_connect ($db_host, $db_user, $db_pass, $db_name, $db_port) or die('Can not connect to db server');
mysqli_query($conn, "set names " . $db_charset);
$result = mysqli_query ($conn, $installSQL);
$err = mysqli_error ($conn);
mysqli_close($conn);
if($result){
	die('Job done.');
} else {
	die("Install failed!<br />\n" . $err);
}
?>