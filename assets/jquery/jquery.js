jQuery(document).ready(function() {
	
	jQuery('.nav-tab-wrapper-azrcrv-pi .nav-tab').on('click',function(event) {
		var item_to_show = '.azrcrv_pi_tabs' + jQuery(this).data('item');

		jQuery(this).siblings().removeClass('nav-tab-active');
		jQuery(this).addClass("nav-tab-active");
		
		jQuery(item_to_show).siblings().css('display','none');
		jQuery(item_to_show).css('display','block');
	});
	
});