<?php
/**
 * Insights article (used only when VMI_USE_PLUGIN_TEMPLATES is true on a classic theme).
 * To customize, copy this file into the (child) theme as single-insight.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="vmi vmi-single">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="vmi-single__header">
				<p class="vmi-back"><a href="<?php echo esc_url( get_post_type_archive_link( 'insight' ) ); ?>">&larr; All Insights</a></p>
				<h1><?php the_title(); ?></h1>
				<p class="vmi-meta">
					By <?php echo esc_html( get_the_author() ); ?> &middot;
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="vmi-single__image"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>

			<div class="vmi-single__content entry-content">
				<?php the_content(); ?>
			</div>

			<?php echo vmi_cta_html(); // phpcs:ignore ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
