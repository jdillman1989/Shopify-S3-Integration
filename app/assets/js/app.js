$(document).ready(function() {
	function initialState() {
		$.ajax({
			type: 'GET',
			dataType : 'json',
			async: false,
			url: 'get_orders.php',
			data: {},
			complete: function(response) {
				$('#solidShopifyContent').html(response.responseText);
			}
		});
	}

	initialState();

	function pagination(page) {
		$.ajax({
			type: 'GET',
			dataType : 'json',
			async: false,
			url: 'get_orders.php',
			data: {"page": page},
			complete: function(response) {
				$('#solidShopifyContent').html(response.responseText);
			}
		});
	}

	$('#solidShopifyContent').on('click', '#page-next', function() {
		var page = $(this).data('page');
		pagination(page);
	});

	$('#solidShopifyContent').on('click', '#page-previous', function() {
		var page = $(this).data('page');
		pagination(page);
	});

	$('#solidShopifyContent').on('click', '#page-go', function() {
		var input = $(this).siblings('#page-input');
		var page = input.val();
		var lastPage = input.data('last');
		if (parseInt(page) <= parseInt(lastPage)) {
			pagination(page);
		} 
		else{
			$(this).text('Must be less than ' + lastPage);
		};
	});
});