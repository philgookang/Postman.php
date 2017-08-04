<?php

include 'config.php';
include 'autoload.php';

class TestCase {

    protected $postman;

    public function __construct($host, $userid, $password, $database) {
        $this->postman = Postman::getInstance( $host, $userid, $password, $database, false );
    }

    public function createByLoop() {

        $query  = "INSERT INTO `transaction_np` ";
        $query .= "( `trans_time`, `price`, `created_date_time`, `is_del` ) ";
		$query .= "VALUES ";
		$query .= "( ?, ?, ?, ? )";

        $list_params = array();

        for( $i = 0; $i < 5; $i++) {
            $trans_time[$i]         = date('Y-m-d H:i:s');
            $price[$i]              = rand(14,51000);
    		$created_date_time[$i]	= date('Y-m-d H:i:s');
    		$is_det[$i]				= 0;

            $fmt = 'sdsi';

    		$params = array($fmt);
            $params[] = &$trans_time[$i];
            $params[] = &$price[$i];
    		$params[] = &$created_date_time[$i];
    		$params[] = &$is_det[$i];

            array_push($list_params, $params);
        }

        $idx = $this->postman->execute($query, $list_params, true);

        var_dump($idx);
    }

    public function createByPush() {

        $query  = "INSERT INTO `transaction_np` ";
        $query .= "( `trans_time`, `price`, `created_date_time`, `is_del` ) ";
		$query .= "VALUES ";
		$query .= "( ?, ?, ?, ? )";

        $fmt = 'sdsi';

        $list_params = array();

        ///////////////////////////////////////////////////

        $trans_time     = '2017-09-12 11:22:33';
        $price          = 50;
        $created_date_  = '2017-09-13 11:22:33';
        $is_det         = 0;

		$params = array($fmt);
        $params[] = &$trans_time;
        $params[] = &$price;
		$params[] = &$created_date_;
		$params[] = &$is_det;

        array_push($list_params, $params);

        ///////////////////////////////////////////////////

        $trans_time     = '2017-09-12 11:22:33';
        $price          = 50;
        $created_date_  = '2017-09-13 11:22:33';
        $is_det         = 0;

        array_push($list_params, array($fmt, &$trans_time, &$price, &$created_date_, &$is_det));

        ///////////////////////////////////////////////////

        $idx = $this->postman->execute($query, $list_params, true);

        var_dump($idx);
    }

    public function getList() {

        $query  = "SELECT ";
        $query .=   "* ";
        $query .= "FROM ";
        $query .=   "`transaction_np` ";
        $query .= "WHERE ";
        $query .=   "`is_del`=? ";
        $query .= "ORDER BY idx desc ";
        $query .=   "limit ? offset ? ";

        $fmt = 'iii';

        $status = 0;
        $limit  = 2;
        $offset = 0;

        $list = array($fmt, &$status, &$limit, &$offset);

        $idx = $this->postman->executeList($query, $list);

        var_dump($idx);
    }

    public function getTotal() {

        $query  = "SELECT ";
        $query .=   "COUNT(*) as cnt ";
        $query .= "FROM ";
        $query .=   "`transaction_np` ";
        $query .= "WHERE ";
        $query .=   "`is_del`=? ";

        $fmt = 'i';
        $status = 0;
        $list = array($fmt, &$status);

        $idx = $this->postman->executeList($query, $list);

        var_dump($idx);
    }

    public function get() {

        $query  = "SELECT ";
        $query .=   "* ";
        $query .= "FROM ";
        $query .=   "`transaction_np` ";
        $query .= "WHERE ";
        $query .=   "`idx`=? ";

        $fmt = 'i';
        $idx = 1;
        $list = array($fmt, &$idx);

        $idx = $this->postman->executeObject($query, $list);

        var_dump($idx);
    }
}

$tc = new TestCase($host, $userid, $password, $database);
$tc->createByLoop();
$tc->createByPush();
$tc->getList();
$tc->getTotal();
$tc->get();
