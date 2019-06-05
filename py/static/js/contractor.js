'use strict';

$(document).ready(function() {

	window.current_user = getCurrentUser();

	if (window.current_user['user_type'] === 'contractor') {

		console.log('window.current_user '+window.current_user['user_type']+' '+ (window.current_user['user_email'] ? window.current_user['user_email'] : '(no_email)'));
		$('#user').html(window.current_user['user_email']);

	} else {

		location.replace(URL_INDEX_PAGE);

	};

	// ========= Предварительные настройки
	prepareContractorPage();
	getCategories();
	resetRequestsTableSettings();
	initializeRequestsTable();
	initializeTableRowsClick();
});


// ========= Формирование элементов интерфейса

function prepareContractorPage() {

};

function getCategories() {

	$.ajax({
		url: 'php/index.php',
		type: 'GET',
		data: {
			'action': 'get_categories'
		},
		success: function(json) {
			var categories = $.parseJSON(json);
			for (var i = 0; i < categories.length; i++) {
				$('#contractor_categories').append('<option value='+categories[i][0]+'>' + categories[i][1] + '</option>');
				console.log(categories[i][0]+' '+categories[i][1]);
			};
			console.log('categories[0][0]: '+categories[0][0]);
			setCategory(categories[0][0]);
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function setCategory(category_id) {

		requestsTimer('stop');
		window.category_id = category_id;
		console.log('category_id: '+category_id); //удалить
		console.log('window.category_id: '+window.category_id); //удалить
		window.requestsTable.clear().draw();
		clearOfferData();
		resetRequestsTableSettings();
		getRequests(true);
};

function resetRequestsTableSettings() {

	window.request_last_id = 0;
	window.total_new_requests = 0;
	moment.locale('ru');
};

function initializeRequestsTable() {

	window.requestsTable = $('#requests_table').DataTable({
		language: {
			"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Russian.json",
			searchPlaceholder: CONTRACTOR_REQ_TABL_SEARCH_PLACEHOLDER
		},
		destroy: true,
		order: [[ 1, 'desc' ]],
		pageLength: 5,
		columnDefs: [
						{ targets: 0,
						  type : 'date',
						  render: function(data)
						  { return moment(data).format("DD.MM.YYYY HH:mm"); }
						},
						{ targets: 1, visible: true },
						{ targets: 5, render: $.fn.dataTable.render.ellipsis( 25 ) },
						{ targets: 6, visible: true },
						{ targets: 7, visible: true }
					],

		scrollX: true
	});

};

// Данные с сервера:
// 0_$result_array['request_created_at']
// 1_$result_array['request_id']
// 2_$result_array['request_type_name']
// 3_$result_array['subcategory_name']
// 4_$result_array['title_text']
// 5_$result_array['request_text']
// 6_$result_array['email']
// 7_$result_array['phone']
// 8_$last_offer['price_from']
// 9_$last_offer['price_to']
// 10_$last_offer['period']
// 11_$last_offer['period_units']
// 12_$result_array['is_closed']

function initializeTableRowsClick() {

	$('#requests_table tbody').on('click', 'tr', function() {
		window.cellData = window.requestsTable.row(this).data();
		requestInfoFill(window.cellData[2],
						window.cellData[3],
						window.cellData[4],
						window.cellData[5],
						window.cellData[6],
						window.cellData[7]
		);
		offerDivShowHide('show');
		window.request_id = window.cellData[1];
		window.is_request_closed = window.cellData[8];
		window.row_indx = window.requestsTable.row(this).index();
		window.cell_indx = 6;
	});
};

function updateAddButtonValue(new_requests) {

	window.total_new_requests = window.total_new_requests + new_requests;
	$('#add_requests_btn').val('Добавить новые запросы ' + '('+ window.total_new_requests + ')');
};

// ========= Получение запросов за последнюю неделю

function getRequests(add_to_table) {

	$.ajax({
		url: 'php/index.php',
		type: 'GET',
		data: {
				'category_id': window.category_id,
				'action': 'get_requests',
				'request_last_id': window.request_last_id
		},
		success: function(json) {
			var data = JSON.parse(json);
			var offer_data = prepareOfferData(data);
			window.request_last_id = data['request_last_id'];
			window.requestsTable.rows.add(offer_data);
			updateAddButtonValue(offer_data.length);
			requestsTimer('start');
			if (add_to_table) { addRequestsToTable(); };
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function requestsTimer(action) {

	if (action = 'start') {
		$('#requests_table').stopTime('requestsTimer');
		$('#requests_table').everyTime(NEW_REQUESTS_GET_INTERVAL, 'requestsTimer', function() {
	 		getRequests();
		});
	} else if (action = 'stop') {
		$('#requests_table').stopTime('requestsTimer');
	};
};

// Данные после подготовки:
// 0_$result_array['request_created_at']
// 1_$result_array['request_id']
// 2_$result_array['request_type_name']
// 3_$result_array['subcategory_name']
// 4_$result_array['title_text']
// 5_$result_array['request_text']
// 6_$result_array['email']
// 7_$result_array['phone']
// 8_$last_offer['price_from']+$last_offer['price_to']+$last_offer['period']+$last_offer['period_units']
// 12_$result_array['is_closed']

function prepareOfferData(offer_data) {

	for (var i=0; i<offer_data['data'].length; i++) {

		offer_data['data'][i][8] = offerPriceFormat(
								   Number(offer_data['data'][i][8]),
								   Number(offer_data['data'][i][9]))
								   +' '+
								   offerPeriodFormat(
								   Number(offer_data['data'][i][10]),
								   Number(offer_data['data'][i][11]));

		offer_data['data'][i][9] = offer_data['data'][i][12] ? 'закрыт' : '';
	};

	return offer_data['data'];
};

function addRequestsAutomaticaly() {

	if ($('#add_requests_automaticaly').prop("checked")) {

		$('#add_requests_automaticaly').everyTime(1000, 'add_requests_automaticaly_timer', function() {

 			addRequestsToTable();

		});

	} else if (! $('#add_requests_automaticaly').prop("checked")) {

		$('#add_requests_automaticaly').stopTime('add_requests_automaticaly_timer');

	};
};

function addRequestsToTable() {

	window.requestsTable.draw();
	window.total_new_requests = 0;
	updateAddButtonValue(0);
};

function addSentOfferToTable(price_from, price_to, period, period_units, offer_text) {

	if (window.is_request_closed) { return; };
	var offer_price_format = offerPriceFormat(price_from, price_to);
	var offer_period_format = offerPeriodFormat(period, period_units);
	window.requestsTable.cell(window.row_indx,window.cell_indx).data(offer_price_format+' '+offer_period_format).draw();
};


// ========= Формирование интерфейса подачи предложения

function offerDivShowHide(action) {
	if (action === 'show') {
		clearOfferData();
		$('#offer_div').fadeIn('fast');
	} else if (action === 'hide') {
		$('#offer_div').fadeOut('fast');
	};
};

function requestInfoFill(request_type, subcategory, title_text, request_text, customer_email, customer_phone) {
	$('#request_type').html(request_type);
	$('#subcategory').html(subcategory);
	$('#title_text').html(title_text);
	$('#request_text').html(request_text);
	if (customer_email && customer_phone) {
		var customer_contact = customer_email+' / '+customer_phone;
	} else if (customer_email) {
		var customer_contact = customer_email;
	} else if (customer_phone) {
		var customer_contact = customer_phone;
	};
	$('#customer_contact').html(customer_contact);
};


// ========= Подготовка предложения

function prepareOffer() {
	var price_from = Number($('#price_from').val());
	var price_to = Number($('#price_to').val());
	var period = Number($('#period').val());
	var period_units = Number($('#period_units').val());
	var offer_text = $('#offer_text').val();
	var request_id = window.cellData[1];
	if (checkOfferData(price_from, price_to, period, period_units, offer_text)) {
		showAlert('info', 'Отправка предложения...');
		console.log('Отправка предложения: ' + price_from + ' - ' + price_to + ', ' + period + ', ' + period_units + ', ' + offer_text); //удалить
		sendOffer(price_from, price_to, period, period_units, offer_text, request_id);
	};
};


// ========= Очистка предложения

function clearOfferData() {
	$('#price_from').val('');
	$('#price_to').val('');
	$('#period').val('');
	$('#period_units').val(86400);
	$('#offer_text').val('');
};


// ========= Проверка предложения

function checkOfferData(price_from, price_to, period, period_units, offer_text) {

	var regexp_price = /^(?:\d{0,7}|)$/;
	var regexp_period = /^(?:\d{0,2}|)$/;
	if (price_from === 0 &&
		price_to === 0 &&
		period === 0 &&
		(offer_text.length == 0 || offer_text.length > OFFER_TEXT_MAX_LEN)) {
		showAlert('warning', 'Для подачи предложения должно быть заполнено хотя бы одно поле');
		return;
	} else if (!regexp_price.test(price_from) || 
               !regexp_price.test(price_to) ||
               !regexp_period.test(period)) {
		showAlert('warning', 'Неверный формат данных в полях стоимости или в поле сроков');
		return;
	} else if (parseInt(price_to) > 0 && parseInt(price_from) > parseInt(price_to)) {
		showAlert('warning', 'Стоимость "от" не может быть больше стоимости "до"');
		return;
	} else if (period * period_units > 31104000) {
		showAlert('warning', 'Неверный формат данных в полях стоимости или в поле сроков');
		return;
	} else {
		return true;
	};
};


// ========= Отправка предложения

function sendOffer(price_from, price_to, period, period_units, offer_text, request_id) {

	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: {
				'category_id': window.category_id,
				'action': 'add_offer',
				'price_from': price_from,
				'price_to': price_to,
				'period': period,
				'period_units': period_units,
				'offer_text': offer_text,
				'request_id': request_id
		},
		success: function(json) {
			var data = $.parseJSON(json);
			showAlert(data['msg_type'], data['msg']);
			addSentOfferToTable(price_from, price_to, period, period_units, offer_text);
			clearOfferData(); //раскомментить
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};