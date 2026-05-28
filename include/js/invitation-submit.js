(function($){
    $.fn.autoKana('#val_lastname', '#val_last_kana');
    $.fn.autoKana('#val_firstname', '#val_first_kana');
    $('#val_postcode').zip2addr({
	    addr:'#val_address'
    })
    $( "#ndinvrequestform" ).submit(function( event ) {
        event.preventDefault();

        var $form = $( this );
        
//        if ( $form[0].checkValidity() ) {
            $('#agree_Contract').prop('checked', false);
            $('#send_request').prop('disabled', true);
            $('#invitationConfirm').modal('show');
            $('#ndi-cf-kana').html($form.find("#val_last_kana").val()+" "+$form.find("#val_first_kana").val());
            $('#ndi-cf-name').html($form.find("#val_lastname").val()+" "+$form.find("#val_firstname").val());
            $('#ndi-cf-birth').html($form.find("#val_birth_year").val()
                                    +"/"+("0"+$form.find("#val_birth_month").val()).slice(-2)
                                    +"/"+("0"+$form.find("#val_birth_day").val()).slice(-2));
            $('#ndi-cf-email').html($form.find("#val_email").val());
            $('#ndi-cf-postcode').html("〒"+$form.find("#val_postcode").val());
            $('#ndi-cf-address').html($form.find("#val_address").val());
            $('#ndi-cf-homeschool').html($form.find("#val_homeschool").val());
            $('#ndi-cf-homecity').html($form.find("#val_homecity").val());
            $('#ndi-cf-job').html($form.find("#val_job").val());
            $('#ndi-cf-family').html($form.find("#val_family").val());
            $('#ndi-cf-invitation').html($form.find("#val_invitation").val());
            $('#ndi-cf-comment').html($form.find("#val_comment").val());
    });
})(jQuery);

function sendRequest() {
    (function($){

        $('#invitationConfirm').modal('hide');
        
        var $form = $( "#ndinvrequestform" )
        var url = $form.attr( "action" );
        var plugindir = $form.attr( "plugin-dir" );
        var sorryimg = plugindir+"/asset/image/sorry.png";

        // Send the data using post
        var posting = $.post( url, $form.serializeArray() );

        // Put the results in a div
        posting.done(( data ) => {
            
            if( data.result == 'OK' ) {
                $form[0].reset();
                /*
                $form.find("textarea").val("");
                $form.find(":text").val("");
                $form.find(".ndi-email").val("");
                $form.find(".ndi-select").val("");
                */
            } else {
                $('#confirm-dialog-title').html('<div class="float-left"><img src="'+sorryimg+'"></img></div><div class="float-left mt-3"><font size="6">申し訳ありません。</font></div>');
                $('#invitation-confirm-message').html('<P><i class="fa fa-warning"></i>ご意見ご要望が正しく送られていない可能性があります。<BR>事務局にお問い合わせください。</P>');
            }
            $('#invitationResponse').modal('show');
        }).fail(( err ) => {
            $('#confirm-dialog-title').html('<div class="float-left"><img src="'+sorryimg+'"></img></div><div class="float-left mt-3"><font size="6">申し訳ありません。</font></div>');
            $('#invitation-confirm-message').html('<P><i class="fa fa-warning"></i>ご意見ご要望が正しく送られていない可能性があります。<BR>事務局にお問い合わせください。</P>');
            $('#invitationResponse').modal('show');
        });

    })(jQuery);
}

function agreeContract(value) {
    (function($){
        $('#send_request').prop('disabled', !value);
    })(jQuery);
}

function getAddress(zip) {
    (function($){
        var re = /[0-9][0-9][0-9]-?[0-9][0-9][0-9][0-9]/; 
        if ( re.exec(zip) ) {
            $.getJSON( `https://maps.googleapis.com/maps/api/geocode/json`,
                       {
                           address : zip,
                           language : 'ja',
                           sensor : false
                       },function ( resp ) {
                           if(resp.status == "OK"){
                               // APIのレスポンスから住所情報を取得
                               var obj = resp.results[0].address_components;
                               if (obj.length < 5) {
                                   return false;
                               }
                               var address = obj[3]['long_name']; // 都道府県
                               address += obj[2]['long_name'];  // 市区町村
                               address += obj[1]['long_name']; // 番地
                               $('#val_address_label').addClass('active');
                               $('#val_address').val(address);
                           }
                       });
        }
    })(jQuery);
}

