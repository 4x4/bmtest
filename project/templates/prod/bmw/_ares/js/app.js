$(document).foundation();

$(document).ready(function() {
	// $('select:not([multiple])').styler();
	$("select[multiple]").select2();
	historyAccardion();
	itemAccardion();
	historySuccsec();
	dropdown();
	drowShow();

	$('#customerDaP').click(function(event) {
		$(this).toggleClass('is-active');
		$(this).next('.accardion-content').toggleClass('open');
		mainHeight();
	});

	$('#favoritesAccardion').click(function(event) {
		$('.favorites-mini').toggle();
		$(this).toggleClass('is-active');
		$(this).next('.accardion-content').toggleClass('open');
	});

	$('.accardion-block-header').click(function(event) {
		$(this).next('.accardion-block-content').slideToggle('fast');
	});

	$('.main-tabs').click(function(event) {
		$('.block-report').removeClass('is-active');
	});

	$('#reportTabs').click(function(event) {
		$('.block-report').addClass('is-active');
	});

	$('#fileReport').click(function(event) {
		$('.overlay-top').addClass('is-active');
		$('#reportSuccess').addClass('is-active');
	});



	$(".checkbox input").click(function() {
		$(this).parents('.col-row').children('.col-2_7').children('.filter-item').children('.checkbox').removeClass('checked');
		$(this).parents('.checkbox').toggleClass('checked');
	});

$("#history .checkbox input").click(function() {
		$(this).parents('.dropdown-content').find('.checkbox').removeClass('checked');
		$(this).parents('.checkbox').toggleClass('checked');
	});
	$(".col-2_5 .checkbox input").click(function() {
		$(this).parents('.col-row').children('.col-2_5').find('.checkbox').removeClass('checked');
		$(this).parents('.checkbox').toggleClass('checked');
	});

	$(".col-3_5 .checkbox input").click(function() {
		$(this).parents('.col-row').children('.col-3_5').find('.checkbox').removeClass('checked');
		$(this).parents('.checkbox').toggleClass('checked');
	});

	$('.col-3_5 .checkbox input').click(function(event) {
		$('#otherArea').attr('disabled','true');
	});

	$('#otherComment').click(function() {
		$('#otherArea').removeAttr('disabled');
	});

});

function drowShow() {
	$('#dropdownShow').click(function() {
		var nameDrop = $(this).parents('.dropdown-content').find('.checked').data('index');
		$('#dropdown').text(nameDrop);
	});
}

/*Scale*/
$(window).resize(function() {
	autoHeight();
});

function dropdown() {
	$('#dropdown').click(function() {
		$(this).next('.dropdown-content').toggle();
	});
}
/*AutoHeight*/
function autoHeight() {
	var heightDisplay = $(window).height();
		heightHeader = $('.header').height();
		heightMain = heightDisplay - heightHeader;
		heightBlock1 = $('#firstBlock').height();
		heightBlock2 = $('#secondBlock').height();
		blockScrool = heightMain - heightBlock1 -heightBlock2 - 120;

	$('.block-scroll').css('height', blockScrool );
}

function mainHeight() {
	var heightDisplay = $(window).height();
		heightHeader = $('.header').height();
		heightMain = heightDisplay - heightHeader;
		heightBlock1 = $('#firstBlock').height();
		heightBlock2 = $('#secondBlock').height();

}

$(document).ready(function() {
	updateFavorites();
	disabledSend();
	autoHeight();
});


	$(document).on('click', '.close-poi',function(event) {
	var namePois = $(this).parents('.pois-item').children('.pois-name').text();		
	$('#removeFavorites').children('.modal-title').children('span').text(namePois);
	

	
});

$('.close-favorites').click(function(event) {
	var nameFav = $(this).parents('.favorites-modal-item').find('.favorites-names').text();
		thisPois = $(this);
	$('#deleteFavorites').children('.modal-title').children('span').text(nameFav);
	modalRemoveFav();

	$('#deleteFavorites .deleteFavorites').click(function() {
		$(thisPois).parents('.col-1_3').remove();
	});
});




/*MODAL*/
$(document).ready(function() {
	$('.modal-close,#success .btn,#successHistory .btn, #reportSuccess .btn, .modal-cancel, #closeCallMoreBtn').click(function(event) {
		$(this).parents('.modal').removeClass('is-active');
		$(this).parents('.overlay').removeClass('is-active');
		$(this).parents('.overlay-top').removeClass('is-active');
	});

	$('#success .btn').click(function() {
		$('.pois-item').remove();
		disabledSend();
		updateFavorites();
	});

	$('#sendSuccess').click(function(event) {
		$('.overlay-top').addClass('is-active');
		$('#success').addClass('is-active');
	});

	$('#manFavorites').click(function(event) {
		$('.overlay').addClass('is-active');
		$("#manageFavorites").addClass('is-active');
	});

	$('#closeCallOpen').click(function() {
		closeCall();
	});
});

function historyAccardion() {
	$('.table_row-header').click(function() {
		$(this).parent('.table_row').toggleClass('is-active');
	});
}

function itemAccardion() {
	$('.item_accardion-btn').click(function() {
		$(this).toggleClass('is-active');
		$(this).next('.item_accardion').slideToggle('fast');
	});
}

function historySuccsec() {
	$('#resendHistory').click(function() {
		$('.overlay-top').addClass('is-active');
		$('#successHistory').addClass('is-active');
	});
}

function closeCall() {
	if ($('#foundPous').children('.pois-item').length > 1) {
			$('.overlay-top').addClass('is-active');
			$('#closeCallMore').addClass('is-active');
		} else{
			$('.overlay-top').addClass('is-active');
			$('#closeCall').addClass('is-active');
		}
}

function updateFavorites() {
	var countPois = $('#foundPous').children('.pois-item').length;
		freePois = 5 - countPois;

	$('.countPois').text(countPois);
	$('.freePois').text(freePois);
}

function disabledSend() {
	if ($('#foundPous').children('.pois-item').length < 1) {
		$('#foundPous').children('.flex').children('.btn').addClass('disabled');
	}
}

function modalRemovePoi() {
	$('.overlay-top').addClass('is-active');
	$('#removeFavorites').addClass('is-active');
	$('#removeFavorites .removeFavorites').click(function(event) {
		$(this).parents('#removeFavorites').removeClass('is-active');
		$(this).parents('.overlay-top').removeClass('is-active');
	});
}

function modalRemoveFav() {
	$('.overlay-top').addClass('is-active');
	$('#deleteFavorites').addClass('is-active');
	$('#deleteFavorites .deleteFavorites').click(function(event) {
		$(this).parents('#deleteFavorites').removeClass('is-active');
		$(this).parents('.overlay-top').removeClass('is-active');
	});
}

$('input').keyup(function() {
	var count = $(this).val().length;
	$(this).next('.form-item-count').children('span').text(count);
});

$('textarea').keyup(function() {
	var count = $(this).val().length;
	$(this).next('.form-item-count').children('span').text(count);
});

$('#createPOI').click(function(event) {
	$('.overlay').addClass('is-active');
	$('#creatPoi').addClass('is-active');
});

$('.edit-poi').click(function(event) {
	$('.overlay').addClass('is-active');
	$('#editPoi').addClass('is-active');
});

$('.edit-favorites').click(function(event) {
	$('.overlay-top').addClass('is-active');
	$('#editFavorites').addClass('is-active');
});

/**/
$(document).ready(function() {
	var nowPosition = $('#nowPosition').text();
	$('.now-posiotion').val(nowPosition);

	$('.reset-find').click(function(event) {
		$(this).parents('.form').children('.form-row').children('.select2').children('.selection').children('.select2-selection--multiple').children('.select2-selection__rendered').children('.select2-selection__choice').remove();
	});

	function tic() {
		for (i = 0; i < 30; i++) {
			$('#pin').addClass('red');
			$('#pin').text(i);
		}
	}

	/*printNumbersInterval();
	function printNumbersInterval() {
		var i = 1;
		var timerId = setInterval(function() {

		$('#pin').addClass('red');
		$('#pin').text(i);

		if (i == 20) clearInterval(timerId);
			i++;
		}, 60000);
	}*/
});

