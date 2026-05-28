<?php
class GRMS_PostList {
    function __construct() {
        add_shortcode('grms_post_list', array( &$this, 'post_list'));
    }

    function post_list( $attr ) {
        $content = "";
        
        $post_args = array();

        $reduce_word = null;
        
        $cat_attr = false;
        if( $attr ) {
            if( array_key_exists('tag', $attr) ) {
                $tag_attr = $attr['tag'];
                $tag  = get_tags(array('slug' => $tag_attr));
                if( count($tag) > 0 ) {
                    $post_args['tag__in'] = array($tag[0]->term_id);
                }
            }
            if( array_key_exists('extag', $attr) ) {
                $extag_attr = $attr['extag'];
                $extag  = get_tags(array('slug' => $extag_attr));
                if( count($extag) > 0 ) {
                    $post_args['tag__not_in'] = array($extag[0]->term_id);
                }
            }
            if( array_key_exists('cat', $attr) ) {
                $cat_attr = $attr['cat'];
                $cat = get_categories(array('slug' => $cat_attr));
                $catids = array('none');
                foreach ($cat as &$value) {
                    array_push($catids, $value->term_id);
                }
                $post_args['category__in'] = $catids;
                $cat_attr = true;
            }
            if( array_key_exists('latest', $attr) ) {
                $latest_attr = $attr['latest'];
                $post_args['date_query'] = array(
                    array(
                        'column' => 'post_date_gmt',
                        'after' => $latest_attr.' month ago',
                    ));
            }
            if( array_key_exists('reduce', $attr) ) {
                $reduce_word = $attr['reduce'];
            }
        }

        $post_query = new WP_Query( $post_args );

        if ( $post_query->have_posts() ) {
            while ( $post_query->have_posts() ) {
                $content .= '<div class="row grms-active-article">';
                $post_query->the_post();

                $post_title = get_the_title();
                if( $reduce_word ) {
                    $post_title = str_replace($reduce_word, '', $post_title);
                    $post_title = str_replace('【】', '', $post_title);
                    $post_title = str_replace('[]', '', $post_title);
                }
                $content .= '<div class="col-sm-3 grms-active-thumbnail px-0" ><a href="?p='.get_the_id().'">';
                $imgid = get_post_thumbnail_id();
                $imageurl = wp_get_attachment_image_src($imgid, 'small-thumbnail' );
                $content .= '<img src="'.$imageurl[0].'" height="120px" class="mb-0">';
                $content .= '</a></div>';
                $content .= '<div class="col-sm-9 grms-active-posts"><div class="grms-active-posts__header">';
                if( !$cat_attr ) {
                    $content .= '<div class="grms-active-posts__header-title">';
                    $content .= '<span class="grms-category-title">';
                    $post_categories = wp_get_post_categories( get_the_id() );
                    $cats = array();
                    foreach( $post_categories as $c ){
                        $cat = get_category( $c );
                        $content .= $cat->name;
                    }
                    $content .= '</span>';
                    $content .= '</div>';
                    $content .= '<div class="grms-active-posts__header-date"><i class="far fa-clock"></i>&nbsp';
                    $content .= get_the_date();
                } else {
                    $content .= '<div class="grms-active-posts-header-title-date"><i class="far fa-clock"></i>&nbsp';
                    $content .= get_the_date();
                }
                $content .= '</div></div>';
                $content .= '<div class="grms-active-posts__title">';
                $content .= '<a href="?p='.get_the_id().'"><span class="all-title">';
                $content .= $post_title;
                $content .= '</span></a>';
                $content .= '</div>';
                $content .= '<div class="grms-active-posts__content">';
                $content .= get_the_excerpt();
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

$grms_post_list = new GRMS_PostList();
    

