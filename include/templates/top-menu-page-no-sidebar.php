<?php
/**
* Template Name: Top Menu Page
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
      <div class="entry-header">
        <h1 class="grms_h1_title"><span><?php the_title(); ?></span></h1>
      </div>
      <!--article-->
      <article class="article content-page">
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
    </main>
    <!--end main-->
  </div>
</div>
<?php get_footer(); ?>
