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
	private var $max_time_diff = 0.04;

    // should log
    private var $should_log = false;

    // show dev errors
    private var $show_dev_error = false;

	// postman singleton
	private static $singleton;

	// mysql connection, only access by this class
	private var $mysqlConnection;

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
	 *
	 */
	private function db_bind_param(&$stmt, $params) {
		$f = array($stmt, "bind_param");
		return call_user_func_array($f, $params);
	}


    public function setDevError( $show_dev ) {
        $this->show_dev_error = $show_dev;
    }

	// -------------------------------------------------

	function sql($query, $params) {

		for ($i = 1; $i <= (count($params) - 1); $i++) {
			$query = $this->str_replace_first('?', '\''. $params[$i] . '\'', $query);
		}

		return $query;
	}

	function str_replace_first($from, $to, $subject) {
		$from = '/'.preg_quote($from, '/').'/';
		return preg_replace($from, $to, $subject, 1);
	}

	function execute($query, $params, $return_insert_idx = false) {

		$star_time	= 0;
		$end_time	= 0;
		$query3		= '';

		if ( $this->should_log ) {
			$star_time = microtime(true);
		}

		if ( $this->show_dev_error ) {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);

			$query3 = $query;

			for ($i = 1; $i <= (count($params) - 1); $i++) {
				$query3 = $this->str_replace_first('?', '\''. $params[$i] . '\'', $query3);
			}
		}

		$stmt = $this->mysqlConnection->stmt_init();
		$stmt = $this->mysqlConnection->prepare($query);

		$this->db_bind_param($stmt, $params);
		$result = $stmt->execute();

		if (!$result) {
			exit(json_encode( array( 'code' => '400', 'msg' => $this->mysqlConnection->error ) ));
		}

		$result = $stmt->get_result();


		if ( $this->should_log ) {

			// set end time
			$end_time = microtime(true);

			// get the time difff
			$time_diff = ($end_time - $star_time);

			// if time if is larger than
			if ( ($this->max_time_diff < $time_diff) ) {
				// logging( $query, $params, ($time_diff/1000) );
			}
		}

		if ( $this->show_dev_error ) {
			$aaa = ($end_time - $star_time);
			echo number_format($aaa, 3)  . ' explain ' . $query3 . '; ' . '<Br />';
		}

		if ( $return_insert_idx ) {
			return $stmt->insert_id;
		} else {
			return $result;
		}
	}

	function execute_group($query, $list_params, $return_insert_idx = false) {

		$star_time	= 0;
		$end_time	= 0;
		$query3		= '';

		if ( $this->should_log ) {
			$star_time = microtime(true);
		}

		if ( $this->show_dev_error ) {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);

			$query3 = $query;

			for ($i = 1; $i <= (count($params) - 1); $i++) {
				$query3 = $this->str_replace_first('?', '\''. $params[$i] . '\'', $query3);
			}
		}

		$stmt = $this->mysqlConnection->stmt_init();
		$stmt = $this->mysqlConnection->prepare($query);

		$return_list = array();

		foreach($list_params as $params) {
			$this->db_bind_param($stmt, $params);
			$result = $stmt->execute();

			if (!$result) {
				exit(json_encode( array( 'code' => '400', 'msg' => $this->mysqlConnection->error ) ));
			}

			$result = $stmt->get_result();


			if ( $this->should_log ) {

				// set end time
				$end_time = microtime(true);

				// get the time difff
				$time_diff = ($end_time - $star_time);

				// if time if is larger than
				if ( ($this->max_time_diff < $time_diff) ) {
					// logging( $query, $params, ($time_diff/1000) );
				}
			}

			if ( $this->show_dev_error ) {
				$aaa = ($end_time - $star_time);
				echo number_format($aaa, 3)  . ' explain ' . $query3 . '; ' . '<Br />';
			}

			if ( $return_insert_idx ) {
				array_push($return_list, $stmt->insert_id);
			} else {
				array_push($return_list, $result);
			}
		}

		return $return_list;
	}

	function returnDataList($query, $params) {

		$result = $this->execute($query, $params);

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
