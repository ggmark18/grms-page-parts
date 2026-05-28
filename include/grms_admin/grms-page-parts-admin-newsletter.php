<?php
class GRMSNewsLetterSettings extends AdminPageFramework_PostType {
    protected $_postType = "";
    function __construct($arg, $parts) {
        parent::__construct($arg);
        //        $base_options = get_option( 'GRMSPagePartsSettings' )['baseinfo_setting'];
        //        print $base_options['title'];
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
        $title = $base_options['newslettertitle'] ;
        return $title ? $title : '会報誌';
    }
    
    public function setUp() {
        $this->setArguments(
            array( // argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
                'labels' => array(
                    'name'               => $this->name(),
                    'add_new_item'       => $this->name() 
                ),
                'supports'          => array( 'title', 'editor', 'thumbnail', 'custom-field'),
                'public'            => true,
                'menu_icon'         => version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) 
                ? 'dashicons-media-document' 
                : plugins_url( 'asset/image/newsletter.png', APFDEMO_FILE ),
            )    
        );    
    }
    
    public function columns_grms_newsletter( $aHeaderColumns ) {
        return array(
            'cb'    => '<input type="checkbox" />', // Checkbox for bulk actions.
            'title' => 'Title', // Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
            'cover'   => '表紙',
            'content' => '内容', 
            'post'    => '関連記事', 
            'date'    => '日付'
        );
    }
    
    public function cell_grms_newsletter_cover( $sCell, $iPostID ) { // cell_{post type}_{column key}
        echo get_the_post_thumbnail( $iPostID, 'thumbnail', array( 'class' => 'alignleft' ) );
    }
    public function cell_grms_newsletter_content( $sCell, $iPostID ) { // cell_{post type}_{column key}
        echo "<div class='newsletter-admin-content'>";
        the_content( );
        echo "</div>";
    }
    public function cell_grms_newsletter_post( $sCell, $iPostID ) { // cell_{post type}_{column key}
        $newsletter_post_id = get_post_meta($iPostID, '_newsletter_post_id', true );
        if ( $newsletter_post_id ) {
            $plink = get_permalink($newsletter_post_id);
            $handle = @fopen($plink, 'r');
            if( $handle ) {
                echo '<span width="100%"><a href="'.$plink.'" target="_blank">投稿</a></span>';
            }
        }
    }
}

class GRMSNewsLetterMetaBox extends AdminPageFramework_MetaBox {

    function __construct() {
        parent::__construct(
            null,   // meta box ID - can be null.
            '会報誌 情報', // title
            array( 'grms_newsletter' ),                 // post type slugs: post, page, etc.
            'normal',                                   // context
            'low'                                       // priority
        );
        add_filter( 'add_attachment', array( $this,'grms_newsletter_attachment' ), 11, 1 );
        // 呼び出しが再帰無限ループにならないように、呼び出しの中で制御 huck!!!!
        add_action( 'save_post', array( $this,'grms_newsletter_save_post' ), 11, 1 );
        add_action( 'save_post', array( $this,'grms_newsletter_make_post' ));

        add_image_size( 'newsletter-thumbnail',  550, 550, true );
    }
  
    public function setUp() {
        
        $this->addSettingFields(
            array(
                'field_id'  => 'newsletter_file',
                'title'     => 'PDF',
                'type'      => 'file'
            ),
            array(
                'field_id'          => 'newsletter_upload',
                'label'             => 'Upload',
                'type'              => 'submit',
                'show_title_column' => false,
                'label_min_width'   => '',
                'attributes'        => array(
                    'field' => array(
                        'style' => 'float:left; width:auto;',
                    ),                   
                ),
            )
        );
    }

    public function do_GRMSNewsLetterMetaBox() {

        $thumbnail_id = get_post_meta(get_the_ID(), '_thumbnail_id', true );
        $newsletter_id = get_post_meta(get_the_ID(), '_newsletter_id', true );
        if( $thumbnail_id && $newsletter_id) {
            $url = wp_get_attachment_url($thumbnail_id);
            $pdf = wp_get_attachment_url($newsletter_id);
            echo '<span><a href="'.$pdf.'" target="_blank"><img src="'.$url.'" width="200px"></img></a></span>';
        }
        $newsletter_post_id = get_post_meta(get_the_ID(), '_newsletter_post_id', true );
        if ( $newsletter_post_id ) {
            $plink = get_permalink($newsletter_post_id);
            $handle = @fopen($plink, 'r');
            if( $handle ) {
                echo '<span width="100%"><a href="'.$plink.'" target="_blank">紹介投稿記事作成済</a></span>';
            }

        }
        submit_button('記事作成','primary','newsletter_post_make');
    }

    public function script_GRMSNewsLetterMetaBox( $script ) {
        return $script . file_get_contents(__DIR__.'/../js/newsletter-submit.js');
    }
    public function import_mime_types_GRMSNewsLetterMetaBox( $arrMIMETypes ) {  // import_mime_types_ + {page slug}
		$arrMIMETypes[] = 'application/pdf';
		return $arrMIMETypes;
    }

    public function grms_newsletter_save_post( $post_id ){
        if(isset($_POST['newsletter_upload']) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
      
            $pdf_attachment_id = media_handle_upload('newsletter_file', $post_id);

            if ( is_wp_error( $pdf_attachment_id ) ) {
                grms_error_die($pdf_attachment_id->get_error_message(), __FILE__, __LINE__);
                $pdf_attachment_id = false;
            } else {
                $thumbnail_id = get_post_meta( $pdf_attachment_id, '_thumbnail_id', true );
                update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );
                update_post_meta( $post_id, '_newsletter_id', $pdf_attachment_id );
            }
        }
    }
    
    public function grms_newsletter_make_post( $post_id ){
        if(isset($_POST['newsletter_post_make']) ) {

            $idObj = get_category_by_slug( 'gifunet');
            $cid = $idObj->term_id;
            $newsletter = get_post($post_id);
            $newsletter_id = get_post_meta( $post_id, '_newsletter_id', true );
            $pdf = wp_get_attachment_url($newsletter_id);
            $pdficon = plugins_url('/../../asset/image/adobe-pdf-icon.png',__FILE__);
            $content = '<a class="float-right" href="'.$pdf.'" target="_blank"><img src="'.$pdficon.'" width="30px"></img>PDFを開く</a>';
            $content .= '<h4><B>目次</B></h4><ul><li style="list-style-type: none;">';
            $content .= $newsletter->post_content;
            $content .= '</li></ul>';
            $download = $newsletter->post_title . ".pdf";
            $downloadicon = plugins_url('/../../asset/image/PDF-Icon.png',__FILE__);
            $content .= '<P><P class="float-right"><a href="'.$pdf.'" download="'.$download.'"><img src="'.$downloadicon.'" width="30px"></img> Download</a></P></P>';
            $newsletter_post = array(
                'post_title' => $newsletter->post_title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_category' => array($cid)
            );
            remove_action( 'save_post', array( $this,'grms_newsletter_make_post' ));
            $nspost_id = wp_insert_post( $newsletter_post );
            // update_post_meta( $nspost_id, '_wp_page_template', 'templates/newsletter-page.php' );
            // set_post_format($nspost_id,'newsletter');

            $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
            if ( $thumbnail_id ){ 
                update_post_meta( $nspost_id, '_thumbnail_id', $thumbnail_id );
            }
            update_post_meta( $post_id, '_newsletter_post_id', $nspost_id );
            add_action( 'save_post', array( $this,'grms_newsletter_make_post' ));
            
        }
        return $post_id;
    }

    public function grms_newsletter_attachment( $attachment_id ){ // Generate thumbnail from PDF
        if ( get_post_mime_type( $attachment_id ) === 'application/pdf' ){
            $thumbnail_id = get_post_meta( $attachment_id, '_thumbnail_id', true );
            if ( $thumbnail_id ){ // delete ex thumb 
                $ex_file = get_attached_file( $thumbnail_id );
                $meta = wp_get_attachment_metadata( $thumbnail_id );
                
                if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
                    $uploadpath = wp_get_upload_dir();
                    foreach ( $meta['sizes'] as $size => $sizeinfo ) {
                        $intermediate_file = str_replace( basename( $ex_file ), $sizeinfo['file'], $ex_file );
                        wp_delete_file( path_join( $uploadpath['basedir'], $intermediate_file ) );
                    }
                };
                wp_delete_file( $ex_file );
            }
            $new_file = $this->grms_letter_cover_generate( $attachment_id );

            if ( file_exists( $new_file ) ){ // new thumb
                $file_title = esc_attr( get_the_title( $attachment_id ) );
                $attachment = get_post( $attachment_id );
                $filetype = wp_check_filetype( basename( $new_file ), null );
                $new_thumb = array( 
                    'post_type' => 'attachment',
                    'post_mime_type' => $filetype['type'],
                    'post_title' => $file_title,
                    'post_excerpt' => $attachment->post_excerpt,
                    'post_content' => $attachment->post_content,
                    'post_parent' => $attachment_id,
                    'guid' => dirname($attachment->guid). '/' .basename( $new_file )
                );
                if ( $thumbnail_id ){ // if regenerating, overwite ex thumb ID.
                    $new_thumb['ID'] = $thumbnail_id;
                    wp_update_post( $new_thumb );
                    update_attached_file( $thumbnail_id, $new_file );
                } else { // create new attachment
                    $thumbnail_id = wp_insert_attachment( $new_thumb, $new_file );
                    update_post_meta( $thumbnail_id, '_wp_attachment_image_alt', sprintf( __( 'thumbnail of %s', 'pdf-image-generator' ), $file_title ) ); 
                    update_post_meta( $attachment_id, '_thumbnail_id', $thumbnail_id );
                }
                $metadata = wp_generate_attachment_metadata( $thumbnail_id, $new_file );
                if ( !empty( $metadata ) && !is_wp_error( $metadata ) ) {
                    wp_update_attachment_metadata( $thumbnail_id, $metadata );
                }
                $return = $thumbnail_id;
            } 
        }
        if ( empty( $return ) ) $return = false;
        
        return $return;
    }
    
    public function grms_letter_cover_generate( $attachment_id ){ // Generate thumbnail from PDF
        set_time_limit( 0 );
        $image_type = 'png';
        $max_width = 1024;
        $max_height = 1024;
        $setReso = 128;
        $quality = 80;
        $image_bgcolor = 'white';
        
        $file = get_attached_file( $attachment_id );
        $new_filename = sanitize_file_name( str_replace( '.pdf', '-pdf', basename( $file ) ) ).'.'.$image_type;
        $new_filename = wp_unique_filename( dirname( $file ), $new_filename );

        $base_options = get_option( 'GRMSPagePartsSettings' )['baseinfo_setting'];
        $maxwidth = $base_options['width_max_thumbnail'] ;
        $picwidth = $base_options['width_pic_thumbnail'] ;

        $file_url = str_replace( basename( $file ), $new_filename, $file );
        // -extent オプションに内部で使用されるcompositeで合成脱色するケースがあるため
        // 足りない横幅を計算し、spliceで埋めることで回避する。
        // $resize = "-thumbnail 640x640 -gravity north -extent 640x640";
        $resize = "-thumbnail ".$picwidth."x".$picwidth;

        //PDFファイルから表紙をPNG抽出後、 identify コマンドで横幅を抽出
        $imageMagick = "PATH=${PATH}:/usr/bin:/usr/local/bin; convert -density {$setReso} {$file}[0] {$resize} {$file_url}; identify -format \"%w\" {$file_url}";
        exec( $imageMagick, $output, $return ); // Convert pdf to image
        //$width = shell_exec( $imageMagick ); // Convert pdf to image
        //    $width = system( $imageMagick ); // Convert pdf to image and check image width
        if( $return === 0 ) {
            $width = $output[0];
            $gap = $maxwidth - (int)$width;

            if( $gap > 0 ) {
                $lest = $gap % 2;
                if( $lest > 0 ) {
                    $gap = $gap - 1;
                }
                $left_gap = (int)$gap/2;
                $right_gap = (int)$gap/2;
                $left_gap = $left_gap + $lest;
                
                $imageMagick = "PATH=${PATH}:/usr/bin:/usr/local/bin; convert {$file_url} -background none -gravity northwest -splice {$left_gap}x0 -gravity northeast -splice {$right_gap}x0 {$file_url}";
                exec( $imageMagick, $output, $return ); // Convert pdf to image and check image width
                if ( $return !== 0 ) {
                    error_log( "PNG resize convert is failed : {$file_url}" );
                    $file_url = null;
                }
            }
        } else {
            error_log( "pdf->PNG convert is failed : {$file}[0]" );
            $file_url = null;
        }
        return $file_url;
    }
}

