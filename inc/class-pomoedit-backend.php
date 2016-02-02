<?php
/**
 * POMOEdit Backend Functionality
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Backend Functionality
 *
 * Hooks into various backend systems to load
 * custom assets and add the editor interface.
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */

class Backend extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 *
	 * @uses Registry::get() to retrieve enabled post types.
	 */
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_backend() ) {
			return;
		}

		// Post-setup stuff
		static::add_action( 'plugins_loaded', 'ready' );

		// Plugin information
		static::add_action( 'in_plugin_update_message-' . plugin_basename( PME_PLUGIN_FILE ), 'update_notice' );

		// Script/Style Enqueues
		static::add_action( 'admin_enqueue_scripts', 'enqueue_assets' );
	}

	// =========================
	// ! Post-Setup Stuff
	// =========================

	/**
	 * Load the text domain.
	 *
	 * @since 1.0.0
	 */
	public static function ready() {
		// Load the textdomain
		load_plugin_textdomain( 'pomoedit', false, PME_PLUGIN_DIR . '/lang' );
	}

	// =========================
	// ! Plugin Information
	// =========================

	/**
	 * In case of update, check for notice about the update.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The information about the plugin and the update.
	 */
	public static function update_notice( $plugin ) {
		// Get the version number that the update is for
		$version = $plugin['new_version'];

		// Check if there's a notice about the update
		$transient = "pomoedit-update-notice-{$version}";
		$notice = get_transient( $transient );
		if ( $notice === false ) {
			// Hasn't been saved, fetch it from the SVN repo
			$notice = file_get_contents( "http://plugins.svn.wordpress.org/pomoedit/assets/notice-{$version}.txt" ) ?: '';

			// Save the notice
			set_transient( $transient, $notice, YEAR_IN_SECONDS );
		}

		// Print out the notice if there is one
		if ( $notice ) {
			echo apply_filters( 'the_content', $notice );
		}
	}

	// =========================
	// ! Script/Style Enqueues
	// =========================

	/**
	 * Enqueue necessary styles and scripts.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_assets(){
		// Only bother if we're viewing the editor screen
		if ( get_current_screen()->id != 'tools_page_pomoedit' ) {
			return;
		}

		// Admin styling
		wp_enqueue_style( 'pomoedit-interface', plugins_url( 'css/interface.css', PME_PLUGIN_FILE ), '1.0.0', 'screen' );

		// Admin javascript
		wp_enqueue_script( 'pomoedit-interface-js', plugins_url( 'js/interface.js', PME_PLUGIN_FILE ), array( 'backbone' ), '1.0.0' );

		// Localize the javascript
		wp_localize_script( 'pomoedit-interface-js', 'pomoeditL10n', array(
			// to be written
		) );
	}
}
