<?php

	if (! defined ( "READFILE" )) {
		exit ( "Error, wrong path to file.<br><a href='/'>Go to main</a>." );
	};

	function validateUserType($user_type) {

		if ($user_type === 'guest' ||
			$user_type === 'customer' ||
			$user_type === 'contractor') {

			return $user_type;

		} else {

			writeLog('Wrong user_type: '.substr($user_type, 0, 9));
			die;

		};
	};

	function validateCityId($city_id) {

		if (validateInt($city_id, 1, getCitiesQuantity(), 'city_id')) {

			return $city_id;

		} else {

			writeLog('Wrong city_id: '.substr($city_id, 0, 10));
			die;

		};
	};

	function validateCategoryId($category_id) {

		$categories_ids_array = getCategoriesIds();

		if (validateInt($category_id, 1, count($categories_ids_array), 'category_id value') &&
			in_array($category_id, $categories_ids_array))
		{

			return $category_id;

		} else {

			writeLog('Wrong category_id: '.substr($category_id, 0, 9));
			die;

		};
	};

	function validateRequestTypeId($request_type_id) {

		$request_types_ids_array = getRequestTypesIds($_SESSION['category_id']);

		if (in_array($request_type_id, $request_types_ids_array)) {

			return $request_type_id;

		} else {

			writeLog('Wrong request_type_id: '.substr($request_type_id, 0, 25));
			die;

		};
	};

	function validateSubcategoryId($subcategory_id) {

		$subcategories_ids_array = getSubcategoriesIds($_SESSION['category_id']);
		if (in_array($subcategory_id, $subcategories_ids_array)) {

			return $subcategory_id;

		} else {

			writeLog('Wrong subcategory_id: '.substr($subcategory_id, 0, 25));
			die;

		};
	};

	function validateEmail($email, $description=null) {

		if (! $email) {

			return;

		} else if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

			return $email;

		} else {

			writeLog('Wrong '.$description.': '.substr($email, 0, 90));
			die;

		};
	};

	function validatePhone($phone, $description=null) {

		if (preg_match('/^(?:\d{10,10}|)$/', $phone)) {

			return strval($phone);

		} else {

			writeLog('Wrong '.$description.': '.substr($phone, 0, 15));
			die;

		};
	};

	function validatePassword($password, $description=null) {

		if (preg_match('/^[a-zA-Z0-9]{6,20}$/i', $password)) {

			return strval($password);

		} else {

			writeLog('Wrong '.$description.': '.substr($password, 0, 15));
			die;

		};
	};

	function validateText($text, $min_len, $max_len, $description=null) {

		require_once("connect.php");

		// Проверяем фактическую длину многострочного $request_text с учётом переносов строк
		if (mb_strlen($text) >= $min_len && mb_strlen($text) <= $max_len) {

			$connect = createConnection();
			$stmt = $connect->stmt_init();

			$text = strval($text);
			$text = strip_tags($text);
			$text = htmlspecialchars($text);
			// Затем прогоняем через mysqli_real_escape_string, после этого длина $request_text
			// увеличивается, поэтому в БД varchar(700) вместо varchar(600)
			$text = mysqli_real_escape_string($connect, $text);

		    return $text;

		} else {

			writeLog('Wrong '.$description.'('.mb_strlen($text).'): '.substr($text, $min_len, 50));
			die;

		};
	};

	function validateInt($int, $min, $max, $description=null) {

		if ($int === '' || !isset($int)) { $int = 0; };
		if (filter_var($int, FILTER_VALIDATE_INT) === 0 ||
			filter_var($int, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max)))) {

			return $int;

		} else {

			writeLog('Wrong '.$description.': '.substr($int, 0, 20));
			die;

		};
	};

	function validatePeriodUnits($period, $period_units) {

		// Если размерность срока равно 0, то единица измерения срока в БД тоже равна 0
		if ($period == 0) { $period_units = 0;  return $period_units; }
		else if ($period_units == 60 ||
				 $period_units == 3600 ||
				 $period_units == 86400 ||
				 $period_units == 604800 ||
				 $period_units == 2592000 &&
				 // Срок выполнения работ не может превышать 12 месяцев (12 * 2592000)
				 $period * $period_units <= 31104000) {

			return $period_units;

		} else {
				writeLog('validatePeriodUnits() error. Period: '.$period.'/period_units: '.$period_units);
				die;
		};
	};

?>