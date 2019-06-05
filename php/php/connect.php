<?php

	if (! defined ( "READFILE" )) {
		exit ( "Error, wrong way to file.<br><a href='/'>Go to main</a>." );
	};

	function createConnection() {

		$connect = new mysqli("localhost","akbashev.a","hwnd3264zone51def",'qix_main');
		mysqli_set_charset($connect, 'utf8');

		if ($connect->connect_error) {

			writeLog('DB connect error (' . $connect->connect_errno . ') ' . $connect->connect_error);

		} else {

			return $connect;

		};

	};

?>