<?php
class WTWebpage {

	private $dbh;
	public $vars;

	function __construct() {
		$this->vars = $_GET;
		$this->dbh = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	}
	public function getDbh() {
		return $this->dbh;
	}

}
?>