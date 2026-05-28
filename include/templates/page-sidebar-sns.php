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
	  <div class="col-main first">
      <div class="entry-header">
        <h1 class="grms_h1_title"><span><?php the_title(); ?></span></h1>
      </div>
      <!--article-->
        <article <?php echo 'class="article content-page grms-page-'.get_the_id().'"' ?> >
        <?php while ( have_posts() ) : the_post(); ?>
          <header>
            <?php if( has_post_thumbnail() ): ?>
              <div class="article-thumbnail">
                <?php the_post_thumbnail( 'large-thumbnail', array( 'itemprop' => 'image' ) ); ?>
              </div>
            <?php endif; ?>
          </header>
          <section class="article-body">
            <P>
            <?php the_content(); ?>
            <?php wp_link_pages('before=<div class="next-page">&after=</div>&next_or_number=number&pagelink=<span class="page-numbers">%</span>'); ?>
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
        <?php endwhile; ?>
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
