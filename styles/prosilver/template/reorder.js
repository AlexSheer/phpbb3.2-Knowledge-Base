$(function() {
	$('#sortable').sortable({
		axis: 'y',
		opacity: 0.7,
		handle: '#move',
		containment: '#forumbg',
		update: function(event, ui) {
			var list_sortable = $(this).sortable('toArray').toString();
			var page = $('#page').text();
			// change order in the database using Ajax
			$.ajax({
				url: 'set_order',
				type: 'POST',
				data: {list_order:list_sortable, page:page},
				success: function(data) {
				//finished
				}
			});
		}
	}); // fin sortable
});
