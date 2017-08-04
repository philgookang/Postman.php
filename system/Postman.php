<?php

/***************************************************
 *
 * PHP Mysql Wrapper Class
 * - supports prepare statment only
 * - for best practice, only uses group prepare statment
 *   through testing, group prepare statment increase average speed by 20%!!
 *
 * by phil goo kang
 *
 ***************************************************/

class Postman {

	// any query taken over the set limit, we must log to optimization
	private $max_time_diff = 0.04;

    // should log
    private $should_log = false;

    // show dev errors
    private $show_dev_error = false;

	// postman singleton
	private static $singleton;

	// mysql connection, only access by this class
	private $mysqlConnection;

	// keeps record of last query summary
	public $last_query = array(
		'query' 	=> '', // query statment
		'run_time' 	=> '', // query run time
		'result'	=> '' // result from mysql after query is run
	);

	/**
	 * Called automatically, don't need to do anything
	 */
	function __destruct() {

		// check if we have a live connection
		if ( $this->mysqlConnection != null ) {
			@mysqli_close($this->mysqlConnection);
		}
	}

	/**
	 * Returns a Mysql Connection Instance.
	 * - we do not want multiple mysql connections, so use a single static varbiable.
	 * - automatically kill on run complete
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
	 */
	private function _db_bind_param(&$stmt, $params) {
		$f = array($stmt, "bind_param");
		return call_user_func_array($f, $params);
	}

	// -------------------------------------------------

	public function execute($query, $list_params, $return_insert_idx = false) {

		// $smt
		$stmt = $this->mysqlConnection->stmt_init();
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

	function executeList($query, $params) {

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

	function returnDataObject($query, $params) {
		$list = $this->returnDataList($query, $params);
		return (isset($list[0])) ? $list[0] : new stdClass();
	}
}
