<?php
/**
* Template Name: NewsLetterPage
* @package WordPress
* @subpackage GRMS
* @since GRMS 1.0
*/
?>
<!--article-->
<article class="article content-page">
	<?php while ( have_posts() ) : the_post(); ?>
	<header>
		<div class="article-header">
			<h1><?php the_title(); ?><?php edit_post_link( __( 'Edit', 'emanon' ), '<span class="edit-link"><i class="fa fa-pencil-square-o"></i>', '</span>' ); ?></h1>
		</div>
		<?php if( has_post_thumbnail() ): ?>
		<div class="article-thumbnail">
		<?php the_post_thumbnail( 'large-thumbnail', array( 'itemprop' => 'image' ) ); ?>
		</div>
		<?php endif; ?>
	</header>
	<section class="article-body">
		<?php the_content(); ?>
		<?php if( is_emanon_exclude_ad_article() ): ?>
			<?php emanon_under_ad300(); ?>
		<?php endif; ?>
	</section>
	<?php if( is_emanon_exclude_cta_article() ): ?>
	<?php endif; ?>
	<?php if ( comments_open() || get_comments_number() ): ?>
	<footer class="article-footer">
		<?php comments_template(); ?>
	</footer>
	<?php endif; ?>
	<?php endwhile; ?>
</article>
<!--end article-->

