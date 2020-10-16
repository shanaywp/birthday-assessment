jQuery(document).ready( function( $ ){
    jQuery('#send_email').on('click', function(e){
        e.preventDefault();
        jQuery.ajax({
            type : "post",
            // dataType : "json",
            url : urlAjax.ajaxurl,
            data : {action: "send_user_birthday"},
            error: function( res ){ 
                console.log(res);
            },
            success: function(response) {
               if(response == "10") {
                    alert("Email Sent Success");
               }
               else {
                  alert("Email Not sent Because Already Sent, No New users Found");
               }
            }
         })  
    });

    jQuery('.reset_date').on('click', function(e){
        e.preventDefault();
        jQuery('#realm_api_text_field_3').val('');
    });
    
});