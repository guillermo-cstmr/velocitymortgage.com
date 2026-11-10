<?php
/**
 * Insights index (used only when VMI_USE_PLUGIN_TEMPLATES is true on a classic theme).
 * To customize, copy this file into the (child) theme as archive-insight.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="vmi vmi-index">
	<header class="vmi-index__header">
		<h1><?php echo is_tax() ? esc_html( single_term_title( '', false ) ) : 'Insights'; ?></h1>
		<?php if ( VMI_TOPIC_FILTER ) { echo vmi_topic_filter_html(); } // phpcs:ignore ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="vmi-grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'vmi-card' ); ?>>
					<a class="vmi-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); } ?>
					</a>
					<div class="vmi-card__body">
						<h2 class="vmi-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="vmi-meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							&middot; <?php echo esc_html( get_the_author() ); ?>
						</p>
						<div class="vmi-card__excerpt"><?php the_excerpt(); ?></div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => '&larr; Newer',
				'next_text' => 'Older &rarr;',
			)
		);
		?>
	<?php else : ?>
		<p>No articles yet.</p>
	<?php endif; ?>
</main>
<?php
get_footer();
