<?php

    include 'Postman.php';
    include 'config.php';

    // create connection
    $p = Postman::getInstance( $host, $userid, $password, $database, false );

    $s_time = microtime(true);


    create_np2($p);
    // 30.09424996376
    // 30.585628986359
    // 29.742206096649


    /*
    for( $i = 0; $i < 5000; $i++) {
        create_np($p);
    }
    */
    // 37.757413864136

    $e_time = microtime(true);

    $time_diff = ($e_time - $s_time);

    echo $time_diff. '
';

    function create_np2( $p ) {

        $query  = "INSERT INTO `transaction_np` ";
        $query .= "( `trans_time`, `price`, `created_date_time`, `is_del` )";
		$query .= "VALUES ";
		$query .= "( ?, ?, ?, ? )";

        $list_params = array();



        for( $i = 0; $i < 5000; $i++) {
            $trans_time[$i]     = date('Y-m-d H:i:s');
            $price[$i]          = rand(14,51000);
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

        $idx = $p->execute_group( $query, $list_params, true );

/*



        echo $idx . ' ' . $price . '
';
*/
    }

    function create_np( $p ) {

        $query  = "INSERT INTO `transaction_np` ";
        $query .= "( `trans_time`, `price`, `created_date_time`, `is_del` )";
		$query .= "VALUES ";
		$query .= "( ?, ?, ?, ? )";

        $trans_time         = date('Y-m-d H:i:s');
        $price              = rand(14,51000);
		$created_date_time	= date('Y-m-d H:i:s');
		$is_det				= 0;

        $fmt = 'sdsi';

		$params = array($fmt);
        $params[] = &$trans_time;
        $params[] = &$price;
		$params[] = &$created_date_time;
		$params[] = &$is_det;

        $idx = $p->execute( $query, $params, true );
    }
