<?php
class GRMS_ShopList {
    function __construct($shop_page_id, $shop_page_name) {
        if( !is_admin() ) {

            register_post_type('grms_shop', array(
                'publicly_queryable' => true,
                'labels' => array(
                    'name'               => $shop_page_id
                ),
                'rewrite' => array(
                    'pages'              => $shop_page_name
                ),
            ));
        }
        add_shortcode('grms_shop_list', array( &$this, 'shop_list'));
        add_shortcode('grms_shop_article', array( &$this, 'shop_article'));
        add_shortcode('grms_shop_sns', array( &$this, 'shop_sns'));
    }

    function shop_list( $attr ) {
        $content = "";
        $post_args = array();
        $post_args['post_type'] = "grms_shop";
        $article_id = null;
        $cat_attr = false;
        if( $attr ) {
            if( array_key_exists('type', $attr) ) {
                $type_attr = $attr['type'];
                $post_args['meta_query'] = array(
                    'key' => 'type',
                    'value'   => $type_attr,
                    'compare' => '=');
                $post_args['meta_key'] = 'type';
            }
            if( array_key_exists('article_id', $attr) ) {
                $article_id = $attr['article_id'];
            }
        }

        $post_query = new WP_Query( $post_args );

        if ( $post_query->have_posts() ) {
            while ( $post_query->have_posts() ) {
                $content .= '<div class="row grms-active-article">';
                $post_query->the_post();
                $post_id = get_the_id();
                $post_title = get_the_title();
                
                $content .= '<div class="col-sm-3 grms-active-thumbnail px-0" ><a href="?&p='.$post_id.'&post_type=grms_shop">';
                $imgid = get_post_thumbnail_id();
                $imageurl = wp_get_attachment_image_src($imgid, 'small-thumbnail' );
                $content .= '<img src="'.$imageurl[0].'" height="120px" class="mb-0">';
                $content .= '</a></div>';
                
                $content .= '<div class="col-sm-9 grms-active-posts">';
                
                $content .= '<div class="grms-active-posts__header">';
                $content .= '<div class="grms-active-shops__header-title">';
                $content .= '<a href="?p='.$post_id.'&post_type=grms_shop"><span class="all-title">';
                $content .= $post_title;
                $content .= '</span></a></div>';
                $content .= '<div class="grms-active-shops__header-city">';
                $content .= get_post_meta($post_id, 'city', true);
                $content .= '</div>';
                $content .= '<div class="grms-active-shops__header-rating">';
                $content .= grms_rating_star(get_post_meta($post_id, 'rating', true));
                $content .= '</div></div>';
                
                $content .= '<div class="grms-active-posts__content">';
                $content .= get_the_excerpt();
                $content .= '</div>';
                $content .= '<div class="grms-active-posts__content">';
                $content .= get_post_meta($post_id, 'address', true);
                $content .= '</div>';
                
                $content .= '</div></div>';
            }
            /* オリジナルの投稿データを復元
             * 注意: WP_Query を使っているのでオリジナルの $wp_query を壊すことは
             * なく、wp_reset_query() によってリセットする必要はありません。
             * 投稿データを wp_reset_postdata() で復元することだけが必要です。
             */
            wp_reset_postdata();
        } else if( $attr && array_key_exists('noaction_indication', $attr) ) {
            $indication = $attr['noaction_indication'];
            $content .= '<div class="grms-noaction-indication">'.$indication.'</div>';
        }

        return $content;
    }
}

function grms_rating_star($rating_number) {
    $rating_string = "";
    for( $i = 0; $i < $rating_number ;$i++) {
        $rating_string .= '<i class="fa fa-star grms-rating"></i>';
    }
    return $rating_string;
}
$all_settings = get_option('GRMSPagePartsSettings', []);
$base_options = $all_settings['baseinfo_setting'] ?? [];

$shop_page_id = $base_options['shop_page_id'] ?? null;
if( $shop_page_id ) {
    $shop_page_name = $base_options['shop_recomend_title'];
    $grms_shop_list = new GRMS_ShopList($shop_page_id, $shop_page_name);
}
    

