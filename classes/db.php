<?php
class WTWebpage {

	private $dbh;
	public $vars;

	function __construct() {
		/*$dbConfig = array(
		    'host'        => CONFIG_DB_HOST,
		    'username'    => CONFIG_DB_USER,
		    'password'    => CONFIG_DB_PASS,
		    'dbname'    => CONFIG_DB_NAME
		);*/
		$this->vars = $_GET;
		$this->dbh = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	}
	public function getDbh() {
		return $this->dbh;
	}

}
?>