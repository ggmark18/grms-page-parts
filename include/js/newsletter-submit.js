(function($){
    // type="file" を有効にするためには、formのenctypeを以下のように設定する必要があるためHUCK.
    $('#post').attr('enctype','multipart/form-data');
    $("#newsletter_upload__0").prop("disabled", true);
    $( "#newsletter_file__0" ).change(function( event ) {
        if(event.target.files.length > 0) {
            $("#newsletter_upload__0").prop("disabled", false);
        }
    });
})(jQuery);

