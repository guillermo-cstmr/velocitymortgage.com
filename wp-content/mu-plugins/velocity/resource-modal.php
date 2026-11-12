<?php
/**
 * Resource Modal
 *
 * A custom modal to display a gravity form with custom prefilled values
 * based on the selected resource.
 *
 * Broker Toolkit buttons link to the download address of a toolkit document
 * (see cpt-toolkit.php). Until the visitor unlocks the toolkit, a click on a
 * locked document opens this form instead. Sending the form unlocks every
 * document on this browser for a year (cookie), turns the buttons into
 * plain downloads, and starts the download that was asked for.
 *
 * Older links of the form #pdfrequest&resource_id=N still open the form.
 * The hidden Request ID field records which file was asked for.
 *
 * @package Velocity_Mortgage
 * @since 1.0.0
 */

class Velocity_Resource_Modal {


	const FORM_ID = 14;

	// Hidden "Request ID" field of the form.
	const REQUEST_FIELD_ID = 4;

	public static function hooks() {
		add_action( 'wp_footer', array( __CLASS__, 'render_modal' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_filter( 'gform_pre_render_' . self::FORM_ID, array( __CLASS__, 'button_text' ) );
		add_filter( 'gform_submit_button_' . self::FORM_ID, array( __CLASS__, 'follow_up_text' ) );
		// The form lives in a modal: no scrolling to it after sending. Gravity
		// Forms' scroll fails there and stops the confirmation event.
		add_filter( 'gform_confirmation_anchor_' . self::FORM_ID, '__return_false' );
	}

	/**
	 * Submit button wording from the design.
	 */
	public static function button_text( $form ) {
		$form['button']['text'] = __( 'Unlock to Download', 'velocitymu' );
		return $form;
	}

	/**
	 * Note above the submit button.
	 */
	public static function follow_up_text( $button ) {
		return '<p class="vm-toolkit-follow-up">' . esc_html__( 'An account executive will follow up to help you put these to work.', 'velocitymu' ) . '</p>' . $button;
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

.velocity-resource-modal .vm-toolkit-partner {
	margin-top: 24px;
}

.velocity-resource-modal .vm-toolkit-partner a {
	color: #21275a;
	font-weight: 600;
	text-decoration: underline;
}

.vm-toolkit-wl:not(:has(a[href*="action=velocity_toolkit"])) {
	display: none;
}
		';
	}

	/**
	 * Inline JS: locked buttons, the unlock cookie and the old hash links.
	 */
	private static function get_inline_js() {
		$form_id  = self::FORM_ID;
		$field_id = self::REQUEST_FIELD_ID;
		$cookie   = Velocity_Toolkit::COOKIE;
		$ajax_url = esc_url_raw( admin_url( 'admin-ajax.php' ) );
		return "(function($) {
	var modal         = $('.velocity-resource-modal');
	var formContainer = modal.find('.form-container');
	var cookieName    = '{$cookie}';
	var ajaxUrl       = '{$ajax_url}';
	var pending       = null;

	// Cache the form HTML before an AJAX submission replaces it.
	var originalContent  = formContainer.html();
	var formWasSubmitted = false;

	function isUnlocked() {
		return document.cookie.split('; ').some(function(c) {
			return c.indexOf(cookieName + '=') === 0;
		});
	}

	// Buttons say Download once the toolkit is unlocked.
	function markUnlocked() {
		$('body').addClass('vm-toolkit-unlocked');
		$('a[href*=\"action=velocity_toolkit\"]').each(function() {
			var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT);
			while (walker.nextNode()) {
				walker.currentNode.nodeValue = walker.currentNode.nodeValue.replace(/Unlock to Download/g, 'Download');
			}
		});
	}

	function param(url, name) {
		try {
			return new URL(url, window.location.href).searchParams.get(name);
		} catch (e) {
			return null;
		}
	}

	function openForm(requestId) {
		if ( formWasSubmitted ) {
			formContainer.html( originalContent );
			formWasSubmitted = false;
			$(document).trigger('gform_post_render', [ {$form_id}, 1 ]);
		}
		$('#input_{$form_id}_{$field_id}').val( requestId || '' );
		modal.show();
		$('body').addClass('velocity-modal-open');
	}

	function closeModal() {
		modal.hide();
		$('body').removeClass('velocity-modal-open');
		if ( window.location.hash.indexOf('pdfrequest') !== -1 ) {
			history.replaceState( null, '', window.location.pathname + window.location.search );
		}
	}

	// A locked document before unlocking: open the form instead.
	$(document).on('click', 'a[href*=\"action=velocity_toolkit\"]', function(e) {
		if ( isUnlocked() || param(this.href, 'locked') !== '1' ) {
			return;
		}
		e.preventDefault();
		pending = this.href;
		openForm( param(this.href, 'rid') );
	});

	// Old links: #pdfrequest&resource_id=N.
	function openFromHash() {
		var hash = window.location.hash;
		if ( ! hash || hash.indexOf('pdfrequest') === -1 ) {
			return;
		}
		var id = new URLSearchParams( hash.substring(1) ).get('resource_id');
		if ( ! id || ! /^[a-zA-Z0-9_-]+$/.test(id) ) {
			return;
		}
		pending = ajaxUrl + '?action=velocity_toolkit&rid=' + encodeURIComponent(id);
		if ( isUnlocked() ) {
			history.replaceState( null, '', window.location.pathname + window.location.search );
			window.location.href = pending;
			return;
		}
		openForm( id );
	}

	// After sending the form: unlock, then download what was asked for.
	$(document).on('gform_confirmation_loaded', function(event, formId) {
		if ( parseInt( formId, 10 ) !== {$form_id} ) {
			return;
		}
		formWasSubmitted = true;
		var secure = window.location.protocol === 'https:' ? '; secure' : '';
		document.cookie = cookieName + '=1; max-age=31536000; path=/; samesite=lax' + secure;
		markUnlocked();
		if ( pending ) {
			window.location.href = pending;
			pending = null;
		}
	});

	if ( isUnlocked() ) {
		markUnlocked();
	}
	openFromHash();
	$(window).on('hashchange', openFromHash);

	modal.find('.dimmer').on('click', closeModal);
	modal.find('button.modal-close').on('click', closeModal);
	$(document).on('keyup.velocity-resource-modal', function(e) {
		if ( e.key === 'Escape' ) {
			closeModal();
		}
	});

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
		<div class="velocity-resource-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Unlock the Toolkit', 'velocitymu' ); ?>">
			<div class="dimmer"></div>
			<div class="inner">
				<button class="modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'velocitymu' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
				<div class="form-container">
					<h2><?php esc_html_e( 'Unlock the Toolkit', 'velocitymu' ); ?></h2>
					<p><?php esc_html_e( 'Fill this out once. Every file on this page becomes a one-click download, now and on your next visit.', 'velocitymu' ); ?></p>
					<?php gravity_form( self::FORM_ID, false, false, false, array(), true, 1 ); ?>
					<p class="vm-toolkit-partner"><a href="<?php echo esc_url( home_url( '/become-a-partner/' ) ); ?>"><?php esc_html_e( 'Not a partner yet? Become a Partner', 'velocitymu' ); ?></a></p>
				</div>
			</div>
		</div>
		<?php
	}
}

Velocity_Resource_Modal::hooks();
