<?php
/**
 * Style guide template. Loaded by inc/styleguide.php.
 *
 * The inline styles below only lay out this page. They are deliberately not
 * part of the theme stylesheet. Everything being demonstrated (colors, type,
 * buttons) comes from the theme, exactly as any page would get it.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

$vm_headings = array(
	'h1' => 'H1. Poppins Regular, 74 / 40',
	'h2' => 'H2. Poppins Regular, 56 / 32',
	'h3' => 'H3. Poppins Regular, 46 / 26',
	'h4' => 'H4. Poppins Medium, 38 / 22',
	'h5' => 'H5. Poppins SemiBold, 30 / 18',
	'h6' => 'H6. Poppins Bold, 24',
);

get_header();
?>
<style>
	.vm-sg { max-width: 1280px; margin: 0 auto; padding: 64px 24px 112px; }
	.vm-sg section { padding: 48px 0; border-top: 1px solid var(--vm-border); }
	.vm-sg .vm-sg-label { font-size: 14px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--global-palette6); margin: 0 0 24px; }
	.vm-sg .vm-sg-row { display: flex; flex-wrap: wrap; gap: 24px; align-items: center; }
	.vm-sg .vm-sg-swatch { width: 180px; }
	.vm-sg .vm-sg-chip { height: 96px; border-radius: 8px; border: 1px solid var(--vm-border); }
	.vm-sg .vm-sg-swatch p { margin: 8px 0 0; font-size: 14px; line-height: 1.3; }
	.vm-sg .vm-sg-type > * { margin: 0 0 24px; }
	.vm-sg .vm-sg-dark { background: var(--global-palette3); padding: 32px; border-radius: 8px; }
	.vm-sg .vm-sg-card { background: var(--global-palette9); border: 1px solid var(--vm-border); border-radius: 8px; box-shadow: var(--vm-shadow-medium); padding: 32px; max-width: 410px; text-align: center; }
	.vm-sg .vm-sg-card > * { margin: 0 0 16px; }
	.vm-sg .vm-sg-card > *:last-child { margin-bottom: 0; }
	/* Text styles that pages set on each Text (Adv) block. Shown here for reference. */
	.vm-sg .vm-subtitle { font-size: 20px; line-height: 1.31; }
	.vm-sg .vm-body-small { font-size: 14px; line-height: 1.1; }
	.vm-sg .vm-h3-small { font-family: var(--global-heading-font-family); font-size: 22px; font-weight: 700; line-height: 1.1; letter-spacing: 0; }
	@media (max-width: 767px) { .vm-sg .vm-subtitle { font-size: 18px; } }
</style>

<div class="vm-sg">
	<h1>Style Guide</h1>
	<p class="vm-subtitle">Tokens as the theme renders them. Compare against the Style Guide page in Figma.</p>

	<section>
		<p class="vm-sg-label">Colors</p>
		<div class="vm-sg-row">
			<?php foreach ( vm_palette() as $vm_slot => $vm_color ) : ?>
				<?php
				if ( $vm_slot > 10 ) {
					continue;
				}
				?>
				<div class="vm-sg-swatch">
					<div class="vm-sg-chip" style="background: var(--global-palette<?php echo esc_attr( $vm_slot ); ?>);"></div>
					<p>
						<strong><?php echo esc_html( $vm_color[1] ); ?></strong><br>
						palette<?php echo esc_html( $vm_slot ); ?> &middot; <?php echo esc_html( $vm_color[0] ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section>
		<p class="vm-sg-label">Headings</p>
		<div class="vm-sg-type">
			<?php foreach ( $vm_headings as $vm_tag => $vm_text ) : ?>
				<<?php echo esc_attr( $vm_tag ); ?>><?php echo esc_html( $vm_text ); ?></<?php echo esc_attr( $vm_tag ); ?>>
			<?php endforeach; ?>
			<p class="vm-h3-small">Card title. H3 at 22, Poppins Bold</p>
		</div>
	</section>

	<section>
		<p class="vm-sg-label">Text</p>
		<div class="vm-sg-type" style="max-width: 720px;">
			<p class="vm-subtitle">Subtitle. Encode Sans Regular, 20 / 18. Where banks get stuck, Velocity keeps moving.</p>
			<p>Body. Encode Sans Regular, 16, line height 1.47. By lending and holding our own money, we offer the flexibility you need alongside the stability you expect. This paragraph has <a href="#">a link inside it</a> to show the link style.</p>
			<p class="vm-body-small">Body Small. Encode Sans Regular, 14.</p>
		</div>
	</section>

	<section>
		<p class="vm-sg-label">Buttons on a light background</p>
		<div class="vm-sg-row">
			<a class="button" href="#">Primary</a>
			<a class="button button-style-outline" href="#">Outline</a>
			<a class="button button-style-secondary" href="#">Secondary</a>
		</div>
	</section>

	<section>
		<p class="vm-sg-label">Buttons on a dark background</p>
		<div class="vm-sg-dark vm-sg-row">
			<a class="button" href="#">Primary</a>
			<a class="button button-style-secondary" href="#">Secondary</a>
		</div>
	</section>

	<section>
		<p class="vm-sg-label">Card</p>
		<div class="vm-sg-card">
			<p class="vm-h3-small">Stable Funding in Shifting Markets</p>
			<p>Our publicly held model keeps our funding steady, with over $11B lent since 2004.</p>
		</div>
	</section>
</div>
<?php
get_footer();
