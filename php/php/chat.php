<?php

	if (! defined ( "READFILE" )) {
		exit ( "Error, wrong path to file.<br><a href='/'>Go to main</a>." );
	};


	// function processMessage($message_data) {

	// 	debug($message_data['type']);
	// 	debug($message_data['message']);
	// 	debug('sess_id: '.session_id());
	// 	debug('PHPSESSID: '.$_COOKIE['PHPSESSID']);

	// 	// debug(
	// 	// 	'current '.$_SESSION['user_type'].' session: '.
	// 	// 	$_SESSION['city_id'].'/'.
	// 	// 	$_SESSION['category_id'].'/'.
	// 	// 	$_SESSION['user_email'].'/'.
	// 	// 	$_SESSION['user_phone'].'/'.
	// 	// 	$_SESSION['user_type'].'/'.
	// 	// 	$_SESSION['authorized'].'/'.
	// 	// 	$_SESSION['user_id']
	// 	// );


	// };

	function addChatMessage($message, $request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$request_id = checkRequestIdAccordanceToCustomer($request_id);

		// Проверяем фактическую длину многострочного $message с учётом переносов строк
		if (mb_strlen($message) >= 1 && mb_strlen($message) <= 200) {

		// Затем прогоняем через mysqli_real_escape_string, после этого длина $message
		// увеличивается, поэтому в БД varchar(300) вместо varchar(200)
			$message = mysqli_real_escape_string($connect, $message);

		} else {

			writeLog('addChatMessageFromCustomer() error WRONG chat message length: '.mb_strlen($message));
			return false;
		};

		if ($_SESSION['user_email']) {

			$from_customer_id = $_SESSION['user_id'];

			if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_customer, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?, ?);")) ||
				(! $stmt->bind_param('sisis', $message, $request_id, $from_customer_id, $from_contractor_id, $to_customer_id)))
				{ writeLog('addChatMessageFromCustomer error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_customer_id: ' . $from_customer_id . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer_id); }

		} else if ($_SESSION['user_email']) {

			if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_customer, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?, ?);")) ||
				(! $stmt->bind_param('sisis', $message, $request_id, $_SESSION['user_email'], $from_contractor_id, $to_customer_id)))
				{ writeLog('addChatMessageFromCustomer error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_customer: ' . $from_customer . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer_id); }

		} else if ($_SESSION['user_phone']) {

			if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_customer, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?, ?);")) ||
				(! $stmt->bind_param('sisis', $message, $request_id, $_SESSION['user_phone'], $from_contractor_id, $to_customer_id)))
				{ writeLog('addChatMessageFromCustomer error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_customer: ' . $from_customer . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer_id); }

		} else {

			writeLog('addChatMessageFromCustomer() error unknown customer id');
			return false;
		};

		if (! $stmt->execute()) {

			writeLog('addChatMessageFromCustomer() DB error (' . $stmt->errno . ') ' . $stmt->error);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

	};

	function addChatMessageFromCustomer($message) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$request_id = getLastRequestIdForCurrentCustomer();

		// Проверяем фактическую длину многострочного $message с учётом переносов строк
		if (mb_strlen($message) >= 1 && mb_strlen($message) <= 200) {

		// Затем прогоняем через mysqli_real_escape_string, после этого длина $message
		// увеличивается, поэтому в БД varchar(300) вместо varchar(200)
			$message = mysqli_real_escape_string($connect, $message);

		} else {

			writeLog('addChatMessageFromCustomer() error WRONG chat message length: '.mb_strlen($message));
			return false;
		};

		if ($_SESSION['user_email']) {

			$from_customer_id = $_SESSION['user_id'];

			if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_customer, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?, ?);")) ||
				(! $stmt->bind_param('sisis', $message, $request_id, $from_customer_id, $from_contractor_id, $to_customer_id)))
				{ writeLog('addChatMessageFromCustomer error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_customer_id: ' . $from_customer_id . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer_id); }

		} else if ($_SESSION['user_email']) {

			if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_customer, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?, ?);")) ||
				(! $stmt->bind_param('sisis', $message, $request_id, $_SESSION['user_email'], $from_contractor_id, $to_customer_id)))
				{ writeLog('addChatMessageFromCustomer error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_customer: ' . $from_customer . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer_id); }

		} else if ($_SESSION['user_phone']) {

			if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_customer, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?, ?);")) ||
				(! $stmt->bind_param('sisis', $message, $request_id, $_SESSION['user_phone'], $from_contractor_id, $to_customer_id)))
				{ writeLog('addChatMessageFromCustomer error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_customer: ' . $from_customer . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer_id); }

		} else {

			writeLog('addChatMessageFromCustomer() error unknown customer id');
			return false;
		};

		if (! $stmt->execute()) {

			writeLog('addChatMessageFromCustomer() DB error (' . $stmt->errno . ') ' . $stmt->error);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

	};

	function addChatMessageFromContractor($message, $request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$from_contractor_id = $_SESSION['user_id'];
		$to_customer = getCustomerByRequestId($request_id);

		if ((! $stmt->prepare("INSERT INTO chat (message_text, request_id, from_contractor_id, to_customer)" . "VALUES (?, ?, ?, ?);")) ||
			(! $stmt->bind_param('siis', $message, $request_id, $from_contractor_id, $to_customer)))
			{ writeLog('addChatMessageFromContractor error (' . $stmt->errno . ') ' . $stmt->error . ' message_text_length: ' . mb_strlen($message) . ' request_id: ' . $request_id . ' from_contractor_id: ' . $from_contractor_id . ' to_customer: ' . $to_customer); }

		if (! $stmt->execute()) {

			writeLog('addChatMessageFromContractor() DB error (' . $stmt->errno . ') ' . $stmt->error);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };
	};

?>