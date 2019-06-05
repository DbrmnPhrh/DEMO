'use strict';

// var NET_HOST
// var NET_SOCKET_PORT
var CHAT_TEXT_MIN_LEN = 2;
var CHAT_TEXT_MAX_LEN = 600;
var USRMSG_CHAT_INCORRECT_TEXT = 'Сообщение должно быть от '+CHAT_TEXT_MIN_LEN+' до '+CHAT_TEXT_MAX_LEN+' символов';

// ========= Сокеты

// function getSocketParameters() {
// 	$.ajax({
// 		url: 'php/index.php',
// 		type: 'GET',
// 		data: {
// 			'action': 'get_socket_parameters'
// 		},
// 		success: function(json) {
// 			var socket_parameters = $.parseJSON(json);
// 			NET_HOST = socket_parameters['host'];
// 			NET_SOCKET_PORT = socket_parameters['socket_port'];
// 			socketCreate();
// 		},
// 		error: function() {
// 			ajaxErrorHandler();
// 		}
// 	});
// };

// function socketCreate() {
// 	if (NET_HOST && NET_SOCKET_PORT) {
// 		socket = new WebSocket('ws://' + NET_HOST + ':' + NET_SOCKET_PORT);
// 		socket.onopen = socketOnOpen;
// 		socket.onmessage = socketOnMessage;
// 		socket.onerror = socketOnError;
// 		socket.onclosed = socketOnClosed;
// 	} else {
// 		showAlert('warning', 'Не удаётся инициализировать чат. Попробуйте перезагрузить страницу!');
// 	};
// };

// function socketOnOpen() {
// 	showAlert('success', 'Чат готов к работе!');
// };

// function socketOnMessage(e) {
// 	console.log(e.data);
// };

// function socketOnError() {
// 	showAlert('warning', 'Ошибка сокета!');
// };


// function socketOnClosed() {
// 	showAlert('info', 'Сокет закрыт!');
// };

// function socketSend(data) {
// 	socket.send(data);
// };

function getChatMessages() {

	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: {
			'action': 'get_chat_msg'
		},
		success: function(json) {
			var data = JSON.parse(json);
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function prepareChatMessage() {

	var chat_message = $('#message_text').val();

	if (checkChatMessage(chat_message)) {
		sendChatMessage(chat_message);
	};
};

function checkChatMessage(chat_message) {

	if (chat_message.length < 1) {
		showAlert('warning', USRMSG_CHAT_EMPTY_MESSAGE);
		return false;
	} else if (chat_message.length < CHAT_TEXT_MIN_LEN || chat_message.length > CHAT_TEXT_MAX_LEN) {
		showAlert('warning', USRMSG_CHAT_INCORRECT_TEXT);
		return false;
	} else {
		return true;
	};
};

function sendChatMessage(chat_message) {

	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: {
			'city_id': window.city_id,
			'category_id': window.category_id,
			'action': 'add_chat_msg',
			'chat_msg': chat_message,
			'request_id': window.request_id
		},
		success: function(json) {
			var data = JSON.parse(json);
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};