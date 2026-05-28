<?php 
/*
Plugin Name: GRMS コミュニティWP運営のためのぺージパーツ 
Description: GRMS ページテンプレート　入会申請するためのフォーム 等
Version: 3.2
Author: Tsuneyuki Mark Imawaka
Author URI: http://www.cetacea.jp
License: GPLv2 or later
Text Domain: gmrs-page-parts
Domain Path: /languages/

Copyright 2017  Tsueyuki Mark Imawaka (email : mark@cetacea.jp)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'GRMSPAGEPARTSVER', '3.2' );
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//Include admin

include dirname( __FILE__ ) .'/include/grms-template-loader.php';
include dirname( __FILE__ ) .'/include/grms-list-newsletters.php';
include dirname( __FILE__ ) .'/include/grms-list-posts.php';
include dirname( __FILE__ ) .'/include/grms-list-shops.php';
include dirname( __FILE__ ) .'/include/grms-list-events.php';
include dirname( __FILE__ ) .'/include/grms-list-tablepress.php';
include dirname( __FILE__ ) .'/include/grms-wpc-integration.php';
include dirname( __FILE__ ) .'/include/grms-page-parts-admin.php';

add_action('init','add_categories_for_pages'); 
function add_categories_for_pages() { 
   register_taxonomy_for_object_type('category', 'page');
   register_taxonomy_for_object_type('post_tag', 'page');
}
add_filter( 'wpcf7_form_tag_data_option', 'make_options_for_WPCF6', 9, 3 );
function make_options_for_WPCF6( $data, $options, $args) {
    $type = $options[0];
    if( $type == 'GRMS_birth_years' )  {
        $startYear = 1925;
        $thisyear = date('Y');
        $endYear = $thisyear;

        $list = array("年");
        for ( $y = $endYear ; $y >  $startYear ; $y-- ) {
            $label = "";
            $label .= $y;
            $label .= "(";
            $gy = $y;
            if( $y < 1989 ) {
                $label .= '昭和';
                $gy = $y - 1926 + 1;
            } else if( $y < 2019 ) {
                $label .= '平成';
                $gy = $y - 1989 + 1;
            } else {
                $label .= '令和';
                $gy = $y - 2019 + 1;
            }
            if( $gy == 1 ) {
                $gy = '元';
            }
            $label .= $gy;
            $label .= ")年";
            $list[$y] = $label;
        }
        $data = array_merge( (array) $data, $list );
    }
    return $data;
}

add_action( 'wp_enqueue_scripts', 'GRIR_styles_enqueue',11 );
function GRIR_styles_enqueue() {
    wp_enqueue_style( 'GR-IR-styles', plugins_url('include/css/grms-page-parts.css', __FILE__), array(), GRMSPAGEPARTSVER );
    if(has_tag('personal_input')) {
        wp_enqueue_script( 'GR-IR-script', plugins_url('include/js/jquery.autoKana.js', __FILE__), array('jquery'), GRMSPAGEPARTSVER );
        wp_enqueue_script( 'yubinbango', 'https://yubinbango.github.io/yubinbango/yubinbango.js', array(), null, true );
    }
}

add_action( 'admin_enqueue_scripts', 'GRIR_admin_styles_enqueue' );
function GRIR_admin_styles_enqueue( $hook ) {
    wp_enqueue_style( 'GR-IR-datepicker', plugins_url('include/css/bootstrap-datetimepicker.css', __FILE__), array(), GRMSPAGEPARTSVER );
    wp_enqueue_style( 'GR-IR-admin-styles', plugins_url('include/css/grms-admin-page-parts.css', __FILE__), array(), GRMSPAGEPARTSVER );
    wp_enqueue_script( 'GR-IR-moment', plugins_url('include/js/moment-with-locales.min.js', __FILE__), array(), GRMSPAGEPARTSVER );
    wp_enqueue_script( 'GR-IR-datepicker', plugins_url('include/js/bootstrap-datetimepicker.js', __FILE__), array(), GRMSPAGEPARTSVER);
}

add_filter('emanon_footer_custom_powered_by', 'grms_powered_by', 10, 1);
function grms_powered_by ( $powerd_by ) {
    return " powered by Gifu DX committee</a>";
}

add_action("template_redirect", 'grms_template_redirect');
add_filter( 'post_type_link', 'grms_post_type_link', 1, 2 );
// Template selection
function grms_template_redirect()
{
    global $wp;
    global $wp_query;
    
    $template_path = null;

    if (array_key_exists('post_type', $wp->query_vars) ){
        $post_type = $wp->query_vars["post_type"];
        if( $post_type == "grms_shop") {
            $template_path = dirname( __FILE__ ) .'/include/templates/single-grms_shop.php';
        }
        // Let's look for the property.php template file in the current theme
        if ($template_path && have_posts()) {
            include($template_path);
            die();
        } else {
            $wp_query->is_404 = true;
        }
    }
}
function grms_post_type_link( $link, $post ){
  if ( 'grms_shop' === $post->post_type ) {
    return home_url( '/?p=' . $post->ID."&post_type=grms_shop" );
  } else {
    return $link;
  }
}
function get_grms_breadcrumb() {
    
    $microdata_li   = ' itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"';
    $microdata_a    = ' itemprop="item"';
    $microdata_span = ' itemprop="name"';
    $postType       = get_post_type();
    $post_type_obj  = get_post_type_object( $postType ) ;

    $breadcrumb_home_name_type = get_theme_mod( 'breadcrumb_home_name_type', 'site_title' );
    $breadcrumb_home_name = get_theme_mod( 'breadcrumb_home_name', __( 'Home', 'emanon' ) );

    if ( $breadcrumb_home_name ) {
        $name = get_theme_mod( 'breadcrumb_home_name', __( 'Home', 'emanon' ) );
    } else {
        $name = '<span class="display-none">Home</span>';
    }

    if ( $breadcrumb_home_name_type == 'home' ) {
        $bread_home = $name;
    } else {
        $bread_home = get_bloginfo('name');
    }
    $parent_id = null;
    if( $post_type_obj ) {
        $post_type_name = esc_html( $post_type_obj->labels->name );
        $parent_id = $post_type_obj->rewrite['pages'];
    }
    
    $bread_crumb_html = '<!--breadcrumb-->
    <div class="content-inner">
    <nav id="breadcrumb" class="rcrumbs clearfix">
    <ol itemscope itemtype="http://schema.org/BreadcrumbList">';
                      
    $bread_crumb_html .= '<li' . $microdata_li . '><a' . $microdata_a . ' href="' . home_url('/') . '"><i class="fa fa-home"></i><span' . $microdata_span . '>' . $bread_home . '</span></a><i class="fa fa-angle-right"></i><meta itemprop="position" content="1" /></li>';

    $ancestors = array_reverse( get_post_ancestors( $parent_id ) );
    array_push( $ancestors, $parent_id );
    foreach ( $ancestors as $ancestor ) {
        $bread_crumb_html .= '<li'.$microdata_li.'><a'.$microdata_a.' href="'. get_permalink($ancestor) .'"><span'.$microdata_span.'>'. strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ) .'</span></a><i class="fa fa-angle-right"></i><meta itemprop="position" content="2" /></li>';
    }
    $bread_crumb_html .= '<li><span>' . strip_tags( apply_filters( 'single_post_title', get_the_title() ) ) . '</span></li>';
    
    $bread_crumb_html .= '</ol></nav></div><!--end breadcrumb-->';
    return $bread_crumb_html;
}

add_action("phpmailer_init", "grms_send_mail_smtp");
function grms_send_mail_smtp($phpmailer)
{
    $grms_paramname = 'GRMSPagePartsSettings';
    $smtp_options = get_option( $grms_paramname )['smtp_common_setting'];
    $smtphost = $smtp_options['host'];

    if( $smtphost ) {
        $phpmailer->isSMTP();                     //SMTP有効設定
        $phpmailer->SMTPAuth = true;              //SMTP認証の有無（true OR false）
        //        $phpmailer->SMTPSecure = "tls";           //SMTP暗号化方式（ssl OR tls）
        $phpmailer->Host = $smtphost;  //メールサーバーのホスト名
        $phpmailer->Port = $smtp_options['port'];                 //SMTPポート番号(ssl:465 tls:587)
        $phpmailer->Username = $smtp_options['username'];;        //ユーザー名
        $phpmailer->Password = $smtp_options['password'];   //パスワード
        $phpmailer->From = $smtp_options['from'];    //送信者メールアドレス
        error_log($phpmailer->From);
        //  $phpmailer->SMTPDebug = 2;                //デバッグ表示
    }
}
add_action("wp_mail_failed", "grms_send_mail_error");
function grms_send_mail_error($error)
{
    error_log("==== MAIL DEBUG INFO =====");
    error_log(print_r($error->get_error_message(),true));
}

add_filter( 'wpcf7_validate_text*', 'custom_authcode_validation_filter', 20, 2 );
function custom_authcode_validation_filter( $result, $tag ) {
    if ( preg_match('/^grms-auth-code-([1-5])$/', $tag->name, $matches ) ){
        $input_code = isset( $_POST[$tag->name] ) ? trim( $_POST[$tag->name] ) : '';

        $codeparam = 'authcode'.$matches[1][0];
        $grms_paramname = 'GRMSPagePartsSettings';
        $authcode_options = get_option( $grms_paramname )['each_authcode_setting'];
        $auth_code = $authcode_options[$codeparam];
        if ( $input_code != $auth_code ) {
            $result->invalidate( $tag, "認証コードが正しくありません。" );
        }
    }
    return $result;
}
// 認証コードにデフォルトを設定 QRコードへの埋め込みを前提
// http://grms-wordpress/?page_id=1576&authcode=ABC&codeno=1
add_filter('wpcf7_form_tag', 'authcode_form_tag_filter', 11);
function authcode_form_tag_filter($tag){
    if ( ! is_array( $tag ) )
        return $tag;
 
    if(isset($_GET['authcode'])){ //投稿ID
        $authcode = htmlspecialchars($_GET['authcode']);
        $codeno = $_GET['codeno'];
        if( $tag['name'] == 'grms-auth-code-'.$codeno ) {
            $tag['values'] = [$authcode];
        }
    }
    return $tag;
}


