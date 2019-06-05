var URL_INDEX_PAGE = 'index.html';
var URL_CUSTOMER_GET_OFFERS_PAGE = 'customer_get_offers.html';
var URL_CONTRACTOR_PAGE = 'contractor.html';
var LOCATION_REPLACE_TIMEOUT = 1500;
var LOCATION_RELOAD_TIMEOUT = 3000;
var NEW_REQUESTS_GET_INTERVAL = 5000;
var NEW_OFFERS_GET_INTERVAL = 5000;
var REQUEST_TITLE_MIN_LEN = 5;
var REQUEST_TITLE_MAX_LEN = 50;
var REQUEST_TEXT_MIN_LEN = 10;
var REQUEST_TEXT_MAX_LEN = 600;
var OFFER_TEXT_MAX_LEN = 600;
var CONTRACTOR_REQ_TABL_SEARCH_PLACEHOLDER = 'дата, любой текст запроса, телефон, email клиента';
var TIP_TITLE_TEXT = 'Для ускорения предоставления информации о стоимости пожалуйста вводите наименование бренда правильно, т.е., например, не "Самсунг", а "Samsung", не "Айфон", а "iPhone" и т.п.';
var TIP_REQUEST_TEXT = 'Подсказка к сообщению';
var USRMSG_INCORRECT_EMAIL = 'Неверный формат email';
var USRMSG_INCORRECT_PHONE = 'Неверный формат номера телефона';
var USRMSG_INCORRECT_EMAIL_LENGTH = 'Email должен содержать не менее 8 символов';
var USRMSG_INCORRECT_PASS = 'Пароль должен содержать от 6 до 20 символов и должен состоять из цифр 0-9 или латинских букв a-z или A-Z. Русские буквы и спецсимволы недопустимы';
var USRMSG_INCORRECT_TITLE_TEXT = 'В поле наименования модели должно быть не менее 5 и не более 50 символов';
var USRMSG_INCORRECT_REQUEST_TEXT = 'В поле текста запроса должно быть не менее 10 и не более 600 символов';
var USRMSG_REQUESTS_LIMIT = 'Количество отправляемых запросов с одного IP адреса в день превышено! Новые запросы вы сможете отправить завтра!';
var USRMSG_PAGE_RELOAD = 'Страница будет перезагружена!';


$(document).ready(function() {

	preventDefault();
	listenElemEvents();

	showTooltip(null,null);
	setWhatIsItTipsText();
	showWhatIsItTip();

	var socket;
});


function preventDefault() {
	$('a').click(function(event) {
	    event.preventDefault();
	});
};

function showTooltip(elem_id, tooltip_text) {

	$(document).tooltip({
      track: true
    });

    if (elem_id && tooltip_text) {
		$('#'+elem_id).attr("title", tooltip_text);
    };
};

function setWhatIsItTipsText() {

	$('#title_text').prop('title', TIP_TITLE_TEXT);
	$('#request_text').prop('title', TIP_REQUEST_TEXT);
};

function showWhatIsItTip() {

	$('.whatIsItTip').tooltip({
		position: {
			my: "left top",
			at: "right+5 top-5",
			collision: "none"
	    },
	});
};

function ajaxErrorHandler() {
	showAlert('error', USRMSG_PAGE_RELOAD);
	// setTimeout(function(){ location.reload(true); }, LOCATION_RELOAD_TIMEOUT);
};

/* типы обрабатываемых сообщений
	'success' - успешно
	'info' - информационные сообщения
	'warning' - предупреждающие сообщения
	'error' - неисправимая ошибка требующая перезагрузки страницы
*/
function showAlert(type, message) {
	$('#alert_box').fadeIn('fast');
	$('#alert_text').html(message);
};

function closeAlert() {
	$('#alert_box').fadeOut('fast');
};

// ========= ТЕСТ

function TEST() { //удалить
	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: { 'action': 'TEST',
				'email': 'asdasd@asdasd.ru'
		}
	});
};



// ========= Проверка данных перед отправкой

function checkEmail(email) {
	var regexp_email = /^\S+@\S+\.\S+$/i;
	if (!regexp_email.test(email)) { return false; };
	return email;
};

function checkPhone(phone) {
	var regexp_phone = /^(?:\d{10,10}|)$/;
	if (!regexp_phone.test(phone)) { return false; };
	return phone;
};

function checkRequestText(title_text, request_text) {

	if (title_text.length < REQUEST_TITLE_MIN_LEN || title_text.length > REQUEST_TITLE_MAX_LEN) {
		showAlert('warning', USRMSG_INCORRECT_TITLE_TEXT);
		return false;
	} else if (request_text.length < REQUEST_TEXT_MIN_LEN || request_text.length > REQUEST_TEXT_MAX_LEN) {
		showAlert('warning', USRMSG_INCORRECT_REQUEST_TEXT);
		return false;
	} else {
		return true;
	};
};

function checkDailyRequestsLimit() {
	var lim = 5;
	var now = new Date();
	now = parseInt(now.getDate());
	if (!localStorage.getItem('old')) {
		localStorage.setItem('old', now);
		console.log('localStorage old CREATED: ' + localStorage.getItem('old')); //удалить
	};
	if (!localStorage.getItem('sent')) {
		localStorage.setItem('sent', 0);
		console.log('localStorage sent CREATED: ' + localStorage.getItem('sent')); //удалить
	};
	var old = parseInt(localStorage.getItem('old'));
	console.log('So, old = ' + old); //удалить
	console.log('and now = ' + now); //удалить
	if (old === now) {
		if (parseInt(localStorage.getItem('sent')) >= lim) {
			$('#send_request').prop('disabled', true);
			showAlert('warning', USRMSG_REQUESTS_LIMIT);
			return false;
		}
	} else {
		console.log('IT\'s A NEW DAY and SENT = ' + localStorage.getItem('sent')); //удалить
		localStorage.setItem('old', now);
		localStorage.setItem('sent', 0);
		localStorage.setItem('sent', parseInt(localStorage.getItem('sent')) + 1);
	}
	$('#send_request').prop('disabled', false);
	return true;
};

function checkUserAgreement() {
	if ($('#user_agreement_checkbox').prop('checked')) {
		$('#send_request').prop('disabled', false);
	} else {
		$('#send_request').prop('disabled', true);
	}
	console.log($('#user_agreement_checkbox').prop('checked')); //удалить
};


// ========= Форматирование данных

function offerPriceFormat(offer_price_from, offer_price_to) {

	if (offer_price_from == 0 && offer_price_to == 0) {

		return '';

	} else if (offer_price_from == offer_price_to) {

		price = offer_price_from + ' руб.';
		return price;

	} else if (offer_price_from > offer_price_to && offer_price_to > 0 ) {

		return '';

	} else if (offer_price_from > 0 && offer_price_to > 0) {

		price = 'от ' + offer_price_from + ' до ' + offer_price_to + ' руб.';
		return price;

	} else if (offer_price_from > 0 && offer_price_to == 0) {

		price = offer_price_from + ' руб.';
		return price;

	} else if (offer_price_from == 0 && offer_price_to > 0) {

		price = offer_price_to + ' руб.';
		return price;

	} else {

		return '';

	};
};

function offerPeriodFormat(offer_period, offer_period_units) {

	if (offer_period == 0 || offer_period == null) {
		return '';
	} else {
		switch (offer_period_units) {
			case null:		return ''; 									break;
			case 0:			return ''; 									break;
			case 60:		return ' за ' + offer_period + ' минут'; 	break;
			case 3600:		return ' за ' + offer_period + ' часов'; 	break;
			case 86400:		return ' за ' + offer_period + ' раб. дн.'; break;
			case 604800:	return ' за ' + offer_period + ' недель'; 	break;
			case 2592000:	return ' за ' + offer_period + ' месяцев'; 	break;	
			default:		return ''; 									break;
		};
	};
};