<?php

class DataModel {

    protected $postman = null;

    public function __construct() {
        $this->postman = Postman::init();
    }

    public function create_omr( $tableName, $field_list, $data_list, $fmt ) {

		$query	= "INSERT INTO ";
		$query .=   "`$tableName` ";
		$query .=	"( ";
        foreach($field_list as $field) {
            $query .=	"`$field`, ";
        }
        $query .=	" `created_date_time`, `status`) ";
		$query .= "VALUES ";
		$query .=	"( ";
        foreach($data_list as $data) {
            $query .=	" ?, ";
        }
        $query .=	" ?, ?) ";

		$created_date_time	= date('Y-m-d H:i:s');
		$status				= 'A';

		$params = array($fmt."ss");
        foreach($data_list as &$data) {
            $params[] = &$data;
        }
		$params[] = &$created_date_time;
		$params[] = &$status;
        // echo $this->postman->sql( $query, $params );
        return $this->postman->execute( $query, $params, true );
    }

    public function remove( $table_name, $idx, $website_idx = '' ) {

        $query	= "UPDATE ";
		$query .=   "`$table_name` ";
		$query .= "SET ";
        $query .=	"`status`=? ";
        $query .= "WHERE ";
        if ( $website_idx != '' ) { $query .= "`website_idx`=? AND "; }
        $query .=	"`idx`=? ";

        $status = 'D';

        $fmt = 's';
        if ( $website_idx != '' ) { $fmt .= 'i'; }
        $fmt .= 'i';

		$params = array( $fmt );
        $params[] = &$status;
        if ( $website_idx != '' ) { $params[] = &$website_idx; }
        $params[] = &$idx;

        $this->postman->execute( $query, $params );
    }
}
