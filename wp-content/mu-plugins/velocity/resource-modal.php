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
 * The Broker Tools page also shows the form on the page itself. The form is
 * printed once: it sits in the block with the class vm-toolkit-inline while
 * the window is closed, and moves into the window when a button opens it.
 * A visitor who already unlocked the toolkit does not see it on the page.
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
	 * Inline CSS: the window, and the parts of the form the window and the
	 * page share. Sizes and colors are the ones in the design.
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
	background: rgba(1,7,57,0.72);
	height: 100vh;
	left: 0;
	top: 0;
	width: 100vw;
	cursor: pointer;
}

/* The window: 640px wide, as tall as its content, scrolling only when the
   screen is too short for it. */
.velocity-resource-modal .inner {
	background-color: #ffffff;
	border: 1px solid #d4d4d4;
	border-radius: 16px;
	box-sizing: border-box;
	left: 50%;
	max-height: 94vh;
	max-width: 640px;
	overflow: auto;
	padding: 48px;
	top: 50%;
	transform: translate(-50%, -50%);
	width: calc(100% - 32px);
}

.velocity-resource-modal button.modal-close {
	background: transparent;
	border: none;
	box-shadow: none;
	cursor: pointer;
	height: 34px;
	padding: 0;
	position: absolute;
	right: 20px;
	top: 20px;
	width: 34px;
}

.velocity-resource-modal button.modal-close svg {
	stroke: #bb5013;
	stroke-width: 1.5;
	transition: stroke 200ms ease-in-out;
}

.velocity-resource-modal button.modal-close:focus svg,
.velocity-resource-modal button.modal-close:hover svg {
	stroke: #010739;
}

.velocity-resource-modal .form-container h2 {
	color: #010739;
	font-size: 56px;
	line-height: 1.1;
	margin: 0 0 20px;
}

.velocity-resource-modal .form-container > p {
	color: #010739;
	font-size: 16px;
	line-height: 1.47;
	margin: 0 0 32px;
}

.velocity-resource-modal .form-container legend {
	border-bottom: none;
}

/* A shorter screen, or a phone: smaller title and less space around. */
@media (max-height: 900px), (max-width: 600px) {
	.velocity-resource-modal .inner {
		padding: 32px 32px 28px;
	}

	.velocity-resource-modal .form-container h2 {
		font-size: 36px;
		margin-bottom: 12px;
	}

	.velocity-resource-modal .form-container > p {
		margin-bottom: 20px;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .gform_fields {
		row-gap: 12px;
	}
}

/* Phones: everything a size smaller, and first and last name side by side,
   so the whole form fits on the screen without scrolling. */
@media (max-width: 600px) {
	.velocity-resource-modal .inner {
		padding: 20px 20px 20px;
	}

	.velocity-resource-modal button.modal-close {
		height: 28px;
		right: 14px;
		top: 16px;
		width: 28px;
	}

	.velocity-resource-modal .form-container h2 {
		font-size: 26px;
		margin-bottom: 8px;
		padding-right: 36px;
	}

	.velocity-resource-modal .form-container > p {
		font-size: 14px;
		line-height: 1.35;
		margin-bottom: 12px;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .gform_fields {
		row-gap: 8px;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .gfield_label,
	body .velocity-resource-modal .gform_wrapper.gravity-theme legend.gfield_label {
		font-size: 14px;
		margin-bottom: 4px;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme input[type="text"],
	body .velocity-resource-modal .gform_wrapper.gravity-theme input[type="email"],
	body .velocity-resource-modal .gform_wrapper.gravity-theme input[type="tel"] {
		height: 42px;
		min-height: 0;
		padding-bottom: 8px;
		padding-top: 8px;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .ginput_complex {
		flex-wrap: nowrap;
		gap: 8px;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .ginput_complex > span {
		flex: 1 1 0;
		margin: 0;
		padding: 0;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .ginput_complex label {
		font-size: 12px;
		margin: 2px 0 0;
		padding: 0;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .gform_footer {
		gap: 12px;
		margin-top: 12px;
	}

	.velocity-resource-modal .vm-toolkit-fields .vm-toolkit-follow-up {
		font-size: 14px;
		line-height: 1.35;
	}

	body .velocity-resource-modal .gform_wrapper.gravity-theme .gform_footer .gform_button {
		min-height: 46px;
		padding-bottom: 12px;
		padding-top: 12px;
	}

	body .velocity-resource-modal .vm-toolkit-fields .vm-toolkit-partner {
		font-size: 14px;
		margin-top: 16px;
	}
}

/* The last part of the form, in the window and on the page: the line about the
   follow-up, the button at its own width, and the link to become a partner. */
body .vm-toolkit-fields .gform_wrapper.gravity-theme .gform_footer {
	flex-direction: column;
	align-items: flex-start;
	gap: 24px;
}

body .vm-toolkit-fields .gform_wrapper.gravity-theme .gform_footer .gform_button {
	width: 240px;
	max-width: 100%;
	min-height: 54px;
	font-size: 16px;
	align-self: flex-start;
}

.vm-toolkit-fields .vm-toolkit-follow-up {
	color: #010739;
	font-size: 16px;
	line-height: 1.47;
	margin: 0;
}

body .vm-toolkit-fields .vm-toolkit-partner {
	font-size: 16px;
	line-height: 1.1;
	margin: 32px 0 0;
}

.vm-toolkit-partner a {
	color: #bb5013;
	font-weight: 600;
	text-decoration: underline;
}

.vm-toolkit-partner a:hover,
.vm-toolkit-partner a:focus {
	color: #010739;
}

/* On the page all of it is centered. */
body .vm-toolkit-inline .gform_wrapper.gravity-theme .gform_footer {
	align-items: center;
	text-align: center;
}

body .vm-toolkit-inline .gform_wrapper.gravity-theme .gform_footer .gform_button {
	align-self: center;
}

.vm-toolkit-inline .vm-toolkit-partner {
	text-align: center;
}

.vm-toolkit-wl:not(:has(a[href*="action=velocity_toolkit"])) {
	display: none;
}

/* "Velocity Branded" only makes sense beside a White Label button. */
.vm-toolkit-doc:not(:has(.vm-toolkit-wl a[href*="action=velocity_toolkit"])) .vm-toolkit-branded {
	display: none;
}

/* Phones: the buttons of a document fill the row, two side by side when it
   has a White Label file. Two do not fit with the whole label, so until the
   toolkit is unlocked they read "Unlock". Screen readers still get it all. */
@media (max-width: 767px) {
	.vm-toolkit-doc .kb-buttons-wrap,
	body .vm-toolkit-doc .kb-buttons-wrap .kb-button {
		width: 100%;
	}

	body .vm-toolkit-doc .kb-buttons-wrap .kb-button {
		padding-left: 12px;
		padding-right: 12px;
	}

	body:not(.vm-toolkit-unlocked) .vm-toolkit-doc:has(.vm-toolkit-wl a[href*="action=velocity_toolkit"]) a[href*="locked=1"] .kt-btn-inner-text {
		font-size: 0;
	}

	body:not(.vm-toolkit-unlocked) .vm-toolkit-doc:has(.vm-toolkit-wl a[href*="action=velocity_toolkit"]) a[href*="locked=1"] .kt-btn-inner-text::before {
		content: "Unlock";
		font-size: 0.9rem;
	}
}

/* The form on the page, for a visitor who already unlocked the toolkit. */
.vm-toolkit-returning .vm-toolkit-form {
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
	var fields        = modal.find('.vm-toolkit-fields');
	var slot          = $('.vm-toolkit-inline > .kt-inside-inner-col').first();

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

	// The form lives on the page, when the page has a place for it, and
	// moves into the window while the window is open.
	function toPage() {
		if ( slot.length ) {
			slot.append( fields );
		}
	}

	function openForm(requestId) {
		if ( formWasSubmitted ) {
			slot.empty();
			formContainer.html( originalContent );
			fields = modal.find('.vm-toolkit-fields');
			formWasSubmitted = false;
			$(document).trigger('gform_post_render', [ {$form_id}, 1 ]);
		}
		formContainer.append( fields );
		$('#input_{$form_id}_{$field_id}').val( requestId || '' );
		modal.show();
		$('body').addClass('velocity-modal-open');
	}

	function closeModal() {
		modal.hide();
		$('body').removeClass('velocity-modal-open');
		if ( ! formWasSubmitted ) {
			$('#input_{$form_id}_{$field_id}').val('');
			toPage();
		}
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
		// Sent from the window: the form on the page has nothing left to show.
		if ( slot.length && ! slot.find('.vm-toolkit-fields').length ) {
			$('body').addClass('vm-toolkit-returning');
		}
		var secure = window.location.protocol === 'https:' ? '; secure' : '';
		document.cookie = cookieName + '=1; max-age=31536000; path=/; samesite=lax' + secure;
		markUnlocked();
		if ( pending ) {
			window.location.href = pending;
			pending = null;
		}
	});

	// A document anyone can download never says Unlock.
	$('a[href*=\"action=velocity_toolkit\"]').each(function() {
		if ( param(this.href, 'locked') === '1' ) {
			return;
		}
		var walker = document.createTreeWalker(this, NodeFilter.SHOW_TEXT);
		while (walker.nextNode()) {
			walker.currentNode.nodeValue = walker.currentNode.nodeValue.replace(/Unlock to Download/g, 'Download');
		}
	});

	if ( isUnlocked() ) {
		markUnlocked();
		$('body').addClass('vm-toolkit-returning');
	} else {
		toPage();
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
					<div class="vm-toolkit-fields">
					<?php gravity_form( self::FORM_ID, false, false, false, array(), true, 1 ); ?>
					<p class="vm-toolkit-partner"><a href="<?php echo esc_url( home_url( '/become-a-partner/' ) ); ?>"><?php esc_html_e( 'Not a Partner Yet? Become a Partner', 'velocitymu' ); ?></a></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

Velocity_Resource_Modal::hooks();
