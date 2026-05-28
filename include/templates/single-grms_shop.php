<?php
/**
* Template Name: Top Page ( with sidebar )
* @package WordPress
* @subpackage Emanon_Free
* @since Emanon Free 1.0
*/
get_header(); ?>
<div class="content">
  <div class="container">
    <?php if( function_exists( 'emanon_page_breadcrumb' ) ) echo get_emanon_breadcrumb(); ?>
    <!--main-->
    <main>
      <?php $post_id = get_the_id(); ?>
	  <div class="col-main first">
        <div class="entry-header">
          <h1 class="grms_h1_title"><span><?php the_title(); ?></span></h1>
        </div>
        <!--article-->
        <article <?php echo 'class="article content-page grms-page-'.$post_id.'"' ?> >
          <header>
            <div class="grms-shop-header">
              <div class="grms-shop-city"> <?php echo get_post_meta($post_id, 'city', true)?> </div>
              <div class="grms-shop-rating"><?php echo grms_rating_star(get_post_meta($post_id, 'rating', true))?></div>
            </div>
            <?php if( has_post_thumbnail() ): ?>
              <div class="article-thumbnail">
                <?php the_post_thumbnail( 'large-thumbnail', array( 'itemprop' => 'image' ) ); ?>
              </div>
            <?php endif; ?>
          </header>
          <section class="article-body">
            <P>
              <?php the_content(); ?>
              <div class="grms-shop-info"><table>
         <?php
          $shop_url = get_post_meta($post_id, 'url', true);
          if( !empty($shop_url) ) {
             echo '<tr><td class="grms-shop-info-title"><span style="display:inline-block;">ホーム</span>ページ</td><td><a href="'.$shop_url.'" target="_blank">'.$shop_url.'</a></td></tr>';
            }
            $shop_tel = get_post_meta($post_id, 'tel', true);
            if( !empty($shop_tel) ) {
                echo '<tr><td class="grms-shop-info-title">電話<span style="display:inline-block;">（予約）</span></td><td>'.$shop_tel.'</td></tr>';
            }
            echo '<tr><td>住所</td><td>'.get_post_meta($post_id, 'address', true).'</td></tr>';
            echo '</table></div>';
          ?>
          </section>
         <?php if( is_mobile() ) :?>
         <?php dynamic_sidebar( 'page-bottom-sp' ); ?>
         <?php else:?>
         <?php dynamic_sidebar( 'page-bottom-pc' ); ?>
         <?php endif; ?>

         <?php emanon_bottom_sns_share(); ?>
         <?php emanon_under_ad300(); ?>
         <?php emanon_cta_single(); ?>

  	     <?php if ( comments_open() || get_comments_number() || $display_fb_like_btn || $display_content_twitter_follow || $display_content_sns_follow || $display_author_profile ): ?>
         <footer class="article-footer">
           <?php emanon_author_profile(); ?>
           <?php comments_template(); ?>
         </footer>
         <?php endif; ?>


      </article>
      <!--end article-->
      </div>
    </main>
    <!--end main-->
    <!--sidebar-->
	<aside class="col-sidebar sidebar">
    <?php get_sidebar(); ?>
	<!--end sidebar-->
  </div>
</div>
<?php get_footer(); ?>
