<?php

	define ( "READFILE", true ); // https://habrahabr.ru/post/143035/

	require("api.php");
	require("auth.php");
	require("chat.php");
	require("common.php");
	require("validation.php");

	if ($_REQUEST['action'] === 'TEST') {
	
		// sendJSONData(getLastOfferForRequest(11, 574));
		// $last_offer = getLastOfferForRequest(11, 574);

		// debug($last_offer['period']);
		// debug($last_offer['period_units']);

		session_start();
		debug(
			'TEST current '.$_SESSION['user_type'].' session: '.
			'ses_id: '.session_id().' '.
			$_SESSION['city_id'].'/'.
			$_SESSION['category_id'].'/'.
			$_SESSION['user_email'].'/'.
			$_SESSION['user_phone'].'/'.
			$_SESSION['user_type'].'/'.
			$_SESSION['authorized'].'/'.
			$_SESSION['user_id']
		);
	};

?>