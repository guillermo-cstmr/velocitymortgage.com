<?php
/**
 * Loan scenario calculator: markup.
 *
 * Every result card is printed, and all but the default one are hidden.
 * view.js only shows and hides them, so the copy lives here and in
 * config.php, never in JavaScript. Without JavaScript the default card
 * still shows.
 *
 * @package Velocity_Kadence
 *
 * @var array $attributes Block attributes. None are used.
 */

defined( 'ABSPATH' ) || exit;

$vm_calc = require __DIR__ . '/config.php';
$vm_id   = wp_unique_id( 'vm-calc-' );

// What the browser needs to decide: the rules, not the copy.
$vm_rules = array(
	'programs' => array(),
	'reasons'  => $vm_calc['reasons'],
	'noFit'    => $vm_calc['no_fit']['default'],
);
foreach ( $vm_calc['programs'] as $vm_key => $vm_program ) {
	$vm_rules['programs'][ $vm_key ] = array(
		'name'  => $vm_program['name'],
		'rules' => $vm_program['rules'],
	);
}

$vm_check = '<svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="M3.5 8.5l3 3 6-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'vm-calc' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-rules="<?php echo esc_attr( wp_json_encode( $vm_rules ) ); ?>">
	<form class="vm-calc__form" novalidate>
		<?php foreach ( $vm_calc['fields'] as $vm_name => $vm_field ) : ?>
			<div class="vm-calc__field">
				<label class="vm-calc__label" for="<?php echo esc_attr( $vm_id . '-' . $vm_name ); ?>"><?php echo esc_html( $vm_field['label'] ); ?></label>
				<div class="vm-calc__select">
					<select id="<?php echo esc_attr( $vm_id . '-' . $vm_name ); ?>" name="<?php echo esc_attr( $vm_name ); ?>">
						<?php foreach ( $vm_field['options'] as $vm_value => $vm_label ) : ?>
							<option value="<?php echo esc_attr( $vm_value ); ?>"<?php selected( $vm_value, $vm_field['default'] ); ?>><?php echo esc_html( $vm_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if ( 'credit' === $vm_name ) : ?>
					<label class="vm-calc__check">
						<input type="checkbox" name="events" value="yes">
						<span><?php echo esc_html( $vm_calc['events_label'] ); ?></span>
					</label>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<button type="submit" class="vm-calc__submit">Calculate</button>
	</form>

	<div class="vm-calc__result">
		<p class="screen-reader-text vm-calc__status" role="status" aria-live="polite"></p>

		<?php foreach ( $vm_calc['programs'] as $vm_key => $vm_program ) : ?>
			<article class="vm-calc__card" data-program="<?php echo esc_attr( $vm_key ); ?>"<?php echo $vm_key === $vm_calc['default_program'] ? '' : ' hidden'; ?>>
				<div class="vm-calc__card-head">
					<h3 class="vm-calc__title"><?php echo esc_html( $vm_program['name'] ); ?></h3>
					<span class="vm-calc__badge"><?php echo $vm_check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>Fits</span>
				</div>
				<p class="vm-calc__desc"><?php echo esc_html( $vm_program['description'] ); ?></p>
				<dl class="vm-calc__specs">
					<?php foreach ( $vm_program['specs'] as $vm_term => $vm_value ) : ?>
						<div><dt><?php echo esc_html( $vm_term ); ?></dt><dd><?php echo esc_html( $vm_value ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
				<a class="vm-calc__link" href="<?php echo esc_url( $vm_program['url'] ); ?>"><?php echo esc_html( $vm_program['link'] ); ?></a>
			</article>
		<?php endforeach; ?>

		<article class="vm-calc__card vm-calc__card--none" data-program="none" hidden>
			<h3 class="vm-calc__title"><?php echo esc_html( $vm_calc['no_fit']['title'] ); ?></h3>
			<p class="vm-calc__desc vm-calc__reason"><?php echo esc_html( $vm_calc['no_fit']['default'] ); ?></p>
			<a class="vm-calc__link" href="<?php echo esc_url( $vm_calc['no_fit']['url'] ); ?>"><?php echo esc_html( $vm_calc['no_fit']['link'] ); ?></a>
		</article>
	</div>
</div>
