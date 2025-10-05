<?php

use WCML\Options\WPML;
use WPML\API\Sanitize;

/**
 * Class WCML_Setup
 */
class WCML_Setup {

	/** @var WCML_Setup_UI */
	private $ui;
	/** @var WCML_Setup_Handlers */
	private $handlers;
	/** @var  array */
	private $steps;
	/** @var  string */
	private $step;
	/** @var  woocommerce_wpml */
	private $woocommerce_wpml;
	/** @var  SitePress */
	private $sitepress;

	/**
	 * WCML_Setup constructor.
	 *
	 * @param WCML_Setup_UI       $ui
	 * @param WCML_Setup_Handlers $handlers
	 * @param woocommerce_wpml    $woocommerce_wpml
	 * @param SitePress           $sitepress
	 */
	public function __construct( WCML_Setup_UI $ui, WCML_Setup_Handlers $handlers, woocommerce_wpml $woocommerce_wpml, SitePress $sitepress ) {

		$this->ui               = $ui;
		$this->handlers         = $handlers;
		$this->woocommerce_wpml = $woocommerce_wpml;
		$this->sitepress        = $sitepress;

		$stepUrlStorePages          = \WCML\Utilities\AdminUrl::getSetup( WCML_Setup_Store_Pages_UI::SLUG );
		$stepUrlAttributes          = \WCML\Utilities\AdminUrl::getSetup( WCML_Setup_Attributes_UI::SLUG );
		$stepUrlMulticurrency       = \WCML\Utilities\AdminUrl::getSetup( WCML_Setup_Multi_Currency_UI::SLUG );
		$stepUrlDisplayAsTranslated = \WCML\Utilities\AdminUrl::getSetup( WCML_Setup_Display_As_Translated_UI::SLUG );

		$this->steps = [
			'introduction'        => [
				'name'    => __( 'Introduction', 'woocommerce-multilingual' ),
				'view'    => new WCML_Setup_Introduction_UI(
					$stepUrlStorePages
				),
				'handler' => '',
			],
			'store-pages'         => [
				'name'    => __( 'Store Pages', 'woocommerce-multilingual' ),
				'view'    => new WCML_Setup_Store_Pages_UI(
					$this->woocommerce_wpml,
					$this->sitepress,
					$stepUrlAttributes,
					\WCML\Utilities\AdminUrl::getSetup( WCML_Setup_Introduction_UI::SLUG )
				),
				'handler' => [ $this->handlers, 'install_store_pages' ],
			],
			'attributes'          => [
				'name'    => __( 'Global Attributes', 'woocommerce-multilingual' ),
				'view'    => new WCML_Setup_Attributes_UI(
					$this->woocommerce_wpml,
					$stepUrlMulticurrency,
					$stepUrlStorePages
				),
				'handler' => [ $this->handlers, 'save_attributes' ],
			],
			'multi-currency'      => [
				'name'    => __( 'Multiple Currencies', 'woocommerce-multilingual' ),
				'view'    => new WCML_Setup_Multi_Currency_UI(
					$stepUrlDisplayAsTranslated,
					$stepUrlAttributes
				),
				'handler' => [ $this->handlers, 'save_multi_currency' ],
			],
			'translation-options' => [
				'name'    => __( 'Translation Options', 'woocommerce-multilingual' ),
				'view'    => new WCML_Setup_Display_As_Translated_UI(
					'',
					$stepUrlMulticurrency
				),
				'handler' => [ $this->handlers, 'save_display_as_translated' ],
			],
		];
	}

	/**
	 * @return bool
	 */
	private function is_submitting_display_as_translated() {
		/* phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected */
		return (bool) Sanitize::stringProp( WCML_Setup_Handlers::KEY_DISPLAY_AS_TRANSLATED, $_POST );
	}

	public function add_hooks() {
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_init', [ $this, 'wizard' ] );
			add_action( 'admin_init', [ $this, 'handle_steps' ], 0 );
			add_filter( 'wp_redirect', [ $this, 'redirect_filters' ] );
		}

		if ( ! $this->has_completed() ) {
			$this->ui->add_wizard_notice_hook();
		}
	}

	public function setup_redirect() {
		if ( get_transient( '_wcml_activation_redirect' ) ) {
			delete_transient( '_wcml_activation_redirect' );

			if ( ! $this->do_not_redirect_to_setup() && ! $this->has_completed() ) {
				wcml_safe_redirect( admin_url( 'index.php?page=wcml-setup' ) );
			}
		}
	}

	private function do_not_redirect_to_setup() {
		// Before WC 4.6.
		$woocommerce_notices       = get_option( 'woocommerce_admin_notices', [] );
		$woocommerce_setup_not_run = in_array( 'install', $woocommerce_notices, true );

		// Since WC 4.6.
		$needsWcWizardFirst = get_transient( '_wc_activation_redirect' );

		return $this->is_wcml_setup_page() ||
			is_network_admin() ||
			isset( $_GET['activate-multi'] ) ||  /* phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected */
			! current_user_can( 'manage_options' ) ||
			$woocommerce_setup_not_run ||
			$needsWcWizardFirst ||
			wpml_is_ajax();

	}

	/**
	 * @return bool
	 */
	private function is_wcml_setup_page() {
		return isset( $_GET['page'] ) && 'wcml-setup' === $_GET['page'];
	}

	/**
	 * @return bool
	 */
	private function is_wcml_admin_page() {
		return isset( $_GET['page'] ) && 'wcml' === $_GET['page'];
	}

	public function wizard() {

		$this->splash_wizard_on_wcml_pages();

		if ( ! $this->is_wcml_setup_page() ) {
			return;
		}

		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		wp_enqueue_style( 'otgs-icons' );
		wp_enqueue_style(
			'wcml-setup',
			WCML_PLUGIN_URL . '/res/css/wcml-setup.css',
			[
				'dashicons',
				'install',
				OTGS_Assets_Handles::POPOVER_TOOLTIP,
			],
			WCML_VERSION
		);

		wp_enqueue_script( 'wcml-setup', WCML_PLUGIN_URL . '/res/js/wcml-setup.js', [ 'jquery', OTGS_Assets_Handles::POPOVER_TOOLTIP ], WCML_VERSION, true );

		$this->ui->setup_header( $this->steps, $this->step );
		$this->ui->setup_steps( $this->steps, $this->step );
		$this->ui->setup_content( $this->steps[ $this->step ]['view'] );
		$this->ui->setup_footer( ! empty( $this->steps[ $this->step ]['handler'] ) );

		if ( $this->is_setup_complete( $this->step ) ) {
			$this->complete_setup();
			$this->redirect_to_tm_dashboard_on_setup_complete();
		}

		wp_die();
	}

	/**
	 * @param string $step
	 *
	 * @return bool
	 */
	private function is_setup_complete( $step ) {
		if ( WCML_Setup_Display_As_Translated_UI::SLUG !== $step ) {
			return false;
		}

		return $this->is_submitting_display_as_translated();
	}

	/**
	 * @return void
	 */
	private function redirect_to_tm_dashboard_on_setup_complete() {
		wcml_safe_redirect( \WCML\Utilities\AdminUrl::getWPMLTMDashboard() );
	}

	private function splash_wizard_on_wcml_pages() {

		if ( isset( $_GET['src'] ) && \WCML\Utilities\AdminUrl::SRC_SETUP_LATER === $_GET['src'] ) {
			$this->woocommerce_wpml->settings['set_up_wizard_splash'] = 1;
			$this->woocommerce_wpml->update_settings();
		}

		if ( $this->is_wcml_admin_page() && ! $this->has_completed() && empty( $this->woocommerce_wpml->settings['set_up_wizard_splash'] ) ) {
			wcml_safe_redirect( \WCML\Utilities\AdminUrl::getSetup() );
		}
	}

	public function complete_setup() {
		$this->woocommerce_wpml->settings['set_up_wizard_run']    = 1;
		$this->woocommerce_wpml->settings['set_up_wizard_splash'] = 1;
		$this->woocommerce_wpml->update_settings();

		/**
		 * Fires after the setup wizard finishes.
		 *
		 * @since 5.3.0
		 */
		do_action( 'wcml_setup_completed' );
	}

	/**
	 * @return bool
	 */
	public static function is_product_automatically_translated() {
		return WPML::isAutomatic( 'product' );
	}

	private function has_completed() {
		return ! empty( $this->woocommerce_wpml->settings['set_up_wizard_run'] );
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public function redirect_filters( $url ) {
		if ( isset( $_POST['next_step_url'] ) && $_POST['next_step_url'] ) {
			$url = sanitize_text_field( $_POST['next_step_url'] );
		}
		return $url;
	}

	/**
	 * @param string $step
	 *
	 * @return mixed
	 */
	private function get_handler( $step ) {
		$handler = ! empty( $this->steps[ $step ]['handler'] ) ? $this->steps[ $step ]['handler'] : '';
		return $handler;
	}

	public function handle_steps() {
		if ( isset( $_POST['handle_step'] ) && wp_create_nonce( $_POST['handle_step'] ) === $_POST['nonce'] ) {
			$step_name = sanitize_text_field( $_POST['handle_step'] );
			if ( $handler = $this->get_handler( $step_name ) ) {
				if ( is_callable( $handler, true ) ) {
					call_user_func( $handler, $_REQUEST );
				}
			}
		}
	}
}
