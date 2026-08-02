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
        if ( $newsletter_post_id && get_post_status( $newsletter_post_id ) === 'publish' ) {
            $plink = get_permalink( $newsletter_post_id );
            if ( $plink ) {
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
        if ( $newsletter_post_id && get_post_status( $newsletter_post_id ) === 'publish' ) {
            $plink = get_permalink( $newsletter_post_id );
            if ( $plink ) {
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
            $this->grms_newsletter_toc_fill( $attachment_id );
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

    // PDFの「Index」ページを解析し、会報誌投稿の本文(post_content)が未入力の場合のみ
    // 目次のドラフトを自動で流し込む。抽出結果は完全ではないため、公開前に管理者の確認を前提とする。
    public function grms_newsletter_toc_fill( $attachment_id ) {
        $newsletter_post_id = wp_get_post_parent_id( $attachment_id );
        if ( ! $newsletter_post_id ) {
            return false;
        }
        $newsletter = get_post( $newsletter_post_id );
        if ( ! $newsletter || trim( wp_strip_all_tags( $newsletter->post_content ) ) !== '' ) {
            return false; // 既に本文が入力されている場合は上書きしない
        }

        $toc_html = $this->grms_letter_toc_extract( $attachment_id );
        if ( ! $toc_html ) {
            return false;
        }

        // wp_update_post() は同じ投稿に対して save_post を再度発火させる。
        // ここは save_post ハンドラ(PDFアップロード処理)の実行中に呼ばれるため、
        // ガードなしで更新すると同一リクエスト内でアップロード処理が二重実行され、
        // 一時ファイルが消費済みになった2回目の media_handle_upload() が失敗する。
        remove_action( 'save_post', array( $this, 'grms_newsletter_save_post' ), 11 );
        remove_action( 'save_post', array( $this, 'grms_newsletter_make_post' ) );
        wp_update_post( array(
            'ID'           => $newsletter_post_id,
            'post_content' => $toc_html,
        ) );
        add_action( 'save_post', array( $this, 'grms_newsletter_save_post' ), 11, 1 );
        add_action( 'save_post', array( $this, 'grms_newsletter_make_post' ) );
        return true;
    }

    // PDFから「Index」見出し以降に並ぶ目次項目を抽出し、<ul><li>...</li></ul> のHTMLを返す。
    // 見つからない、または抽出に失敗した場合は空文字を返す。
    public function grms_letter_toc_extract( $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        if ( ! $file || ! file_exists( $file ) ) {
            return '';
        }

        $path = getenv( 'PATH' ) . ':/usr/local/bin:/usr/bin:/opt/homebrew/bin';
        $exportPath = "export PATH={$path};";
        $marker = 'Index';
        $max_pages = 6; // 目次は通常誌面の先頭付近にあるため探索範囲を限定する

        $toc_lines = array();
        for ( $page = 1; $page <= $max_pages; $page++ ) {
            $cmd = "{$exportPath} gs -sDEVICE=txtwrite -dFirstPage={$page} -dLastPage={$page} -o - -q " . escapeshellarg( $file ) . " 2>&1";
            exec( $cmd, $lines, $return );
            if ( $return !== 0 || empty( $lines ) ) {
                continue;
            }
            $marker_index = null;
            foreach ( $lines as $i => $line ) {
                if ( trim( $line ) === $marker ) {
                    $marker_index = $i;
                    break;
                }
            }
            if ( $marker_index !== null ) {
                $toc_lines = array_slice( $lines, $marker_index + 1 );
                break;
            }
        }

        if ( empty( $toc_lines ) ) {
            error_log( "PDF toc extract: 'Index' marker not found in first {$max_pages} pages : {$file}" );
            return '';
        }

        $items = array();
        foreach ( $toc_lines as $line ) {
            $item = $this->grms_letter_toc_clean_line( $line );
            if ( $item !== '' ) {
                $items[] = $item;
            }
        }
        if ( empty( $items ) ) {
            return '';
        }

        $html = '<ul>';
        foreach ( $items as $item ) {
            $html .= '<li>' . esc_html( $item ) . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    // 目次1行分のノイズ除去: 末尾のページ番号を切り落とし、
    // 文字間隔の描画崩れで紛れ込んだ余分な半角スペースを取り除く。
    private function grms_letter_toc_clean_line( $line ) {
        // タブ・全角スペース等の連続を半角スペース1つに正規化
        $line = preg_replace( '/[\t\x{3000}]+/u', ' ', $line );
        $line = trim( $line );
        if ( $line === '' ) {
            return '';
        }
        // 末尾の「スペース+ページ番号(全角/半角、間にスペースが入ることもある)」を除去
        $line = preg_replace( '/[\s0-9\x{FF10}-\x{FF19}]+$/u', '', $line );
        $line = trim( $line );
        if ( $line === '' ) {
            return '';
        }

        // 一部の見出しはフォントの都合で文字ごとにスペースが挿入されるため、
        // スペース出現率が高い(ほぼ1文字ごとに空白がある)行だけ、日本語文字間のスペースを除去する
        $jp_class = '[\x{3000}-\x{303F}\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}\x{FF00}-\x{FFEF}]';
        preg_match_all( '/' . $jp_class . '/u', $line, $jp_matches );
        $jp_count = count( $jp_matches[0] );
        $space_count = substr_count( $line, ' ' );
        if ( $jp_count > 0 && $space_count >= $jp_count * 0.5 ) {
            $line = preg_replace( '/(?<=' . $jp_class . ') (?=' . $jp_class . ')/u', '', $line );
        }
        return $line;
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

        // getenv('PATH') は環境によって /usr/local/bin や /opt/homebrew/bin (macOS/Homebrew) を
        // 含まない場合があるため、代表的なパスを補って convert/identify を探索できるようにする。
        // 単なる "PATH=...;" の代入では convert 内部から呼ばれる delegate (Ghostscript 等) の
        // 子プロセスにPATHが継承されないため、必ず export する。
        $path = getenv( 'PATH' ) . ':/usr/local/bin:/usr/bin:/opt/homebrew/bin';
        $exportPath = "export PATH={$path};";

        //PDFファイルから表紙をPNG抽出
        $convertCmd = "{$exportPath} convert -density {$setReso} {$file}[0] {$resize} {$file_url} 2>&1";
        exec( $convertCmd, $convertOutput, $convertReturn ); // Convert pdf to image

        if ( $convertReturn === 0 ) {
            // convert の出力(非推奨警告など)がwidthの値と混ざらないよう、identifyは別実行にする
            $identifyCmd = "{$exportPath} identify -format \"%w\" {$file_url} 2>&1";
            exec( $identifyCmd, $output, $return ); // 横幅を抽出
        } else {
            $return = $convertReturn;
            $output = $convertOutput;
        }

        if( $return === 0 ) {
            $width = (int) $output[0];
            $gap = $maxwidth - $width;

            if( $gap > 0 ) {
                $lest = $gap % 2;
                if( $lest > 0 ) {
                    $gap = $gap - 1;
                }
                $left_gap = (int)$gap/2;
                $right_gap = (int)$gap/2;
                $left_gap = $left_gap + $lest;

                $imageMagick = "{$exportPath} convert {$file_url} -background none -gravity northwest -splice {$left_gap}x0 -gravity northeast -splice {$right_gap}x0 {$file_url} 2>&1";
                exec( $imageMagick, $output, $return ); // Convert pdf to image and check image width
                if ( $return !== 0 ) {
                    error_log( "PNG resize convert is failed : {$file_url} : " . implode( ' / ', (array) $output ) );
                    $file_url = null;
                }
            }
        } else {
            error_log( "pdf->PNG convert is failed : {$file}[0] : " . implode( ' / ', (array) $output ) );
            $file_url = null;
        }
        return $file_url;
    }
}

