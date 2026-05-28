<?php
/**
 * The template for displaying all pages
 * Template Name: 2カラム Category Sidebar
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header();?>
<div class="content">
  <div class="container">
    <?php if( function_exists( 'emanon_page_breadcrumb' ) ) echo get_emanon_breadcrumb(); ?>
	<!--main-->
    <div class="col-main first grms-category-col">
      <div class="entry-header">
        <h2 class="grms_h2_title"><span><?php the_title(); ?></span></h2>
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
            <?php the_content(); ?>
          </section>
        <?php endwhile; ?>
      </article>
      <!--end article-->
    </div>
	<!--end main-->
	<!--sidebar-->
            <a name="side-bar"></a>
            <div class="side-bar-margin-category"></div>

	<aside class="col-sidebar sidebar grms-category-sidebar">

       <div class="col-sidebar-static-title">
<?php 
  $category = get_the_category();
  $cat_name = $category[0]->cat_name;
  wp_reset_query();
  query_posts( array('post_type' => 'page','title' => $cat_name));
  if ( have_posts() ) : the_post();
      echo '<a href="';
      the_permalink();
      echo '"><i class="fa fa-angle-left"></i>&nbsp';
      echo $cat_name;
      echo '</a>';
  else :
    echo $cat_name;
  endif;
  echo '</div>';
  wp_reset_query();
  include 'grms-sidebar-category.php';
?>
    </aside>
	<!--end sidebar-->
    <?php if ( is_mobile() ): ?>            
        <?php emanon_mobile_footer_buttons_page(); ?>
        <?php emanon_mobile_footer_buttons_modal_window(); ?>
    <?php endif; ?>
        
	</div>
</div>
<?php get_footer(); ?>
