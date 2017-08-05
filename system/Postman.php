<?php

/***************************************************
 *
 * PHP Mysql Prepare Statement Wrapper Class
 * - supports prepare statment only
 * - for best practice, only uses group prepare statment
 *   through testing, group prepare statment increase average speed by 20%!!
 *
 * by phil goo kang
 *
 * @category   Mysql Database
 * @package    PHP
 * @subpackage Client
 * @version    1.0
 * @license    http://www.apache.org/licenses/     Apache License
 ***************************************************/

class Postman {

	/*
	 * any query taken over the set limit, we must log to optimization
	 */
	private $max_time_diff = 0.04;

    /*
	 * check whether we should make any logs
	 */
    private $should_log = false;

	/*
	 * postman singleton object
	 */
	private static $singleton;

	/*
	 * the acutal mysql connection
	 */
	private $mysqlConnection;

	/*
	 * keeps record of last query result summary
	 */
	public $last_query = array(
		'query' 	=> '', // query statment
		'run_time' 	=> '', // query run time
		'result'	=> '' // result from mysql after query is run
	);

	/**
	 * Called automatically, don't need to do anything
	 *
	 */
	public function __destruct() {

		// check if we have a live connection
		if ( $this->mysqlConnection != null ) {
			@mysqli_close($this->mysqlConnection);
		}
	}

	/**
	 * Returns a Mysql Connection Instance.
	 * - we do not want multiple mysql connections, so use a single static varbiable.
	 * - automatically kill on run complete
	 *
	 * @param string host mysql server address
	 * @param string mysql user id
	 * @param string password to log into mysql
	 * @param string mysql database to use
	 * @param boolean whether to make logs durring ussage
	 *
	 * @return object Postman class object instance
	 */
	public static function getInstance( $host = '', $userid = '', $password = '', $database = '', $log_setting = false ) {

		// check if the instance has been created before
		if ( Postman::$singleton == null) {

			// create new object
			Postman::$singleton = new Postman();

			// create connection
			Postman::$singleton->connect( $host, $userid , $password , $database , $log_setting );
		}

		return Postman::$singleton;
	}

	/**
	 * Returns a mysql connection
	 * - make in private because we want this to only be called one time
	 *
	 * @param string host mysql server address
	 * @param string mysql user id
	 * @param string password to log into mysql
	 * @param string mysql database to use
	 * @param boolean whether to make logs durring ussage
	 *
	 * @return object Mysql connection
	 */
	private function connect( $host, $userid , $password , $database , $log_setting ) {

		if ($this->mysqlConnection  == null ) {

			// get mysql instance
			$this->mysqlConnection = mysqli_init();

			// attempt connection
			if(mysqli_real_connect($this->mysqlConnection, $host, $userid, $password, $database)) {

                // set log setting
                $this->should_log = $log_setting;

                // change connection encoding
				// MUST set to multi byte because emoticon are becoming a trend,
				// to support them we need to extend the text encoding
				mysqli_set_charset( $this->mysqlConnection, 'utf8mb4' );
				mysqli_query($this->mysqlConnection, 'SET NAMES utf8mb4');
			}
		}

		return $this->mysqlConnection;
	}

	/**
	 * Binds the prepare statment variables to its question mark position
	 * - since the number of binding elements and N,
	 * - we use php call_to_func function to pass params as an prama array list
	 *
	 * @param object mysql prepared statment
	 * @param array holding the fmt and values to be send to mysql
	 *
	 * @return boolean whether call function was successful
	 */
	private function _db_bind_param(&$stmt, $params) {
		$f = array($stmt, "bind_param");
		return call_user_func_array($f, $params);
	}

	// -------------------------------------------------

	/**
	 * Prepares a mysql statment and binds the values
	 *
	 * @param string query string
	 * @param array list_params holds the binding values
	 * @param boolean whether to return the inserted idx value or return mysql results
	 *
	 * @return return results from query action
	 */
	public function execute($query, $list_params, $return_insert_idx = false) {

		// $smt
		$this->mysqlConnection->stmt_init();
		$stmt = $this->mysqlConnection->prepare($query);

		// return list
		$return_list = array();

		foreach($list_params as $params) {

			$this->_db_bind_param($stmt, $params);
			$result = $stmt->execute();

			if (!$result) {
				exit(json_encode( array( 'code' => '400', 'msg' => $this->mysqlConnection->error ) ));
			}

			$result = $stmt->get_result();

			if ( $return_insert_idx ) {
				array_push($return_list, $stmt->insert_id);
			} else {
				array_push($return_list, $result);
			}
		}

		// check for single action
		return (count($return_list) > 1) ? $return_list : $return_list[0];
	}

	public function executeList($query, $params) {

		$result = $this->execute($query, array($params));

		$return_data = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$object = new stdClass();
			foreach ($row as $key => $value) {
				$object->$key = $value;
			}
			array_push($return_data, $object);
		}

		return $return_data;
	}

	public function executeObject($query, $params) {
		$list = $this->executeList($query, $params);
		return (isset($list[0])) ? $list[0] : new stdClass();
	}
}
