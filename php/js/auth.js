function checkRegData() {
	var email = $.trim($('#reg_email').val());
	var password = $('#reg_password').val();
	var regexp_pass = /^[a-zA-Z0-9]{6,20}$/i;

	if (!checkEmail(email)) {
		showAlert('warning', USRMSG_INCORRECT_EMAIL);
		return;
	} else if (email.length < 8 || email.length > 90) {
		showAlert('warning', USRMSG_INCORRECT_EMAIL_LENGTH);
		return;
	} else if (!regexp_pass.test(password)) {
		showAlert('warning', USRMSG_INCORRECT_PASS);
		return;
	} else if (!checkRegLimit()) {
		showAlert('warning', 'Количество регистраций с одного IP адреса ограничено! Пожалуйста используйте существующий аккаунт');
		return;
	} else {
		showAlert('info', 'Регистрация...');
		sendRegData(email, password);
	};
};

function checkRegLimit() {
	var lim = 2438290 - 2438270;
	if (!localStorage.getItem('reg')) {
		localStorage.setItem('reg', 0);
		console.log('localStorage reg CREATED: ' + localStorage.getItem('reg')); //удалить
	};
	if (parseInt(localStorage.getItem('reg')) >= lim) {
		return false;
	};
	return true;
};

function sendRegData(email, password) {
	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: {
				'city_id': window.city_id,
				'user_type': window.user_type,
				'action': 'reg',
				'reg_email': email,
				'reg_password': password
		},
		success: function(json) {
			data = $.parseJSON(json);
			showAlert(data['msg_type'], data['msg']);
			localStorage.setItem('reg', parseInt(localStorage.getItem('reg')) + 1);
			if (data['msg_type'] === 'success') { setTimeout(function(){ window.location.replace(data['url']); }, LOCATION_REPLACE_TIMEOUT) };
		},
		error: function() {
			ajaxErrorHandler();
		}
	});
};

function checkAuthData() {
	var email = $.trim($('#email').val());
	var password = $('#password').val();
	var regexp_pass = /^[a-zA-Z0-9]{6,20}$/i;

	if (!checkEmail(email)) {
		showAlert('warning', USRMSG_INCORRECT_EMAIL);
		return;
	}
	if (email.length < 8 || email.length > 90) {
		showAlert('warning', USRMSG_INCORRECT_EMAIL_LENGTH);
		return;
	}
	if (!regexp_pass.test(password)) {
		showAlert('warning', USRMSG_INCORRECT_PASS);
		return;
	};
	showAlert('info', 'Авторизация...');
	sendAuthData(email, password);
};

function sendAuthData(email, password) {
	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: {
				'action': 'auth',
				'email': email,
				'password': password
		},
		success: function(json) {
			var data = $.parseJSON(json);
			if (data['msg_type'] === 'success') {
				showAlert(data['msg_type'], data['msg']);
				setTimeout(function(){
								if (data['user_type'] === 'customer') {
									window.location.replace(data['url']);
								} else {
									window.location.replace(data['url']);
								};
							}, LOCATION_REPLACE_TIMEOUT);
			} else {
				showAlert(data['msg_type'], data['msg']);
				$('#password').val('');
			};
		}, error: function() {
			ajaxErrorHandler();
		}
	});
};

function logout() {
	showAlert('info', 'Выход из личного кабинета...');
	$.ajax({
		url: 'php/index.php',
		type: 'POST',
		data: { 'action': 'logout' },
		success: function(json) {
			var data = $.parseJSON(json);
			setTimeout(function(){ window.location.replace(data['url']); }, LOCATION_REPLACE_TIMEOUT);
		}
	});
};

function getCurrentUser() {
	var currentUser = $.ajax({
		url: 'php/index.php',
		type: 'GET',
	    async: false,
		data: { 'action': 'get_current_user' },
	}).responseText;

	return $.parseJSON(currentUser);
};

function showHidePasswordRestore(action) {
	if (action === 'show') {
		$('#password_restore_div').fadeIn('fast');
	} else if (action === 'hide') {
		$('#password_restore_div').fadeOut('fast');
	};
};

function passwordRestoreCustomer() {

	emailToRestore = $.trim($('#email_to_restore').val());
	
	if (!checkEmail(emailToRestore)) {
		
		showAlert('warning', 'Неверный формат email!');
		return false;

	} else {

		$.ajax({
			url: 'php/index.php',
			type: 'POST',
			data: { 'action': 'pwd_restore',
					'email': emailToRestore
			},
			success: function(json) {
				data = $.parseJSON(json);
				showAlert(data['msg_type'], data['msg']);
				// if (data['msg_type'] === 'success') { setTimeout(function(){ window.location.replace(data['url']); }, LOCATION_REPLACE_TIMEOUT) };
				// $('#email_to_restore').val(''); //раскомментить
			}, error: function() {
				ajaxErrorHandler();
			}
		});
	};
};

function passwordRestoreContractor() {
	if(!$('#pass_restore_contractor').prop('checked')) {
		$('#password_restore_contractor_message').fadeOut('fast');
		$('#password_restore_form').fadeIn('fast');
	} else {
		$('#password_restore_form').fadeOut('fast');
		$('#password_restore_contractor_message').fadeIn('fast');
	};
};