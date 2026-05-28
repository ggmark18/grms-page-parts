<?php

$grms_shop_type = array (
    'restrant' => '飲食店',
    'shop' => 'ショップ'
);

class GRMSShopRecomendSettings extends AdminPageFramework_PostType {
    protected $_postType = "";  
    function __construct($arg, $parts) {
        parent::__construct($arg);
        $this->_postType = $arg;
        add_filter( 'use_block_editor_for_post', array( $this,'check_block_editor' ), 12, 2 );
    }
    public function check_block_editor( $use_block_editor, $post ) {
        if( $post->post_type == $this->_postType ) {
            return false;
        }
        return $use_block_editor;
    }
    public function name() {
        $base_options = get_option( 'GRMSPagePartsSettings' )['baseinfo_setting'];
        $title = $base_options['shop_recomend_title'] ;
        return $title ? $title : 'ショップ紹介';
    }
    public function setUp() {
        $this->setArguments(
            array( 
                'labels' => array(
                    'name'               => $this->name(),
                    'add_new_item'       => $this->name()
                ),
                //                'hierarchical'      => true,
                //                'supports'          => array( 'title', 'editor', 'thumbnail', 'custom-field', 'page-attributes'),
                'supports'          => array( 'title', 'editor', 'thumbnail', 'custom-field'),
                'public'            => true,
                'menu_icon'         => version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) 
                ? 'dashicons-store' 
                : plugins_url( 'asset/image/shop.png', APFDEMO_FILE ),
            )    
        );    
    }
    
    public function columns_grms_shop( $aHeaderColumns ) {
        return array(
            'cb'    => '<input type="checkbox" />', // Checkbox for bulk actions.
            'title' => 'Title', // Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
            'rating' => '評価',
            'type'  => 'Type', // eat:グルメ、shop:特産品
            'city'  => '地域', // 千代田区、新宿区、千葉県 等
            'url'   => 'Web(HP)'
        );
    }
    
    public function cell_grms_shop_cover( $sCell, $iPostID ) { // cell_{post type}_{column key}
        echo get_the_post_thumbnail( $iPostID, 'thumbnail', array( 'class' => 'alignleft' ) );
    }
    public function cell_grms_shop_content( $sCell, $iPostID ) { // cell_{post type}_{column key}
        echo "<div class='shop-admin-content'>";
        the_content( );
        echo "</div>";
    }
    public function cell_grms_shop_city( $sCell, $iPostID ) { // cell_{post type}_{column key}
        echo get_post_meta($iPostID, 'city', true );
    }
    public function cell_grms_shop_type( $sCell, $iPostID ) { // cell_{post type}_{column key}
        global $grms_shop_type;
        $type = get_post_meta($iPostID, 'type', true );
        if( !empty($type) ) {
            $type = $grms_shop_type[$type];
        }
        return $type;
    }
    public function cell_grms_shop_url( $sCell, $iPostID ) { // cell_{post type}_{column key}
        $url = get_post_meta($iPostID, 'url', true );
        if( !empty($url) ) {
            $url = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
        }
        return $url;
    }
    public function cell_grms_shop_rating( $sCell, $iPostID ) { // cell_{post type}_{column key}
        $rating_string ="";
        $rating_number = get_post_meta($iPostID, 'rating', true );
        for( $i = 0; $i < $rating_number ;$i++) {
            $rating_string .= '<i class="fa fa-star"></i>';
        }
        if( !empty($rating_string) ) {
            $rating_string = '<span style="color:orange;">'.$rating_string.'</span>';
        }
        return $rating_string;
    }
}

class GRMSShopRecomendMetaBox extends AdminPageFramework_MetaBox {

    function __construct() {
        parent::__construct(
            null,   // meta box ID - can be null.
            '店舗情報', // title
            array( 'grms_shop' ),                 // post type slugs: post, page, etc.
            'normal',                            // context
            'low'                               // priority
        );
        add_action( 'save_post', array( $this,'grms_shop_save_post' ), 10, 1 );
    }

    public function grms_shop_save_post( $post_id ){
        $shop = get_post($post_id);
        //        $shop->post_parent = 1373;
        error_log(print_r($shop,true));
    }
  
    public function setUp() {
        global $grms_shop_type;
        $this->addSettingFields(
            array(
                'field_id'  => 'type',
                'title'     => '種類',
                'type'      => 'select',
                'help'      => 'グルメ、特産品などの種類を選択',
                'default'   => 'restrant',
                'label'     => $grms_shop_type
            ),
            array(
                'field_id'  => 'city',
                'title'     => '地域',
                'type'      => 'text',
                'attributes'        => array(
                    'placeholder' => '新宿区、千葉県など'
                )

            ),
            array(
                'field_id'  => 'rating',
                'title'     => '評価',
                'type'      => 'number',
            ),
            array(
                'field_id'  => 'address',
                'title'     => '住所',
                'type'      => 'text',
                'attributes'        => array(
                    'size' => 60,
                )
            ),
            array(
                'field_id'  => 'tel',
                'title'     => '電話（予約）',
                'type'      => 'text',
                'attributes'        => array(
                    'size' => 20,
                )
            ),
            array(
                'field_id'  => 'url',
                'title'     => 'Web（ホームページ）',
                'type'      => 'text',
                'attributes'        => array(
                    'size' => 40,
                )
            ),
        );
    }
}
