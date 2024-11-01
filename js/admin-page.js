jQuery(document).ready(function(){
	//Tabs
    jQuery('.tabs li').click(function(){
        jQuery('.tabs li').removeClass('active');
        jQuery(this).addClass('active');
         
        var num_active = jQuery('.tabs').find('.active').index();
        jQuery('.tabs_divs div').removeClass('active');
        jQuery('.tabs_divs div').eq(num_active).addClass('active');
    }); 
	
	jQuery("#userchat_cf7_title").change(function(){
		value = jQuery(this).val();
		jQuery("#userchat_cf7_id").attr('value', value);
	});
});

