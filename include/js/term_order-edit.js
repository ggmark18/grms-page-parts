(function($) {
    $('.editinline').live('click', function(){
        var $order = $( '.column-term_order', "#term_order" ).html();
        $( ':input[name="term_order"]', '.inline-edit-row' ).val( $order );
        return false;
    });
})(jQuery);

