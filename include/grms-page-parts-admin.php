<?php

function grms_error_die( $error_message, $file, $line ){
    $message = '<H3><i class="fa fa-bomb"></i> GRMS WP運営機能プラグインエラー</H3>';
    $message .= '<P>'.$error_message.'</P>';
    $message .= '<P>'.$file.':'.$line.'</P>';
    $message .= '<HR><a href="javascript:history.back()">[戻る]</a>';
    error_log($error_message.'('.$file.':'.$line.')');
    wp_die($message);
}


include dirname( __FILE__ ) .'/../library/apf/admin-page-framework.php';
include dirname( __FILE__ ) .'/grms_admin/grms-page-parts-admin-baseinfo.php';
include dirname( __FILE__ ) .'/grms_admin/grms-page-parts-admin-smtpserver.php';
include dirname( __FILE__ ) .'/grms_admin/grms-page-parts-admin-authcode.php';


class GRMSPagePartsSettings extends AdminPageFramework {

    use BaseInfoParts;
    use SMTPServerParts;
    use AuthCodeParts;

  public function setUp() {
    // Create the root menu
    $this->setRootMenuPage(
      'GRMS運営機能',    // specify the name of the page group
      'dashicons-admin-site-alt2'
    );   
                           
    // Add the sub menu item
    $this->addSubMenuItems(
      array(
        'title'  => '基本情報',        
        'page_slug'     => 'baseinfo'    
      ),
      array(
        'title'  => 'SMTP設定',        // page title
        'page_slug'     => 'smtpserver'    // page slug
      ),
      array(
        'title'  => '認証コード設定',     // page title
        'page_slug'     => 'authcode'    // page slug
      )
    );
  }
    
}

include dirname( __FILE__ ) .'/grms_admin/grms-page-parts-admin-newsletter.php';
include dirname( __FILE__ ) .'/grms_admin/grms-page-parts-admin-shops.php';


if( is_admin() ) {
    load_plugin_textdomain(
        "grms-page-parts",
        false,
        plugin_basename( dirname( __FILE__ ) ) . '/languages' 
    );
    $all_settings = get_option('GRMSPagePartsSettings', []);
    $base_options = $all_settings['baseinfo_setting'] ?? [];

    $grms_parts_settings_page = new GRMSPagePartsSettings();

    if ( ! empty($base_options['newslettertitle']) ) {
        $grms_newsletter_settings_page = new GRMSNewsLetterSettings('grms_newsletter', $grms_parts_settings_page);
        $grms_newsletter_metabox = new GRMSNewsLetterMetaBox();
    }
    $shop_page_id = $base_options['shop_page_id'] ?? null;
    if ( $shop_page_id ) {
        $grms_shop_settings_page = new GRMSShopRecomendSettings('grms_shop', $grms_parts_settings_page);
        $grms_shop_metabox = new GRMSShopRecomendMetaBox();
    }
}

?>
