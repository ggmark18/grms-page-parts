<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage CETACEA2017
 * @since 1.0
 * @version 1.0
 */
?>

<?php
  $my_id = get_the_ID();
  $category = get_the_category();
  $cat_name = $category[0]->cat_name;
  $cat_id = $category[0]->cat_ID;
  query_posts( array('post_type' => 'page','order'=>'ASC','orderby'=>'menu_order','cat' => $cat_id));
  if ( have_posts() ) :
    echo '<div class="widget static-link-area"><ul>';
    while ( have_posts() ) : the_post();

?>
     <li class="static-link-list <?php if(get_the_ID() == $my_id) echo 'active_li'?>"><a href="<?php the_permalink(); ?>"><span class="static-link"><?php the_title(); ?></span></a>
<?php
     endwhile;
     echo '</ul></div>';
   endif;
   wp_reset_query();
   $posts = get_posts(array(
       'category' => $cat_id
   ));
?>
