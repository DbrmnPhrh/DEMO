'use strict';

function listenElemEvents() {

	$('.logout').on('click', function() {
		logout();
	});

	$('.close_alert').on('click', function() {
		closeAlert();
	});

	// index.html
	$('#city').on('change', function(){
	    setCity(this.value);
	});

	$('#request_type').on('change', function(){
	    setRequestType(this.value);
	});

	$('#subcategory').on('change', function(){
	    setSubcategory(this.value);
	});

	$('.toggleAuthRegForms').on('click', function(){
		var action = $(this).data('action');
	    toggleAuthRegForms(action);
	});

	$('.toggleCategoriesShowHide').on('click', function(){
		var action = $(this).data('action');
	    toggleCategoriesShowHide(action);
	});

	$('.toggleEmailPhoneDiv').on('click', function(){
		var action = $(this).data('action');
	    toggleEmailPhoneDiv(action);
	});

	$('#user_agreement_checkbox').on('click', function(){
	    checkUserAgreement();
	});

	$('#send_request').on('click', function(){
	    prepareRequest();
	});

	$('.toggleViewOffersEmailPhoneDiv').on('click', function(){
		var action = $(this).data('action');
	    toggleViewQuickOffersEmailPhoneDiv(action);
	});

	$('#viewOffers').on('click', function(){
	    viewOffers();
	});

	$('#log_in').on('click', function(){
	    checkAuthData();
	});

	$('.showHidePasswordRestore').on('click', function(){
		var action = $(this).data('action');
	    showHidePasswordRestore(action);
	});

	$('#reg_btn').on('click', function(){
	    checkRegData();
	});

	$('#restore_pwd').on('click', function(){
	    passwordRestoreCustomer();
	});

	$('#pass_restore_contractor').on('click', function(){
	    passwordRestoreContractor();
	});

	// customer_get_offers.html
	$('#message_send_btn').on('click', function(){
	    sendChatMessage();
	});

	$('#add_offers_btn').on('click', function(){
	    addNewOffersToTable();
	});

	// contractor.html
	$('#contractor_categories').on('change', function(){
		var category_id = $('#contractor_categories').val();
	    setCategory(category_id);
	});

	$('.offerDivShowHide').on('click', function(){
		var action = $(this).data('action');
	    offerDivShowHide(action);
	});

	$('#send_offer_btn').on('click', function(){
	    prepareOffer();
	});

	$('#clear_offer_btn').on('click', function(){
	    clearOfferData();
	});

	$('#add_requests_btn').on('click', function(){
	    addRequestsToTable();
	});

	$('#add_new_requests_automaticaly').on('click', function(){
	    addRequestsAutomaticaly();
	});
};

function listenDynamicElemEvents() {

	// index.html
	$('#customer_categories .customer_category').on('click', function(){
		var category_id = $(this).attr('value');
	    setCategory(category_id);
	});

	// customer_get_offers.html
	$('.history_request').on('click', function(){
		var id = $(this).data('id');
	    showCurrentOffers(id);
	});
};