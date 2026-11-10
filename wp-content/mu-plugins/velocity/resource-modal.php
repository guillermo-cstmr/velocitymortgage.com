<?php
/**
 * Resource Modal
 *
 * A custom modal to display a gravity form with custom prefilled values
 * based on the selected resource.
 *
 * Usage: Set any button/link's URL to #pdfrequest&resource_id=N.
 * The modal opens automatically and pre-fills the Gravity Form hidden field.
 *
 * @package Velocity_Mortgage
 * @since 1.0.0
 */

class Velocity_Resource_Modal {


	const FORM_ID = 14;

	public static function hooks() {
		add_action( 'wp_footer', array( __CLASS__, 'render_modal' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue inline CSS and JS with dedicated handles so this works
	 * regardless of which theme is active.
	 */
	public static function enqueue_scripts() {

		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		// Register a src-less style handle so we can attach inline CSS to it.
		wp_register_style( 'velocity-resource-modal', false );
		wp_enqueue_style( 'velocity-resource-modal' );
		wp_add_inline_style( 'velocity-resource-modal', self::get_inline_css() );

		// Register a src-less script handle in the footer, depending on jQuery.
		wp_register_script( 'velocity-resource-modal', false, array( 'jquery' ), null, true );
		wp_enqueue_script( 'velocity-resource-modal' );
		wp_add_inline_script( 'velocity-resource-modal', self::get_inline_js() );
	}

	/**
	 * Inline CSS – mirrors _main-form-modal.scss conventions.
	 */
	private static function get_inline_css() {
		return '
.velocity-resource-modal {
	display: none;
}

.velocity-resource-modal .dimmer,
.velocity-resource-modal .inner {
	position: fixed;
	z-index: 999999;
}

.velocity-resource-modal .dimmer {
	background: rgba(0,0,0,0.8);
	height: 100vh;
	left: 0;
	top: 0;
	width: 100vw;
	cursor: pointer;
}

.velocity-resource-modal .inner {
	background-color: #ffffff;
	left: 50%;
	max-height: 96vh;
	max-width: 900px;
	overflow: auto;
	padding: 60px 20px 20px;
	top: 50%;
	transform: translate(-50%, -50%);
	width: 90%;
}

@media (min-width: 992px) {
	.velocity-resource-modal .inner {
		padding: 80px 115px 60px;
		width: 80%;
	}
}

.velocity-resource-modal button.modal-close {
	background: transparent;
	border: none;
	cursor: pointer;
	height: 24px;
	padding: 0;
	position: absolute;
	right: 20px;
	top: 20px;
	width: 24px;
}

.velocity-resource-modal button.modal-close svg {
	stroke: #21275a;
	stroke-width: 0.6;
	transition: stroke 400ms ease-in-out;
}

.velocity-resource-modal button.modal-close:focus svg,
.velocity-resource-modal button.modal-close:hover svg {
	stroke: #f28520;
}

.velocity-resource-modal .form-container {
	height: 100%;
	padding-bottom: 20px;
}

.velocity-resource-modal .form-container h1,
.velocity-resource-modal .form-container h2,
.velocity-resource-modal .form-container h3,
.velocity-resource-modal .form-container h4,
.velocity-resource-modal .form-container h5,
.velocity-resource-modal .form-container h6 {
	color: #21275a;
	margin-bottom: 20px;
}

.velocity-resource-modal .form-container p,
.velocity-resource-modal .form-container p span,
.velocity-resource-modal .form-container legend,
.velocity-resource-modal .form-container label,
.velocity-resource-modal .form-container label span {
	color: #333333;
}

.velocity-resource-modal .form-container p,
.velocity-resource-modal .form-container label,
.velocity-resource-modal .form-container legend {
	font-size: 1.4rem;
}

.velocity-resource-modal .form-container legend {
	border-bottom: none;
	margin-bottom: 4px;
}
		';
	}

	/**
	 * Inline JS – opens the modal based on URL hash and updates the hidden request_id field.
	 *
	 * Trigger format: #pdfrequest&resource_id=N
	 */
	private static function get_inline_js() {
		$form_id = self::FORM_ID;
		return "(function($) {
	var modal        = $('.velocity-resource-modal');
	var formContainer = modal.find('.form-container');

	// Cache the original form HTML (h2, p, and the rendered GF form)
	// before any AJAX submission can replace it.
	var originalContent  = formContainer.html();
	var formWasSubmitted = false;

	// GF fires this event after an AJAX confirmation replaces the form.
	$(document).on('gform_confirmation_loaded', function(event, formId) {
		if ( parseInt( formId, 10 ) === {$form_id} ) {
			formWasSubmitted = true;
		}
	});

	// Parse resource_id from a hash like #pdfrequest&resource_id=3.
	function getResourceIdFromHash() {
		var hash = window.location.hash;
		if ( ! hash || hash.indexOf('pdfrequest') === -1 ) {
			return null;
		}
		var params = new URLSearchParams( hash.substring(1) );
		var id = params.get('resource_id');
		// Accept any non-empty alphanumeric string.
		return ( id && /^[a-zA-Z0-9_-]+$/.test(id) ) ? id : null;
	}

	function openFromHash() {
		var resourceId = getResourceIdFromHash();
		if ( ! resourceId ) {
			return;
		}

		// If the form was previously submitted, restore the original HTML
		// so the fresh form appears instead of the confirmation message.
		if ( formWasSubmitted ) {
			formContainer.html( originalContent );
			formWasSubmitted = false;
			// Let GF re-initialize any JS-driven field behaviors.
			$(document).trigger('gform_post_render', [ {$form_id}, 1 ]);
		}

		// Set the hidden request_id field inside the Gravity Form.
		$('#gform_{$form_id} input[type=\"hidden\"][name^=\"input_\"]').val( resourceId );

		modal.show();
		$('body').addClass('velocity-modal-open');
	}

	// Open on initial page load if hash is already set.
	openFromHash();

	// Open when the hash changes (button click).
	$(window).on('hashchange', openFromHash);

	// Close on dimmer click.
	modal.find('.dimmer').on('click', closeModal);

	// Close on close button click.
	modal.find('button.modal-close').on('click', closeModal);

	// Close on Escape key.
	$(document).on('keyup.velocity-resource-modal', function(e) {
		if ( e.key === 'Escape' ) {
			closeModal();
		}
	});

	function closeModal() {
		modal.hide();
		$('body').removeClass('velocity-modal-open');
		// Remove the hash without adding a browser history entry.
		history.replaceState( null, '', window.location.pathname + window.location.search );
	}

})(window.jQuery);";
	}

	/**
	 * Output the modal HTML in the footer.
	 */
	public static function render_modal() {

		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		?>
		<div class="velocity-resource-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Resource Request', 'velocitymu' ); ?>">
			<div class="dimmer"></div>
			<div class="inner">
				<button class="modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'velocitymu' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
				<div class="form-container">
					<h2><?php esc_html_e( 'Request Access', 'velocitymu' ); ?></h2>
					<p><?php esc_html_e( 'Complete the form below and the PDF will be emailed to you shortly.', 'velocitymu' ); ?></p>
					<?php gravity_form( self::FORM_ID, false, false, false, array(), true, 1 ); ?>
				</div>
			</div>
		</div>
		<?php
	}
}

Velocity_Resource_Modal::hooks();
