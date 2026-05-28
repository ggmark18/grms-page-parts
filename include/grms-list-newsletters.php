<?php
add_shortcode('grms_newsletter_link', 'make_grms_newsletter_link');
function make_grms_newsletter_link($atts, $content = null) {
    $grms_paramname = 'GRMSPagePartsSettings';
    $base_options = get_option( $grms_paramname )['baseinfo_setting'];
    $newsletter_link = $base_options['newsletter_permlink'];
    $newsletter_title = $base_options['newslettertitle'];

    $query = 'post_type=grms_newsletter&posts_per_page=1';
    query_posts( $query );
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();
    $thumbnail_id = get_post_meta(get_the_ID(), '_thumbnail_id', true );
    $url = wp_get_attachment_url($thumbnail_id);
    $url = str_replace( '-scaled.png', '-212x300.png',  $url );
    endwhile; 
    else:
     // 何も取得されなかった
    endif;

    // クエリをリセット
    wp_reset_query();

    $linkbody =<<<ENDOFLINK
<div class="gifunet-link">
<a href="{$newsletter_link}">
<img src="{$url}" class="gifunet-newsletter-img" alt=""/>
		<div class="gifunet-title">{$newsletter_title}</div></a>
  </div>
ENDOFLINK;
  return $linkbody;
}
add_shortcode('grms_newsletter_list', 'make_grms_newsletter_list');
function make_grms_newsletter_list($atts, $content = null) {
    $grms_paramname = 'GRMSPagePartsSettings';
    $base_options = get_option( $grms_paramname )['baseinfo_setting'];
    $number_of_newsletter = $base_options['number_of_newsletter'];
    $postbody = '';
    $query = 'post_type=grms_newsletter&posts_per_page=';
    $query .= $number_of_newsletter;
    // クエリ（メインクエリを改変）
    query_posts( $query );

    // ループ（改変したメインクエリ）
    $np = 0;
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();
    $title = get_the_title();
    $np++;
//    if( $np === 1) {
//        $title .= "<div class='float-right'><font color='red'>最新号</font></div>";
//    }
    $thumbnail_id = get_post_meta(get_the_ID(), '_thumbnail_id', true );
    $newsletter_id = get_post_meta(get_the_ID(), '_newsletter_id', true );
    $urlorg = wp_get_attachment_url($thumbnail_id);
    $url = str_replace( '.png', '-550x550.png',  $urlorg  );
    $handle = @fopen($url, 'r');
    if( !$handle ) {
        $url = $urlorg;
    }
    $pdf = wp_get_attachment_url($newsletter_id);
    $content = get_the_content();
    $pdficon = plugins_url('/../asset/image/PDF-Icon.png',__FILE__);
    $download = get_the_title() . ".pdf";
    $postbody .= <<<ENDOFPOST
  <div class="row">
    <div class="col-5 newsletter-img text-center">
       <a href="{$pdf}" target="_blank"><img src="{$url}" width="150px"></img></a>
    </div>
    <div class="col-7 px-0">
    <H3 class="newsletter-title">{$title}</H3>
    <div class="newsletter-content">
    {$content}
    </div>
    <P><P class="float-right"><a href="{$pdf}" download="{$download}"><img src="{$pdficon}" width="30px"></img> Download</a>
    </div>
  </div>
ENDOFPOST;

  endwhile; 
  else:
  // 何も取得されなかった
  endif;

  // クエリをリセット
  wp_reset_query();

  return $postbody;
}

add_shortcode('grms_newsletter_backnumber', 'make_grms_newsletter_backnumber');
function make_grms_newsletter_backnumber($atts, $content = null) {
    $grms_paramname = 'GRMSPagePartsSettings';
    $base_options = get_option( $grms_paramname )['baseinfo_setting'];
    $number_of_newsletter = $base_options['number_of_newsletter'];
    $number_of_col = 3;
    $postbody = '';
    // クエリ（メインクエリを改変）
    query_posts( 'post_type=grms_newsletter' );

    // ループ（改変したメインクエリ）
    $np = 0;
    $bp = 0;
    if ( have_posts() ) : 
        while ( have_posts() ) : the_post();
    
    if ( $np++ < $number_of_newsletter ) {
        continue;
    }

    $title = get_the_title();
    $thumbnail_id = get_post_meta(get_the_ID(), '_thumbnail_id', true );
    $newsletter_id = get_post_meta(get_the_ID(), '_newsletter_id', true );
    $urlorg = wp_get_attachment_url($thumbnail_id);
    $url = str_replace( '.png', '-550x550.png',  $urlorg  );
    $handle = @fopen($url, 'r');
    if( !$handle ) {
        $url = $urlorg;
    }
    $pdf = wp_get_attachment_url($newsletter_id);
    $content = get_the_content();
    $pdficon = plugins_url('/../asset/image/PDF-Icon.png',__FILE__);
    $download = get_the_title() . ".pdf";
    if( $bp%$number_of_col === 0 ) $postbody .= '<div class="row">';
    $postbody .= '<div class="col text-center">';
    $postbody .= '<a href="'.$pdf.'"target="_blank"><img src="'.$url.'" width="70px"></img></a>';
    $postbody .= '<a href="'.$pdf.'" download="'.$download.'">';
    $postbody .= '<div class="newsletter-backnumber-title">'.$title.'&nbsp;<i class="fas fa-download"></i></div></a>';
    $postbody .= '</div>';
    if( $bp%$number_of_col === $number_of_col-1 ) $postbody .= '</div>';
    $bp++;
  endwhile; 
  else:
  // 何も取得されなかった
  endif;

  // クエリをリセット
  wp_reset_query();

  return $postbody;
}
