(function($){
	$(document).ready(function(){
		$('.datetimepicker_yyyymmdd').datetimepicker({
			format: 'YYYY/MM/DD',
			locale: 'ja',
			sideBySide:true
		});
		$('.datetimepicker_yyyymmddHHmmss').datetimepicker({
			format: 'YYYY/MM/DD HH:mm:ss',
			locale: 'ja',
			sideBySide:true
		});
		$('#button1').click(function(){
			$.ajax({
				url: location.href+"/get"
				}).done(function( msg ) {
				alert( "データ保存: " + msg );
			});
		});
		var table = $('table');
		table.floatThead({
			scrollContainer: function(table){
				return table.closest('#scrollTable');
				}
		});
	});
})(jQuery);