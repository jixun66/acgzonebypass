<?
/*
	Install the SQL Structure to database.
*/
$db_host    = "localhost";			// ���ݿ��ַ
$db_port    = "3306";				// ���ݿ�˿ڣ�Ĭ�� 3306
$db_user    = "root";				// �û���
$db_pass    = "";					// ����
$db_name    = "dbname";				// ���ݿ���
$db_table   = "acgzone_stat"; 		// ���ݱ���
$db_charset = 'UTF-8';				// ���ݿ����



$installSQL = 'CREATE TABLE IF NOT EXISTS `az_acgzone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);';
$conn   = mysql_connect($db_host . ':' . $db_port, $db_user, $db_pass, true) or die('Can not connect to db server');
mysql_select_db($db_name, $conn) or die('db not exist.');
mysql_query("set names " . $db_charset, $conn);
$result = mysql_query($installSQL, $conn);
$err = mysql_error ($conn);
mysql_close($conn);
if($result){
	die('Job done.');
} else {
	die("Install failed!<br />\n" . $err);
}
?>