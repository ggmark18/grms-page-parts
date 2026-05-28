<?php
/**
 * The template for displaying all pages
 * Template Name: 
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
	<!--end main-->
	<!--sidebar-->
   <aside class="col-sidebar-static sidebar">
   <?php get_sidebar(); ?>                
   </aside>
		<!--end sidebar-->
  </div>
</div>

<?php if ( is_mobile() ): ?>            
    <?php emanon_mobile_footer_buttons_page(); ?>
    <?php emanon_mobile_footer_buttons_modal_window(); ?>
<?php endif; ?>
<!--footer-->
<?php emanon_cta_footer_section(); ?>
<?php emanon_cta_popup(); ?>
<?php wp_footer(); ?>    
</body>
</html>
<?php emanon_html_compress_end(); ?>
