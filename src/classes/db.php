<?php
class WTWebpage {

    private $dbh;
    public $vars;

    function __construct() {
        $this->vars = $_GET;
        $this->dbh = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
        if ($this->dbh->connect_error) {
            die("Connection failed: " . $this->dbh->connect_error);
        }
    }

    public function getDbh() {
        return $this->dbh;
    }

}
?>