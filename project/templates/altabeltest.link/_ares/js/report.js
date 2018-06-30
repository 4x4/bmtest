(function(){

	function createReport(e)
	{	
		$('.btn-color-red.ml-auto').removeClass('disabled');	
		 setFlow({reportSaved:true});				 
	}
	
	function closeCallPopup(e)
	{	
		e.preventDefault();
		setFlow({callClosed:true});
		overlayTopWindowClose('closeCall');
	}
	
	
	function clearCreateReportForm()
	{			
		$('#report').find('input,textarea,.checkbox').val('').prop('checked', false).prop('selected', false).removeClass('checked');
	}
	
	
	
	$('.createReport-cancel').click(clearCreateReportForm);	
	$('#closeCallBtn').click(closeCallPopup);	
	$('.createReport-save').click(createReport);

})();