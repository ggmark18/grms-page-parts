(function($){

    $('#btn_suggestion_sending').hide();
    $( "#ndsendsuggestionform" ).submit(function( event ) {
        // Stop form from submitting normally
        event.preventDefault();
    
        $('#requestConfirm').modal('hide');
        
        // Get some values from elements on the page:
        var $form = $( this );
        var url = $form.attr( "action" );

        // Send the data using post
        var posting = $.post( url, $form.serializeArray() );
        $('#btn_suggestion').prop('disabled', true);
        $('#btn_suggestion_send').hide();
        $('#btn_suggestion_sending').show();

        // Put the results in a div

        posting.done(( data ) => {
            if( data.result == 'OK' ) {
                $form[0].reset();
            } else {
                $('#request-confirm-message').html('<P><i class="fa fa-warning"></i>ご意見ご要望が正しく送られていない可能性があります。<BR>事務局にお問い合わせください。</P>');
            }
            $('#requestConfirm').modal('show');
            $('#btn_suggestion_send').show();
            $('#btn_suggestion_sending').hide();
            $('#btn_suggestion').prop('disabled', false);
        }).fail(( err ) => {
            $('#request-confirm-message').html('<P><i class="fa fa-warning"></i>ご意見ご要望が正しく送られていない可能性があります。<BR>事務局にお問い合わせください。</P>');
            $('#requestConfirm').modal('show');
            $('#btn_suggestion_send').show();
            $('#btn_suggestion_sending').hide();
            $('#btn_suggestion').prop('disabled', false);
        });
    });
})(jQuery);

