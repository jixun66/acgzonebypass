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