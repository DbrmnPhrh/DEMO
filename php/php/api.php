<?php

	if (! defined ( "READFILE" )) {
		exit ( "Error, wrong path to file.<br><a href='/'>Go to main</a>." );
	};

// ========= API для Клиентов


	function getRequestTypes($category_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT request_type_id, request_type_name FROM request_types WHERE category_id = ?")) ||
			(! $stmt->bind_param('i', $category_id)))
			{ writeLog("getRequestTypes() error " . mysqli_error($connect)); die;}

		$stmt->execute();
		$result = $stmt->get_result();
		$result_array = array();
		$request_types_array = array();

		while ($result_array = $result->fetch_array()) {
			array_push($request_types_array, array($result_array[0], $result_array[1]));
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error);  die;};

		return $request_types_array;
	};


	function getSubcategories($category_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT subcategory_id, subcategory_name FROM subcategories WHERE category_id = ?")) ||
			(! $stmt->bind_param('i', $category_id)))
			{ writeLog("getSubcategories() error " . mysqli_error($connect)); die;}

		$stmt->execute();
		$result = $stmt->get_result();
		$result_array = array();
		$subcategories_array = array();

		while ($result_array = $result->fetch_array()) {
			array_push($subcategories_array, array($result_array[0], $result_array[1]));
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error);  die;};

		return $subcategories_array;
	};


	function addRequest($request_type_id, $subcategory_id, $title_text, $request_text) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$customer_id = $_SESSION['user_id'];

		if ( ! $customer_id ||
			(! $stmt->prepare("INSERT INTO requests (category_id, request_type_id, subcategory_id, customer_id, title_text, request_text)" . "VALUES (?, ?, ?, ?, ?, ?);")) ||
			(! $stmt->bind_param('iiiiss', $_SESSION['category_id'], $request_type_id, $subcategory_id, $_SESSION['user_id'], $title_text, $request_text)))
			{ writeLog('addRequest() error (' . $stmt->errno . ') ' . $stmt->error .
					   ' category_id: ' . $_SESSION['category_id'] .
					   ' request_type_id: ' . $request_type_id .
					   ' subcategory_id: ' . $subcategory_id .
					   ' customer_id: ' . $_SESSION['user_id'] .
					   ' title_text: ' . $title_text .
					   ' request_text: ' . $request_text);
					   die;
			}

		if ($stmt->execute()) {

			sendUserMessage('success', 'Ваш запрос успешно отправлен!');

		} else {

			sendUserMessage('danger', USRMSG_DB_ERROR);
			writeLog('addRequest() DB access error (' . $stmt->errno . ') ' . $stmt->error);
			die;
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die;};
	};


	function getRequestsHistory($customer_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT
							   requests.request_created_at,
							   categories.category_name,
							   request_types.request_type_name,
							   subcategories.subcategory_name,
							   requests.title_text,
							   requests.request_text,
							   requests.request_id,
							   requests.is_closed
							   FROM requests, categories, request_types, subcategories
							   WHERE categories.category_id = requests.category_id
							   AND request_types.request_type_id = requests.request_type_id
							   AND subcategories.subcategory_id = requests.subcategory_id
							   AND customer_id = ?")) ||
			(! $stmt->bind_param('i', $customer_id)))
			{ writeLog('getRequestsHistory() SELECT error (' . $stmt->errno . ') ' . $stmt->error . 'customer_id: ' . $customer_id); die;};

		$stmt->execute();
		$result = $stmt->get_result();
		$table_data = array();

		while($result_array = $result->fetch_array()) {

			array_push($table_data, array($result_array['request_created_at'],
										  $result_array['category_name'],
										  $result_array['request_type_name'],
										  $result_array['subcategory_name'],
										  $result_array['title_text'],
										  $result_array['request_text'],
										  $result_array['request_id'],
										  $result_array['is_closed'])
			);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die;};

		return array("data" => $table_data);
	};


	function getOffersForRequest($request_id, $offer_last_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$request_id = checkRequestIdAccordingToCustomer($request_id);

		if ((! $stmt->prepare("SELECT * FROM offers WHERE request_id = ? AND offer_id > ?;")) ||
			(! $stmt->bind_param('ii', $request_id, $offer_last_id)))
			{ writeLog('getOffersForRequest() SELECT error (' . $stmt->errno . ') ' . $stmt->error); die;};

		$stmt->execute();
		$result = $stmt->get_result();
		$total_rows = $result->num_rows;
		$table_data = array();

		if ($total_rows) {

			for ($i = 0 ; $i < $total_rows ; ++$i) {
				$offer = $result->fetch_assoc();
				$contractor = getContractorDataById($offer['contractor_id']);
				array_push($table_data, array($offer['offer_created_at'],$offer['offer_id'],$contractor['form_of_incorp'].' '.$contractor['contractor_name'],$offer['price_from'], $offer['price_to'], $offer['period'], $offer['period_units'], $offer['offer_text']));
			};
			return array("data" => $table_data, "offer_last_id" => $offer['offer_id']);

		} else {

			return array("data" => $table_data, "offer_last_id" => $offer_last_id);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die;};
	};


// ========= API для Контрагентов

	function getRequests($city_id, $category_id, $request_last_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$request_days_limit = REQUEST_DAYS_LIMIT;

		if ((! $stmt->prepare("SELECT
							   requests.request_created_at,
							   requests.request_id,
							   request_types.request_type_name,
							   subcategories.subcategory_name,
							   requests.title_text,
							   requests.request_text,
							   customers.email,
							   customers.phone,
							   requests.is_closed
							   FROM
							   requests,
							   request_types,
							   subcategories,
							   customers
							   WHERE request_types.request_type_id = requests.request_type_id
							   AND subcategories.subcategory_id = requests.subcategory_id
							   AND customers.customer_id = requests.customer_id
							   AND request_created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
							   AND customers.city_id = ?
							   AND requests.category_id = ?
							   AND requests.request_id > ?")) ||
			(! $stmt->bind_param('iiii', $request_days_limit, $city_id, $category_id, $request_last_id)))
		    { writeLog('getRequests($request_last_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error); die;};

		$stmt->execute();
		$result = $stmt->get_result();
		$total_rows = $result->num_rows;
		$table_data = array();
		$contractor_id = $_SESSION['user_id'];

		if ($total_rows) {

			while($result_array = $result->fetch_array()) {

				$last_offer = getLastOfferForRequest($contractor_id, $result_array['request_id']); //получаем последнюю предложенную текущим Контрагентом стоимость

				$result_array['email'] = $result_array['email'] ? preg_replace('%@.*%', '@...', $result_array['email']) : $result_array['email'];
				$result_array['phone'] = $result_array['phone'] ? '8' . (preg_replace('%\d{3}$%', '...', $result_array['phone'])) : $result_array['phone'];

				array_push($table_data, array($result_array['request_created_at'],
											  $result_array['request_id'],
											  $result_array['request_type_name'],
											  $result_array['subcategory_name'],
											  $result_array['title_text'],
											  $result_array['request_text'],
											  $result_array['email'],
											  $result_array['phone'],
											  $last_offer['price_from'],
											  $last_offer['price_to'],
											  $last_offer['period'],
											  $last_offer['period_units'],
											  $result_array['is_closed'])
				);
			};
			echo json_encode(array("data" => $table_data,
								   "request_last_id" => $table_data[count($table_data)-1][1]));

		} else {

			echo json_encode(array("data" => $table_data,
								   "request_last_id" => $request_last_id));
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die;};
	};

	function getLastOfferForRequest($contractor_id, $request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$query = "SELECT price_from, price_to, period, period_units
				  FROM offers
				  WHERE request_id = '{$request_id}'
				  AND contractor_id = '{$contractor_id}'
				  ORDER BY offer_id
				  DESC LIMIT 1;";
		$result = mysqli_query($connect, $query) or writeLog("getLastOfferForRequest() error " . mysqli_error($connect), '') and die;
		$result_array = mysqli_fetch_assoc($result);
		return $result_array;
	};

	function addOffer($price_from, $price_to, $period, $period_units, $offer_text, $request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$last_request_id = getLastRequestId();
		$contractor_id = $_SESSION['user_id'];
		debug($contractor_id);

		// Проверка статуса запроса (открыт или закрыт)
		$is_request_closed = checkRequestIsClosed($request_id);
		if ($is_request_closed) {
			sendUserMessage('info', 'Запрос закрыт. Подача предложений завершена!');
			return;
		};

		// Проверяем фактическую длину многострочного $offer_text с учётом переносов строк
		if (mb_strlen($offer_text) <= 600) {

		// Затем прогоняем через mysqli_real_escape_string, после этого длина $request_text
		// увеличивается, поэтому в БД varchar(700) вместо varchar(600)
			$offer_text = mysqli_real_escape_string($connect, $offer_text);

		} else {

			writeLog('addOffer() error', 'WRONG offer text length: '.mb_strlen($offer_text));
			die;
		};

		if (! isset($price_from) ||
			! isset($price_to) ||
			! isset($period) ||
			! isset($period_units) ||
			! isset($request_id) ||
			$request_id == 0 ||
			$request_id > $last_request_id) {

			writeLog('addOffer() error',
					 'price_from: '.$price_from.
					 '/price_to: '.$price_to.
					 '/period: '.$period.
					 '/period_units: '.$period_units.
					 '/offer_text_length: '.mb_strlen($offer_text).
					 '/request_id: '.$request_id);
			die;

		} else if ($price_from == 0 &&
				   $price_to == 0 &&
				   $period == 0 &&
				   $period_units == 0 &&
				   $offer_text == '') {

			writeLog('addOffer() error', 'empty offer!');
			die;
		};

		if ($stmt->prepare("INSERT INTO offers (price_from,
												price_to,
												period,
												period_units,
												offer_text,
												request_id,
												contractor_id)" . "VALUES (?, ?, ?, ?, ?, ?, ?);") &&
			$stmt->bind_param('iiiisii', $price_from, $price_to, $period, $period_units, $offer_text, $request_id, $contractor_id) &&
			$stmt->execute()) {

			sendUserMessage('success', 'Ваше предложение успешно добавлено!');

		} else {
			sendUserMessage('danger', USRMSG_DB_ERROR);
			writeLog('addOffer() INSERT error (' . $stmt->errno . ') ' . $stmt->error .
					 ' $price_from: ' . $price_from .
					 ' $price_to: ' . $price_to .
					 ' $period: ' . $period .
					 ' $period_units: ' . $period_units .
					 ' $offer_text: ' . $offer_text .
					 ' $request_id: ' . $request_id .
					 ' $contractor_id: ' . $contractor_id);
			die;
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die;};
	};

	function getLastRequestId() {

		require_once("connect.php");

		$connect = createConnection();
		$query = "SELECT request_id FROM requests ORDER BY request_id DESC LIMIT 1;";
		$result = mysqli_query($connect, $query) or writeLog("getLastRequestId() error " . mysqli_error($connect), '') and die;
		$result = mysqli_fetch_array($result);
		return $result['request_id'];
	};

?>