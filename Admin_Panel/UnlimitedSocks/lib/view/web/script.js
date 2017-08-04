$(document).ready(function() {
	jQuery(document).ready(function($) {
		$("button[name='Manager']").on('click',function() {
			layer.open({
				type: 2,
				title: $(this).attr('data-type'),
				offset: 'auto',
				closeBtn: 1,
				shadeClose: true,
				maxmin: true, //开启最大化最小化按钮
				content: ['product.php?id=' + $(this).attr('data-id'), 'no']
			});
		});
	});
});	