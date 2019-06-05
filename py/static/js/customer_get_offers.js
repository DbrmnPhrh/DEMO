'use strict';

$(document).ready(function() {

	window.current_user = getCurrentUser();

	if (window.current_user['user_type'] === 'guest') {

		console.log('window.current_user '+window.current_user['user_type']+' '+ (window.current_user['user_email'] ? window.current_user['user_email'] : '(no_email)'));
		prepareGuestPage();

	} else if (window.current_user['user_type'] === 'customer') {

		console.log('window.current_user '+window.current_user['user_type']+' '+ (window.current_user['user_email'] ? window.current_user['user_email'] : '(no_email)'));
		prepareCustomerPage();

	};

	// ========= Предварительные настройки
	getRequestsHistory();
	initializeOffersTable();
	setInterval(function(){ getOffers(window.request_id); }, NEW_OFFERS_GET_INTERVAL);
});


// ========= Формирование элементов интерфейса

function prepareGuestPage() {

	console.log('preparing guest page'); //удалить
	$('#user').html('');
	$('#you_logged_in_as').fadeIn('fast');
};

function prepareCustomerPage() {

	console.log('preparing customer page: '+window.current_user['user_email']); //удалить
	$('#user').html('Вы вошли как: ' + window.current_user['user_email']);
	$('#you_logged_in_as').fadeIn('fast');
};

// ========= Формирование истории запросов

function getRequestsHistory() {

	$.ajax({
		url: 'php/index.php',
		type: 'GET',
		data: {
			'action': 'get_requests_history',
			'user_email': localStorage.getItem('user_email'),
			'user_phone': localStorage.getItem('user_phone')
		},
		success: function(json) {

			window.history_data = $.parseJSON(json);
			window.history_data = window.history_data['data'].reverse();
			var request_date;
			var title_text;
			var is_closed;

			for (var i = 0; i < window.history_data.length; i++) {
				request_date = moment(window.history_data[i][0]).format('DD.MM.YYYY HH:mm');
				title_text = window.history_data[i][4];
				is_closed = window.history_data[i][7] ? 'закрыт' : '';
				$('#requests_div').append('<span><b>' + (i+1) + '.</b><a id=request_' + (i+1) + ' href="" class="history_request" data-id='+i+'>' + request_date + '</a><span> '+title_text+'</span><b> ' + is_closed + '</b></span><br>');
				if (is_closed) { $('#request_'+(i+1)).css( {"background-color":"red"} ) };
			};

			preventDefault();
			listenDynamicElemEvents();
			showCurrentOffers(0);
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

// ========= Получение предложений по текущему запросу

function initializeOffersTable() {

	window.offer_last_id = 0;
	window.total_new_offers = 0;

	window.offersTable = $('#offers_table').DataTable({
		language: {"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Russian.json"},
		destroy: true,
		order: [[ 1, 'desc' ]],
		pageLength: 10,
		columnDefs: [
						{ targets: 0,
						  type : 'date',
						  render: function(data)
						  { return moment(data).format("DD.MM.YYYY HH:mm"); }
						},
						{ targets: 1, visible: false },
						{ targets: 4, render: $.fn.dataTable.render.ellipsis( 35 ) }
					],
		scrollX: true
	});
};

function showCurrentOffers(request_num) {

	// Данные с сервера:
	// 0 $result_array['request_created_at']
	// 1 $result_array['category_name']
	// 2 $result_array['request_type_name']
	// 3 $result_array['subcategory_name']
	// 4 $result_array['title_text']
	// 5 $result_array['request_text']
	// 6 $result_array['request_id']
	// 7 $result_array['is_closed']

	var request_date = moment(window.history_data[request_num][0]).format("DD.MM.YYYY HH:mm");
	var category_name  = window.history_data[request_num][1];
	var type_and_subcategory = window.history_data[request_num][2]+' '+window.history_data[request_num][3];
	var title_text   = window.history_data[request_num][4];
	var request_text = window.history_data[request_num][5];
	var request_id   = window.history_data[request_num][6];
	var is_closed    = window.history_data[request_num][7];

	window.offersTable.clear();
	window.offer_last_id = 0;
	window.total_new_offers = 0;
	window.request_id = request_id;

	showCurrentRequestText(category_name,
						   request_id,
						   request_date,
						   type_and_subcategory,
						   title_text,
						   request_text,
						   is_closed);

	getOffers(request_id, true);
};

// Функция showCurrentRequestText() отображает текст текущего запроса,
// чтобы Клиент всегда видел какой запрос он отправил
function showCurrentRequestText(category_name,
						   		request_id,
								request_date,
								type_and_subcategory,
								title_text,
								request_text,
								is_closed) {
	$('#category_name').html(category_name);
	$('#request_id').html(request_id);
	$('#request_date').html(request_date);
	$('#type_and_subcategory').html(type_and_subcategory);
	$('#title_text').html(title_text);
	$('#request_text').html(request_text);
	if (is_closed) { showTooltip("current_request_div", "ПОЛУЧЕНИЕ ПРЕДЛОЖЕНИЙ ЗАВЕРШЕНО!");}
	else { showTooltip("current_request_div", "ИДЁТ ПОЛУЧЕНИЕ ПРЕДЛОЖЕНИЙ!");};
};

function getOffers(request_id, add_to_table) {

	$.ajax({
		url: 'php/index.php',
		type: 'GET',
		data: {
			'action': 'get_offers_for_request',
			'request_id': request_id,
			'offer_last_id': window.offer_last_id
		},
		success: function(json) {
			var data = JSON.parse(json);
			var offer_data = prepareOfferData(data);
			window.offer_last_id = data['offer_last_id'];
			window.offersTable.rows.add(offer_data);
			updateAddButtonValue(data['data'].length);
			if (add_to_table) { addNewOffersToTable(); };
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function prepareOfferData(offer_data) {

	for (var i=0; i<offer_data['data'].length; i++) {

		offer_data['data'][i][3] = offerPriceFormat(
								   Number(offer_data['data'][i][3]),
								   Number(offer_data['data'][i][4]))
								   +' '+
								   offerPeriodFormat(
								   Number(offer_data['data'][i][5]),
								   Number(offer_data['data'][i][6]));
		offer_data['data'][i][4] = offer_data['data'][i][7];
	};

	return offer_data['data'];
};

function updateAddButtonValue(new_offers) {

	window.total_new_offers = window.total_new_offers + new_offers;
	$('#add_offers_btn').val('Добавить предложения ' + '('+ window.total_new_offers + ')');
};

function addNewOffersToTable() {

	window.offersTable.draw();
	window.total_new_offers = 0;
	updateAddButtonValue(0);
};