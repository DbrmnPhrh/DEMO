'use strict';

$(document).ready(function() {

	window.API_ROOT = '/qix/'
	window.current_user = getCurrentUser();

	if (window.current_user['user_type'] === 'guest') {

		console.log('window.current_user '+window.current_user['user_type']+' '+ (window.current_user['user_email'] ? window.current_user['user_email'] : '(no_email)'));
		prepareGuestPage();

	} else if (window.current_user['user_type'] === 'customer') {

		console.log('window.current_user '+window.current_user['user_type']+' '+ (window.current_user['user_email'] ? window.current_user['user_email'] : '(no_email)'));
		prepareCustomerPage();

	} else if (window.current_user['user_type'] === 'contractor') {

		location.replace(URL_CONTRACTOR_PAGE);

	};

	// ========= Очистка кол-ва отправленных сообщений в день
	localStorage.removeItem('sent'); // удалить
	localStorage.removeItem('old'); // удалить

	// ========= Очистка кол-ва регистраций
	localStorage.removeItem('reg', 0);

	// ========= Предварительные настройки
	toggleRegUserType('customer');
	toggleViewQuickOffersEmailPhoneDiv('hide');
	clearRequestData();
	clearLocalStorageRequestData();

	// ========= Получение данных
	getCities();
//	setCity(1);
//	localStorage.setItem('city_id', 1); //Город по умолчанию
//	window.city_id = localStorage.getItem('city_id'); //Город по умолчанию
//	getCategories();
//	setCategory(1); //Пока по умолчанию будет категория 1
//	getRequestTypes();
//	getSubcategories();

});


// ========= Формирование элементов интерфейса

function prepareGuestPage() {

	console.log('preparing guest page'); //удалить
	$('#user').html('');
	$('#view_quick_offers').fadeIn('fast');
};

function prepareCustomerPage() {

	console.log('preparing customer page: '+window.current_user['user_email']); //удалить
	$('#user').html(window.current_user['user_email']);
	$('#you_logged_in_as').fadeIn('fast');
	toggleEmailPhoneDiv('hide');
	$('#view_quick_offers').fadeOut('fast');
	$('#view_quick_offers_div').fadeOut('fast');
};

function getCities() {

	$.ajax({
		url: window.API_ROOT+'cities/',
		type: 'GET',
		success: function(json) {
			var cities = $.parseJSON(json);
			var i
			var l = cities.length
			for (i = 0; i < l; i++) {
				$('#city').append('<option value="' + cities[i][0] + '">' + cities[i][1] + '</option>');
				console.log(cities[i][0]+' '+cities[i][1]); //удалить
			};

			if (l > 1) {
				$('#cities_div').fadeIn('fast');
			};
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function getCategories() {
	$.ajax({
		url: window.API_ROOT+'categories/',
		type: 'GET',
		data: { 'city_id': window.city_id },
		success: function(json) {
			$('#categories').empty();
			var categories = $.parseJSON(json);
			var i
			var l = categories.length
			for (i = 0; i < l; i++) {
				$('#customer_categories').append('<div><a class="customer_category" href="" value=' + categories[i][0] + '>' + categories[i][0] +' '+ categories[i][1] + ' </a></div>');
				console.log(categories[i][0]); //удалить
				console.log(categories[i][1]); //удалить
				console.log(categories[i][2]); //удалить
			};
			preventDefault();
			listenDynamicElemEvents();
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function getRequestTypes() {
	$.ajax({
		url: window.API_ROOT+'request_types/',
		type: 'GET',
		data: { 'category_id': window.category_id },
		success: function(json) {
			var request_types = $.parseJSON(json);
			$('#request_type').append('<option value="" selected></option>');
			var i
			var l = request_types.length
			for (var i = 0; i < l; i++) {
				$('#request_type').append('<option value="' + request_types[i][0] + '">' + request_types[i][1] + '</option>');
			};
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function getSubcategories() {
	$.ajax({
		url: window.API_ROOT+'subcategories/',
		type: 'GET',
		data: { 'category_id': window.category_id },
		success: function(json) {
			var subcategories = $.parseJSON(json);
			$('#subcategory').append('<option value="" selected></option>');
			var i
			var l = subcategories.length
			for (var i = 0; i < l; i++) {
				$('#subcategory').append('<option value="' + subcategories[i][0] + '">' + subcategories[i][1] + '</option>');
			};
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function toggleCategoriesShowHide(state) {
	if (state === 'show') {
		getCategories();
		$('#customer_categories').empty();
		$('#categories_div').fadeIn('fast');
	} else {
		$('#categories_div').fadeOut('fast');
		$('#customer_categories').empty();
	};
};

function toggleEmailPhoneDiv(state) {
	if (state === 'user_email') {
		$('#user_phone_preffix').val('');
		$('#user_phone_number').val('');
		$('#email_and_phone_div').fadeIn('fast');
		$('#user_email_div').fadeIn('fast');
		$('#user_phone_div').fadeOut('fast');
	} else if (state === 'user_phone') {
		$('#user_email').val('');
		$('#email_and_phone_div').fadeIn('fast');
		$('#user_email_div').fadeOut('fast');
		$('#user_phone_div').fadeIn('fast');
	} else if (state === 'hide') {
		$('#user_email').val('');
		$('#user_phone_preffix').val('');
		$('#user_phone_number').val('');
		$('#email_and_phone_div').fadeOut('fast');
	};
};


// ========= Установка необходимых параметров запроса

function setCity(city_id) {
	if (window.city_id != city_id) {
		localStorage.setItem('city_id', city_id);
		window.city_id = localStorage.getItem('city_id');
		$('#customer_categories').empty();
		toggleCategoriesShowHide('show');
		console.log('window.city_id: '+window.city_id); //удалить
	};
};

function setCategory(category_id) {
	localStorage.setItem('category_id', category_id);
	window.category_id = localStorage.getItem('category_id');

	clearRequestData();
	$('#request_type').empty();
	$('#subcategory').empty();
	$('#request_options_table').fadeIn('fast');
	getRequestTypes();
	getSubcategories();
	console.log('window.category_id: '+window.category_id); //удалить
};

function setRequestType(request_type_id) {
	window.request_type_id = request_type_id;
	$('#subcategory_td').fadeIn('fast');
	console.log('window.request_type_id: '+window.request_type_id); //удалить
};

function setSubcategory(subcategory_id) {
	window.subcategory_id = subcategory_id;
	$('#title_text_and_request_text_div').fadeIn('fast');
	if (window.current_user['user_type'] === 'guest') { toggleEmailPhoneDiv('user_email'); };
	$('#user_agreement_div').fadeIn('fast');
	$('#send_button_div').fadeIn('fast');
	console.log('window.subcategory_id: '+window.subcategory_id); //удалить
};


// ========= Очистка запроса

function clearRequestData() {
	$('#request_type').val('');
	$('#subcategory').val('');
    $('#title_text').val('');
	$('#request_text').val('');
	$('#user_email').val('');
	$('#user_phone_preffix').val('');
	$('#user_phone_number').val('');
	$('#user_agreement_checkbox').prop("checked", false);
	$('#send_request').prop('disabled', true);
	$('#title_text_and_request_text_div').fadeOut('fast');
	$('#subcategory_td').fadeOut('fast');
	$('#email_and_phone_div').fadeOut('fast');
	$('#user_agreement_div').fadeOut('fast');
	$('#send_button_div').fadeOut('fast');
};

function clearLocalStorageRequestData() {
	localStorage.removeItem('category_id');
	localStorage.removeItem('user_email');
	localStorage.removeItem('user_phone');
};


// ========= Подготовка быстрого запроса

function prepareRequest() {

		var category_id = window.category_id;
		var request_type_id = window.request_type_id;
		var subcategory_id = window.subcategory_id;
		var title_text = $('#title_text').val();
		var request_text = $('#request_text').val();
		var user_email = $.trim($('#user_email').val());
		var user_phone = $('#user_phone_preffix').val() + $('#user_phone_number').val();
		localStorage.setItem('user_email', user_email);
		localStorage.setItem('user_phone', user_phone);

	if (checkRequestData(title_text, request_text, user_email, user_phone) && checkDailyRequestsLimit()) {
		showAlert('info', 'Отправка...');
		sendRequest(category_id, request_type_id, subcategory_id, title_text, request_text, user_email, user_phone);
	};
};


// ========= Проверка запроса (валидация полей и количество запросов в день не более 5)

function checkRequestData(title_text, request_text, user_email, user_phone) {

	if (!checkRequestText(title_text, request_text)) {

		return false;

	} else if (user_email.length == 0 && user_phone.length == 0 && window.current_user['user_type'] === 'guest') {

		showAlert('warning', 'Введите email или телефон');

	} else if (user_email.length > 0 && user_phone.length == 0 && window.current_user['user_type'] === 'guest') {

		if (!checkEmail(user_email)) {
			showAlert('warning', USRMSG_INCORRECT_EMAIL);
		} else {
			return true;
		};

	} else if (user_email.length == 0 && user_phone.length > 0 && window.current_user['user_type'] === 'guest') {

		if (!checkPhone(user_phone)) {
			showAlert('warning', USRMSG_INCORRECT_PHONE);
		} else {
			return true;
		};

	} else {

		return true;
	
	};
};


// ========= Отправка запроса

function sendRequest(category_id, request_type_id, subcategory_id, title_text, request_text, user_email, user_phone) {

	console.log(city_id, category_id, request_type_id, subcategory_id, title_text, request_text, user_email, user_phone); //удалить

	$.ajax({
		url: window.API_ROOT+'requests/',
		type: 'POST',
		data: {
			'city_id': window.city_id,
			'category_id': category_id,
			'request_type_id': request_type_id,
			'subcategory_id': subcategory_id,
			'title_text': title_text,
			'request_text': request_text,
			'user_email': user_email,
			'user_phone': user_phone
		},
		success: function(json) {
			var data = $.parseJSON(json);
			localStorage.setItem('sent', parseInt(localStorage.getItem('sent')) + 1);
			console.log('SENT = ' + localStorage.getItem('sent')); //удалить
			showAlert(data['msg_type'], data['msg']);
			setTimeout(function() { location.replace(URL_CUSTOMER_GET_OFFERS_PAGE); }, LOCATION_REPLACE_TIMEOUT);
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};


// ========= Формирование элементов интерфейса проверки предложений на быстрый запрос

function toggleViewQuickOffersEmailPhoneDiv(state) {
	if (state === 'view_quick_offers_email') {
		$('#view_quick_offers_phone_preffix').val('');
		$('#view_quick_offers_phone_number').val('');
		$('#view_quick_offers_email_div').fadeIn('fast');
		$('#view_quick_offers_phone_div').fadeOut('fast');
	} else if (state === 'view_quick_offers_phone') {
		$('#view_quick_offers_email').val('');
		$('#view_quick_offers_email_div').fadeOut('fast');
		$('#view_quick_offers_phone_div').fadeIn('fast');
	} else if (state === 'view_quick_offers_div') {
		$('#view_quick_offers_div').fadeIn('fast');
	} else if (state === 'hide') {
		$('#view_quick_offers_div').fadeOut('fast');
	};
};


// ========= Просмотр предложений на быстрый запрос

function viewOffers() {
	clearRequestData();
	var user_email = $('#view_quick_offers_email').val();
	var user_phone = $('#view_quick_offers_phone_preffix').val() + $('#view_quick_offers_phone_number').val();

	if (user_email) {

		if (checkEmail(user_email)) {
			localStorage.setItem('user_email', user_email);
			location.replace(URL_CUSTOMER_GET_OFFERS_PAGE);
		} else { showAlert('warning', USRMSG_INCORRECT_EMAIL); };

	} else if (user_phone) {

		if (checkPhone(user_phone)) {
			localStorage.setItem('user_phone', user_phone);
			location.replace(URL_CUSTOMER_GET_OFFERS_PAGE);
		} else { showAlert('warning', USRMSG_INCORRECT_PHONE); };

	};
};


// ========= Регистрация и авторизация

function toggleRegUserType(user_type) {
	$('input[type="radio"]').not(':checked').prop("checked", false);
	window.user_type = user_type;
};

function toggleAuthRegForms(action) {
	if (action == 'reg') {
		$('#authorization_div').fadeOut('fast');
		$('#registration_div').fadeIn('fast');
	} else if (action == 'auth') {
		$('#registration_div').fadeOut('fast');
		$('#authorization_div').fadeIn('fast');
	} else if (action == 'close') {
		$('#authorization_div').fadeOut('fast');
		$('#registration_div').fadeOut('fast');
	};
};