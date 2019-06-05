<?php

	if (! defined ( "READFILE" )) {
		exit ( "Error, wrong path to file.<br><a href='/'>Go to main</a>." );
	};

	//Логирование
	const LOGFILE = '../log/journal.log';
	const LOGMSG_WRONG_REG = ' Wrong registration data!';
	const LOGMSG_WRONG_REQUEST = ' Wrong customer request!';
	const LOGMSG_WRONG_OFFER = ' Wrong contractor offer data!';

	//Отладка
	const DEBUG_MODE = 'ON';
	const DEBUGFILE = '../log/debug.log';

	//Пользовательские сообщения
	const USRMSG_NOT_AUTH = 'Пользователь не авторизован!';
	const USRMSG_OFFER_SUCCESS = 'Ваше предложение успешно отправлено!';
	const USRMSG_DB_ERROR = 'Пожалуйста повторите попытку позднее';

	//URL'ы
	const URL_INDEX_PAGE = 'index.html';
	const URL_CONTRACTOR_PAGE = 'contractor.html';

	//Настройки авторизации и регистрации
	const CONTRACTOR_REG_DISABLE = false;	//Отключение возможности самостоятельной регистрации для контрагентов.
											//При отключении регистрация контрагента осуществляется администратором вручную
											//путём внесения сведений о контрагенте напрямую в БД, либо через админку

	//Сетевые настройки
	const NET_PROTOCOL = 'tcp://';
	const NET_HOST = 'test1.ru';
	const NET_SOCKET_PORT = 8889;

	//Общие настройки
	const REQUEST_MAX_ID = 2147483647;		//DEF: 2147483647 Максимальный id запроса
	const REQUEST_DAYS_LIMIT = 200;			//DEF: 7 Отображение запросов у contractors за указанное количество дней
	const REQUEST_TITLE_MIN_LEN = 5;		//Минимальная длина заголовка запроса (также должно быть на клиенте)
	const REQUEST_TITLE_MAX_LEN = 50;		//Максимальная длина заголовка запроса (также должно быть на клиенте)
	const REQUEST_TEXT_MIN_LEN = 10;		//Минимальная длина текста запроса (также должно быть на клиенте)
	const REQUEST_TEXT_MAX_LEN = 600;		//Максимальная длина текста запроса (также должно быть на клиенте)
	const OFFER_MAX_LAST_ID = 1000;			//Максимальное количество последних предложений по текущему запросу
	const OFFER_TEXT_MAX_LEN = 600;			//Максимальная длина текста предложения (также должно быть на клиенте)
	const OFFER_MAX_COST_FROM = 9999999;	//Максимальное значение стоимости "от" (также должно быть на клиенте)
	const OFFER_MAX_COST_TO = 9999999;		//Максимальное значение стоимости "до" (также должно быть на клиенте)
	const OFFER_MAX_PERIOD = 99;			//Максимальное значение периода (также должно быть на клиенте)
	const CHAT_MSG_MIN_LEN = 10;			//Минимальная длина сообщения чата
	const CHAT_MSG_MAX_LEN = 600;			//Максимальная длина сообщения чата


// ========= Функции для отладки

	function debug($bug) {

		file_put_contents(DEBUGFILE, $bug . PHP_EOL, FILE_APPEND);

	};

	function sendServerError($error_code, $log_msg, $comment) {

		if (DEBUG_MODE === 'ON') { header('X-PHP-Response-Code: ' . $error_code, true, $error_code); };
		file_put_contents(LOGFILE, date("d.m.Y H:i:s") . ' ' . $log_msg . '(' . $comment . ')' . PHP_EOL, FILE_APPEND);

	};

	function writeLog($log_msg) {

		file_put_contents(LOGFILE, date("d.m.Y H:i:s") . ' ' . $log_msg . PHP_EOL, FILE_APPEND);

	};


// ========= Общие функции: текущий пользователь

	function getCurrentUserType() {
		session_start();
		if ($_SESSION['user_type']) { return $_SESSION['user_type']; }
		else { return 'guest'; };
	};

	function sendCurrentUser() {
		session_start();
		sendJSONData(array(
						"user_email" => $_SESSION['user_email'],
						"user_type"  => getCurrentUserType()
					));
		return;
	};

	function sendUserMessage($msg_type, $msg) {

		sendJSONData(array(
						"msg_type" => $msg_type,
						"msg" => $msg
					));
	};

	function sendJSONData($data) {

		echo json_encode($data);
	};

// ========= Сокеты

	function sendSocketParameters() {
		sendJSONData(array(
						"host" => NET_HOST,
						"socket_port" => NET_SOCKET_PORT
					));
		return;
	};


// ========= Общие функции: города

	function getCities() {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if (! $stmt->prepare("SELECT city_id, city_name FROM cities ORDER BY city_id"))
			{ writeLog("getCities() error " . mysqli_error($connect)); }

		$stmt->execute();
		$result = $stmt->get_result();
		$result_array = array();
		$cities_array = array();

		while ($result_array = $result->fetch_array()) {
			array_push($cities_array, array($result_array[0], $result_array[1]));
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $cities_array;
	};

	function getCitiesQuantity() {

		require_once("connect.php");

		$connect = createConnection();
		$query = "SELECT COUNT(1) FROM cities";
		$result = mysqli_query($connect, $query) or writeLog("cities count error " . mysqli_error($connect));
		$cities_quantity = mysqli_fetch_array($result);

		return $cities_quantity[0];
	};

	function getUserCityIdByEmail($email, $user_type) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$table = $user_type . 's';

		if ((! $stmt->prepare("SELECT city_id FROM $table WHERE email = ?")) ||
			(! $stmt->bind_param('s', $email)))
			{ writeLog('getUserCityIdByEmail() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['city_id'];
	};

	function getUserCityIdByPhone($phone) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT city_id FROM customers WHERE phone = ?")) ||
			(! $stmt->bind_param('s', $phone)))
			{ writeLog('getUserCityIdByPhone() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' phone: ' . $phone); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['city_id'];
	};

	function getCityNameById($city_id) { //не используется

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT city_name FROM cities WHERE city_id = ?")) ||
			(! $stmt->bind_param('i', $city_id)))
			{ writeLog('getCityNameById() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['city_name'];
	};


// ========= Общие функции: категории

	function getCategoriesByCityId($city_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT categories.category_id, categories.category_name, categories.description
							   FROM categories, city_categories
							   WHERE categories.category_id = city_categories.category_id
							   AND city_categories.city_id = ?
							   ORDER BY city_categories.category_id ASC")) ||
			(! $stmt->bind_param('i', $city_id)))
			{ writeLog('getCategories($city_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' city_id: ' . $city_id); }

		$stmt->execute();
		$result = $stmt->get_result();
		$categories_array = array();

		while($result_array = $result->fetch_array()) {
			array_push($categories_array, array($result_array[0], $result_array[1], $result_array[2]));
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $categories_array;
	};

	function getCategoriesIdsByCityId($city_id) { //не используется

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT category_id FROM city_categories WHERE city_id = ? ORDER BY category_id")) ||
			(! $stmt->bind_param('i', $city_id)))
			{ writeLog('getCategoriesIdsByCityId() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' city_id: ' . $city_id); }

		$stmt->execute();
		$result = $stmt->get_result();
		$categories_ids = array();

		while($result_array = $result->fetch_array()) {
			array_push($categories_ids, $result_array[0]);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $categories_ids;
	};

	function getCategoriesForContractor($contractor_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$categories_array = array();

		if ((! $stmt->prepare("SELECT *
							   FROM categories, contractor_categories
							   WHERE contractor_categories.category_id = categories.category_id
							   AND contractor_categories.contractor_id = ?
							   ORDER BY categories.category_id ASC")) ||
			(! $stmt->bind_param('i', $contractor_id)))
			{ writeLog('getCategoriesForContractor($contractor_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' contractor_id: ' . $contractor_id); }

		$stmt->execute();
		$result = $stmt->get_result();

		while($result_array = $result->fetch_array()) {
			array_push($categories_array, array($result_array[0], $result_array[1], $result_array[2]));
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $categories_array;
	};

	function getCategoriesIdsByContractorId($contractor_id) { //не используется

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT category_id FROM contractor_categories WHERE contractor_id = ?")) ||
			(! $stmt->bind_param('i', $contractor_id)))
			{ writeLog('getCategoriesIdsByContractorId($contractor_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' contractor_id: ' . $contractor_id); }

		$stmt->execute();
		$result = $stmt->get_result();
		$contractor_categories_ids = array();

		while($result_array = $result->fetch_array()) {
			array_push($contractor_categories_ids, $result_array[0]);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $contractor_categories_ids;
	};

	function getCategoriesIds() {

		require_once("connect.php");

		$connect = createConnection();
		$query = "SELECT category_id FROM categories";
		$result = mysqli_query($connect, $query) or writeLog("getCategoriesIds() error" . mysqli_error($connect));
		$categories_ids = array();

		while($result_array = mysqli_fetch_array($result)) {
			array_push($categories_ids, $result_array[0]);
		};

		return $categories_ids;
	};

	function getCategoryNameById($category_id) { //не используется

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT category_name FROM categories WHERE category_id = ?")) ||
			(! $stmt->bind_param('i', $category_id)))
			{ writeLog('getCategoryNameById($category_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' category_id: ' . $category_id); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_array();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['category_name'];
	};

// ========= Общие функции: типы запросов

	function getRequestTypesIds($category_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT request_type_id FROM request_types WHERE category_id = ? ORDER BY request_type_id")) ||
			(! $stmt->bind_param('i', $category_id)))
			{ writeLog('getRequestTypesIds($category_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' category_id: ' . $category_id); }

		$stmt->execute();
		$result = $stmt->get_result();
		$request_types_ids = array();

		while($result_array = $result->fetch_array()) {
			array_push($request_types_ids, $result_array[0]);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $request_types_ids;

	};

	function getRequestTypeNameById($request_type_id) { //не используется

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT request_type_name FROM request_types WHERE request_type_id = ?")) ||
			(! $stmt->bind_param('i', $request_type_id)))
			{ writeLog('getRequestTypeNameById() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' request_type_id: ' . $request_type_id); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_array();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['request_type_name'];
	};

// ========= Общие функции: подкатегории

	function getSubcategoriesIds($category_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT subcategory_id FROM subcategories WHERE category_id = ? ORDER BY subcategory_id")) ||
			(! $stmt->bind_param('i', $category_id)))
			{ writeLog('getRequestTypesIds($category_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' category_id: ' . $category_id); }

		$stmt->execute();
		$result = $stmt->get_result();
		$subcategories_ids = array();

		while($result_array = $result->fetch_array()) {
			array_push($subcategories_ids, $result_array[0]);
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $subcategories_ids;
	};

	function getSubcategoryNameById($subcategory_id) { //не используется

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT subcategory_name FROM subcategories WHERE subcategory_id = ?")) ||
			(! $stmt->bind_param('i', $subcategory_id)))
			{ writeLog('getSubcategoryNameById() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' subcategory_id: ' . $subcategory_id); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['subcategory_name'];
	};

// ========= Получение данных о customer'ах

	function checkCustomerEmailExist($email) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT customer_id, password FROM customers WHERE email = ?")) ||
			(! $stmt->bind_param('s', $email)))
			{ writeLog('checkCustomerEmailExist() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();
		$customerAccountData = $stmt->get_result();
		$customerAccountData = $customerAccountData->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		if ($customerAccountData['customer_id']) {

			return $customerAccountData;

		} else { return false; };
	};

	function checkCustomerPhoneExist($phone) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT phone FROM customers WHERE phone = ?")) ||
			(! $stmt->bind_param('s', $phone)))
			{ writeLog('checkCustomerPhoneExist() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' phone: ' . $phone); }

		$stmt->execute();
		$customerPhone = $stmt->get_result();
		$customerPhone = $customerPhone->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $customerPhone['phone'];
	};

	function getCurrentUserId() {

		require_once("connect.php");

		$connect = createConnection();
		$user_type = $_SESSION['user_type'];
		$email = $_SESSION['user_email'];

		if ($user_type === 'customer') {
			$get_user = "SELECT customer_id FROM customers WHERE email = '$email'";
		} else if ($user_type === 'contractor') {
			$get_user = "SELECT contractor_id FROM contractors WHERE email = '$email'";
		} else {
			return;
		};

		$user = mysqli_query($connect, $get_user) or writeLog("getCurrentUserId() error " . mysqli_error($connect));
		$user_id = mysqli_fetch_array($user);

		if ($user_type === 'customer') { return $user_id['customer_id']; }
		else { return $user_id['contractor_id']; }
	};

	function getCustomerIdByEmail($email) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT customer_id FROM customers WHERE email = ?")) ||
			(! $stmt->bind_param('s', $email)))
			{ writeLog('getCustomerIdByEmail() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['customer_id'];

	};

	function getCustomerIdByPhone($phone) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT customer_id FROM customers WHERE phone = ?")) ||
			(! $stmt->bind_param('s', $phone)))
			{ writeLog('getCustomerIdByEmail() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' phone: ' . $phone); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['customer_id'];

	};

	function getCustomerByRequestId($request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$query = "SELECT customer_id FROM requests WHERE request_id = $request_id";
		$result = mysqli_query($connect, $query) or writeLog("getCustomerByRequestId() error " . mysqli_error($connect));
		$result = mysqli_fetch_array($result);

		if ($result['customer_id']) {

			return $result['customer_id'];

		} else if ($result['user_email']) {

			return $result['user_email'];

		} else {

			return $result['user_phone'];

		};

	};

	function getCustomerEmailById($customer_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT email FROM customers WHERE customer_id = ?")) ||
			(! $stmt->bind_param('i', $customer_id)))
			{ writeLog('getCustomerEmailById($customer_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' customer_id: ' . $customer_id); }

		$stmt->execute();

		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $result['email'];
	};


// ========= Получение данных о contractor'ах

	function checkContractorEmailExist($email) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT contractor_id FROM contractors WHERE email = ?")) ||
			(! $stmt->bind_param('s', $email)))
			{ writeLog('checkContractorEmailExist() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();
		$contractorEmail = $stmt->get_result();
		$contractorEmail = $contractorEmail->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		if ($contractorEmail['contractor_id']) {
			return true;
		} else { return false; };
	};

	function getContractorDataById($contractor_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT * FROM contractors WHERE contractor_id = ?;")) ||
			(! $stmt->bind_param('i', $contractor_id)))
			{ writeLog('getContractorDataById($contractor_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' contractor_id: ' . $contractor_id); die; };

		$stmt->execute();
		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		return $result;
	};


// ========= Получение данных о запросах

	function checkRequestIdAccordingToCustomer($request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$customer_id = $_SESSION['user_id'];

		if ((! $stmt->prepare("SELECT request_id FROM requests WHERE customer_id = ? AND request_id = ?;")) ||
			(! $stmt->bind_param('ii', $customer_id, $request_id)))
			{ writeLog('checkRequestIdAccordingToCustomer($request_id) SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' request_id: ' . $request_id . ' customer_id: ' . $customer_id); die; };

		$stmt->execute();
		$result = $stmt->get_result();
		$result = $result->fetch_assoc();

		if ($result['request_id']) {

			return $result['request_id'];

		} else {

			writeLog('Error, request_id: '.substr($request_id, 0, 10).' doesn`t according to customer_id: '.substr($customer_id, 0, 9));
			die;
		};
	};

	function checkRequestIsClosed($request_id) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT is_closed FROM requests WHERE request_id = ?;")) ||
			(! $stmt->bind_param('i', $request_id)))
			{ writeLog('checkRequestIsClosed() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' customer_id: ' . $customer_id); };

		$stmt->execute();
		$result = $stmt->get_result();
		$result = $result->fetch_assoc();
		return $result['is_closed'];
	};

	function getLastRequestIdForCurrentCustomer() {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT request_id FROM requests WHERE customer_id = ? ORDER BY request_id DESC LIMIT 1;")) ||
			(! $stmt->bind_param('i', $_SESSION['user_id']))
		   ) { writeLog('getLastRequestIdForCurrentCustomer() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' customer_id: ' . $_SESSION['user_id']); }

		$stmt->execute();
		$request = $stmt->get_result();
		$request = $request->fetch_assoc();

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		return $request['request_id'];
	};

?>