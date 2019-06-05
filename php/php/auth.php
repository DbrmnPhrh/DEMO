<?php

	if (! defined ( "READFILE" )) {
		exit ( "Error, wrong path to file.<br><a href='/'>Go to main</a>." );
	};

	function registration($city_id, $user_type, $email, $password) {

		require_once("connect.php");
		require_once("common.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();
		$table = $user_type . 's';
		$customerAccountData = checkCustomerEmailExist($email);
		$password = password_hash($password, PASSWORD_DEFAULT);

		if ($user_type == "contractor" && CONTRACTOR_REG_DISABLE)
		   { writeLog('trying to register as contractor when CONTRACTOR_REG_DISABLE==true' . ' user_type: ' . $user_type); die; };

		if ((! $stmt->prepare("INSERT INTO $table (email, password, city_id)" . "VALUES (?, ?, ?);")) ||
			(! $stmt->bind_param('ssi', $email, $password, $city_id)))
		    { writeLog('registration() INSERT error (' . $stmt->errno . ') ' . $stmt->error . ' user_type: ' . $user_type . ' email: ' . $email . ' password: ' . $password); die; };

		if (($customerAccountData['customer_id'] && $customerAccountData['password']) || checkContractorEmailExist($email)) {

			sendUserMessage('info', 'Пользователь с этим email уже существует!');
			return;

		} else if ($customerAccountData['customer_id'] && !$customerAccountData['password']) {

			sendUserMessage('info', 'Вы уже отправляли быстрый запрос используя этот email, нажмите <СЮДА>, чтобы создать пароль для Вашего аккаунта. Таким образом Вы будете полноценно зарегистрированы');
			return;

		} else if (($stmt->execute())) {

			sessionWrite($city_id, '', $email, '', $user_type, true);

			echo json_encode(array( "msg_type" => "success",
									"msg" => "Новый пользователь успешно создан! Переход в личный кабинет!",
									"url" => URL_INDEX_PAGE));
		} else {
			writeLog('stmt execute error (' . $stmt->errno . ') ' . $stmt->error); die;
			echo USRMSG_DB_ERROR;
			return;
		};

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die; };
	};

	function registrationByEmail($city_id, $category_id, $email) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if (! checkCustomerEmailExist($email)) {

			if ((! $stmt->prepare("INSERT INTO customers (city_id, email)" . "VALUES (?, ?);")) ||
				(! $stmt->bind_param('is', $city_id, $email)))
				{ writeLog('INSERT error (' . $stmt->errno . ') ' . $stmt->error . ' city_id: ' . $city_id . '/email: ' . $email); die; }
			else if ((! $stmt->execute())) {
				{ writeLog('$stmt->execute error (' . $stmt->errno . ') ' . $stmt->error); die; };
			};

			if ((! $stmt->close()) ||
				(! $connect->close()))
				{ writeLog('registrationByEmail() stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die; };
		};

		sessionWrite($city_id, $category_id, $email, '', 'guest', false);
		return;
	};

	function registrationByPhone($city_id, $category_id, $phone) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if (! checkCustomerPhoneExist($phone)) {

			if ((! $stmt->prepare("INSERT INTO customers (city_id, phone)" . "VALUES (?, ?);")) ||
				(! $stmt->bind_param('is', $city_id, $phone)))
				{ writeLog('INSERT error (' . $stmt->errno . ') ' . $stmt->error . ' city_id: ' . $city_id . '/phone: ' . $phone); die; }
			else if ((! $stmt->execute())) {
				{ writeLog('$stmt->execute error (' . $stmt->errno . ') ' . $stmt->error); die; };
			};

			if ((! $stmt->close()) ||
				(! $connect->close()))
				{ writeLog('registrationByPhone() stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); die; };
		};

		sessionWrite($city_id, $category_id, '', $phone, 'guest', false);
		return;
	};

	function authorization($email, $password) {

		require_once("connect.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if ((! $stmt->prepare("SELECT password FROM customers WHERE email = ?")) ||
			(! $stmt->bind_param('s', $email)))
		    { writeLog('authorization() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();
		$result = $stmt->get_result();
		$customerPwdHash = $result->fetch_assoc();

		if ((! $stmt->prepare("SELECT password FROM contractors WHERE email = ?;")) ||
			(! $stmt->bind_param('s', $email)))
		    { writeLog('authorization() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }

		$stmt->execute();
		$result = $stmt->get_result();
		$contractorPwdHash = $result->fetch_assoc();

		if (password_verify($password, $customerPwdHash['password'])) {

			$user_type = "customer";
			$city_id = getUserCityIdByEmail($email, $user_type);
			$msg_type = "success";
			$msg = "Успешный вход!";
			$url = URL_INDEX_PAGE;

			sessionWrite($city_id, '', $email, '', $user_type, true);

		} else if (password_verify($password, $contractorPwdHash['password'])) {

			$user_type = "contractor";
			$city_id = getUserCityIdByEmail($email, $user_type);
			$msg_type = "success";
			$msg = "Вы вошли как организация ($email)";
			$url = URL_CONTRACTOR_PAGE;

			sessionWrite($city_id, '', $email, '', $user_type, true);

		} else {

			$msg_type = "warning";
			$msg = "Неверный email и/или пароль!";

		};

		echo json_encode(array(
			'msg_type' => $msg_type,
			'msg' => $msg,
			'user_type' => $user_type,
			'url' => $url
		));

		if ((! $stmt->close()) ||
			(! $connect->close()))
			{ writeLog('stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };
	};

	function logout() {
		session_start();
		$_SESSION = array();
		session_destroy();
		sendJSONData(array(
						"url" => URL_INDEX_PAGE
					));
	};

	function sessionWrite($city_id=null,
						  $category_id=null,
						  $user_email=null,
						  $user_phone=null,
						  $user_type=null,
						  $authorized=null)
	{

		session_start();
		$_SESSION['city_id'] 	 = $city_id;
		$_SESSION['category_id'] = $category_id;
		$_SESSION['user_email']  = $user_email;
		$_SESSION['user_phone']  = $user_phone;
		$_SESSION['user_type'] 	 = $user_type;
		$_SESSION['authorized']  = $authorized;

		if ($user_email && $user_type == 'guest') {
			$_SESSION['user_id'] = getCustomerIdByEmail($user_email);
		} else if ($user_phone && $user_type == 'guest') {
			$_SESSION['user_id'] = getCustomerIdByPhone($user_phone);
		} else {
			$_SESSION['user_id'] = getCurrentUserId();
		};

		session_register_shutdown();
		session_write_close();
		return;
	};


// ========= Восстановления пароля

	function customerPasswordRestore($email) {

		require_once("connect.php");
		require_once("common.php");

		$connect = createConnection();
		$stmt = $connect->stmt_init();

		if (checkCustomerEmailExist($email)) {

			$password = genPassword(8);

			if ((! $stmt->prepare("UPDATE customers SET password = ? WHERE email = ?;")) ||
				(! $stmt->bind_param('ss', $password, $email)))
			    { writeLog('customerPasswordRestore() SELECT error (' . $stmt->errno . ') ' . $stmt->error . ' email: ' . $email); }
			else if (($stmt->execute())) {
				// sendPassword($email, $password); //раскомментить
				sendUserMessage('success', 'Спасибо. Новый пароль отправлен на указанный email');
			};

			if ((! $stmt->close()) ||
				(! $connect->close()))
				{ writeLog('customerPasswordRestore() stmt or connect close error (' . $stmt->errno . ') ' . $stmt->error); };

		} else {
			sendUserMessage('warning', 'Нет пользователей с таким email!');
		};
	};

	function genPassword($length) {
	    $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	    $length = intval($length);
	    $size = strlen($chars)-1;
	    $password = "";
	    while($length--) $password .= $chars[rand(0,$size)];
	    return $password;
	};

	function sendPassword($email, $password) {
		$to = $email;
		$subject = 'Восстановление пароля';
		$message = 'Ваш новый пароль: ' . $password;
		$headers = "Content-type: text/plain; charset=UTF-8\r\n";
		mail($to, $subject, $message, $headers);
	};
?>