<?php

	define ( "READFILE", true ); // https://habrahabr.ru/post/143035/
 
	require("api.php");
	require("auth.php");
	require("chat.php");
	require("common.php");
	require("validation.php");

	session_start();
	$_SESSION['user_type'] = $_SESSION['user_type'] ? $_SESSION['user_type'] : 'guest';

	switch ($_SESSION['user_type']) {

		case 'guest':

			actionGuest($_REQUEST['action']);
			break;

		case 'customer':

			if ($_SESSION['user_type'] === 'customer' && $_SESSION['authorized']) {

				actionCustomer($_REQUEST['action']);
				break;

			} else {

				sendUserMessage('info', USRMSG_NOT_AUTH);
				die;

			};

		case 'contractor':

			if ($_SESSION['user_type'] === 'contractor' && $_SESSION['authorized']) {

				actionContractor($_REQUEST['action']);
				break;

			} else {

				sendUserMessage('info', USRMSG_NOT_AUTH);
				die;

			};

		default:
			writeLog('Unknown user type: '.getCurrentUserType());
			die;
	};

	function actionGuest($action) {

		switch($action) {

			case 'get_current_user':

				sendCurrentUser();
				break;

			case 'get_socket_parameters':

				sendSocketParameters();
				break;

			case 'get_cities':

				sendJSONData(getCities());
				break;

			case 'get_categories':

				sendJSONData(getCategoriesByCityId(validateCityId($_REQUEST['city_id'])));
				break;

			case 'get_request_types':

				sendJSONData(getRequestTypes(validateCategoryId($_REQUEST['category_id'])));
				break;

			case 'get_subcategories':

				sendJSONData(getSubcategories(validateCategoryId($_REQUEST['category_id'])));
				break;

			case 'add_request':

				if ($_REQUEST['user_email'] && ! $_REQUEST['user_phone']) {

					registrationByEmail(validateCityId($_REQUEST['city_id']),
										validateCategoryId($_REQUEST['category_id']),
										validateEmail($_REQUEST['user_email'])
					);

				} else if (! $_REQUEST['user_email'] && $_REQUEST['user_phone']) {

					registrationByPhone(validateCityId($_REQUEST['city_id']),
										validateCategoryId($_REQUEST['category_id']),
										validatePhone($_REQUEST['user_phone'])
					);

				} else {

					writeLog('Wrong both user_email: '.substr($_REQUEST['user_email'], 0, 40).' and user_phone: '.substr($_REQUEST['user_phone'], 0, 12));
					die;

				};

				addRequest(validateRequestTypeId($_POST['request_type_id']),
						   validateSubcategoryId($_POST['subcategory_id']),
						   validateText($_POST['title_text'], REQUEST_TITLE_MIN_LEN, REQUEST_TITLE_MAX_LEN, 'title_text'),
						   validateText($_POST['request_text'], REQUEST_TEXT_MIN_LEN, REQUEST_TEXT_MAX_LEN, 'request_text')
				);

				debug(
					'add_request session end: '.
					$_SESSION['city_id'].'/'.
					$_SESSION['category_id'].'/'.
					$_SESSION['user_email'].'/'.
					$_SESSION['user_phone'].'/'.
					$_SESSION['user_type'].'/'.
					$_SESSION['authorized'].'/'.
					$_SESSION['user_id']
				); //удалить
				break;

			case 'get_requests_history':

				if ($_REQUEST['user_email'] && ! $_REQUEST['user_phone']) {

					$_SESSION['user_id'] = getCustomerIdByEmail(validateEmail($_REQUEST['user_email'], 'trying to get requests history by email'));

				} else if (! $_REQUEST['user_email'] && $_REQUEST['user_phone']) {

					$_SESSION['user_id'] = getCustomerIdByPhone(validatePhone($_REQUEST['user_phone'], 'trying to get requests history by phone'));

				} else {

					writeLog('Wrong both user_email: '.substr($_REQUEST['user_email'], 0, 40).'/user_phone: '.substr($_REQUEST['user_phone'], 0, 12));
					die;

				};

				sendJSONData(getRequestsHistory($_SESSION['user_id']));
				break;

			case 'get_offers_for_request':

				sendJSONData(getOffersForRequest(validateInt($_GET['request_id'], 1, REQUEST_MAX_ID, 'request_id'),
												 validateInt($_GET['offer_last_id'], 1, OFFER_MAX_LAST_ID, 'offer_last_id'))
				);
				break;

			case 'get_chat_msg':

				

			case 'add_chat_msg':

				addChatMessageFromCustomer(validateText($_POST['chat_msg'], CHAT_MSG_MIN_LEN, CHAT_MSG_MAX_LEN, 'guest_chat_msg'),
										   validateEmail($_POST['user_email']),
										   validatePhone($_POST['user_phone'])
				);
				break;

			case 'reg':

				debug(validateCityId($_POST['city_id']).'/'.validateUserType($_POST['user_type']).'/'.validateEmail($_POST['reg_email'], 'reg_email').'/'.validatePassword($_POST['reg_password'], 'reg_password'));

				registration(validateCityId($_POST['city_id']),
							 validateUserType($_POST['user_type']),
							 validateEmail($_POST['reg_email'], 'reg_email'),
							 validatePassword($_POST['reg_password'], 'reg_password')
				);
				break;

			case 'auth':

				authorization(validateEmail($_POST['email']),
							  validatePassword($_POST['password'])
				);
				break;

			case 'logout':

				logout();
				break;

			case 'pwd_restore':

				if (mb_strlen($_POST['email']) < 6) { die; };
				customerPasswordRestore(validateEmail($_POST['email']));
				break;

			default:

				writeLog('Wrong action: '.$_REQUEST['action']);
				break;
		};

	};

	function actionCustomer($action) {

		switch($action) {

			case 'get_current_user':

				sendCurrentUser();
				break;

			case 'get_socket_parameters':

				sendSocketParameters();
				break;

			case 'get_cities':

				sendJSONData(getCities());
				break;

			case 'get_categories':

				sendJSONData(getCategoriesByCityId(validateCityId($_REQUEST['city_id'])));
				break;

			case 'get_request_types':

				sendJSONData(getRequestTypes(validateCategoryId($_REQUEST['category_id'])));
				break;

			case 'get_subcategories':

				sendJSONData(getSubcategories(validateCategoryId($_REQUEST['category_id'])));
				break;

			case 'add_request':

				$_SESSION['category_id'] = validateCategoryId($_POST['category_id']);

				addRequest(validateRequestTypeId($_POST['request_type_id']),
						   validateSubcategoryId($_POST['subcategory_id']),
						   validateText($_POST['title_text'], REQUEST_TITLE_MIN_LEN, REQUEST_TITLE_MAX_LEN, 'title_text'),
						   validateText($_POST['request_text'], REQUEST_TEXT_MIN_LEN, REQUEST_TEXT_MAX_LEN, 'request_text')
				);
				break;

			case 'get_requests_history':

				sendJSONData(getRequestsHistory($_SESSION['user_id']));
				break;

			case 'get_offers_for_request':

				sendJSONData(getOffersForRequest(validateInt($_GET['request_id'], 0, REQUEST_MAX_ID, 'request_id'),
									 			 validateInt($_GET['offer_last_id'], 0, OFFER_MAX_LAST_ID, 'offer_last_id'))
				);
				break;

			case 'get_chat_msg':

				

			case 'add_chat_msg':

				addChatMessageFromCustomer(validateText($_POST['chat_msg'], CHAT_MSG_MIN_LEN, CHAT_MSG_MAX_LEN, 'customer_chat_msg'),
										   validateEmail($_POST['user_email']),
										   validatePhone($_POST['user_phone'])
				);
				break;

			case 'logout':

				logout();
				break;

			default:
				writeLog('Wrong action: '.$_REQUEST['action']);
				break;
		};

	};

	function actionContractor($action) {

		switch($action) {

			case 'get_current_user':

				sendCurrentUser();
				break;

			case 'get_socket_parameters':

				sendSocketParameters();
				break;

			case 'get_categories':

				sendJSONData(getCategoriesForContractor($_SESSION['user_id']));
				break;

			case 'get_requests':

				getRequests($_SESSION['city_id'],
							validateCategoryId($_GET['category_id']),
							validateInt($_GET['request_last_id'], 0, REQUEST_MAX_ID)
				);
				break;

			case 'add_offer':

				addOffer(
					validateInt($_POST['price_from'], 0, OFFER_MAX_COST_FROM),
					validateInt($_POST['price_to'], 0, OFFER_MAX_COST_TO),
					validateInt($_POST['period'], 0, OFFER_MAX_PERIOD),
					validatePeriodUnits($_POST['period'], $_POST['period_units']),
					validateText($_POST['offer_text'], 0, OFFER_TEXT_MAX_LEN, 'offer_text'),
					validateInt($_POST['request_id'], 1, REQUEST_MAX_ID) // id может быть от 1 до максимального int
				);
				break;

			case 'add_chat_msg':

				addChatMessageFromContractor(validateText($_POST['chat_msg'], CHAT_MSG_MIN_LEN, CHAT_MSG_MAX_LEN, 'contractor_chat_msg'),
											 validateInt($_POST['request_id'], 0, REQUEST_MAX_ID));
				break;

			case 'logout':

				logout();
				break;

			default:
				writeLog('Wrong action: '.$_REQUEST['action']);
				break;
		};

	};

?>