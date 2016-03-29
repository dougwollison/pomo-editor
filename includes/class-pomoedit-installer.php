<?php
/**
 * POMOEdit Installation Functionality
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Plugin Installer
 *
 * Registers activate/deactivate/uninstall hooks, and handle
 * any necessary upgrading from an existing install.
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Installer extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register the plugin hooks
	 *
	 * @since 1.0.0
	 *
	 * @uses PME_PLUGIN_FILE to identify the plugin file.
	 * @uses Loader::plugin_activate() as the activation hook.
	 * @uses Loader::plugin_deactivate() as the deactivation hook.
	 * @uses Loader::plugin_uninstall() as the uninstall hook.
	 */
	final public static function register_hooks() {
		register_activation_hook( PME_PLUGIN_FILE, array( get_called_class(), 'plugin_activate' ) );
		register_deactivation_hook( PME_PLUGIN_FILE, array( get_called_class(), 'plugin_deactivate' ) );
		register_uninstall_hook( PME_PLUGIN_FILE, array( get_called_class(), 'plugin_uninstall' ) );

		// Upgrade logic
		static::add_action( 'plugins_loaded', 'upgrade', 10, 0 );
	}

	// =========================
	// ! Utilities
	// =========================

	/**
	 * Security check logic.
	 *
	 * @since 1.0.0
	 */
	final protected static function plugin_security_check( $check_referer = null ) {
		// Make sure they have permisson
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		if ( $check_referer ) {
			$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
			check_admin_referer( "{$check_referer}-plugin_{$plugin}" );
		} else {
			// Check if this is the intended plugin for uninstalling
			if ( ! isset( $_REQUEST['checked'] )
			|| ! in_array( plugin_basename( PME_PLUGIN_FILE ), $_REQUEST['checked'] ) ) {
				return false;
			}
		}

		return true;
	}

	// =========================
	// ! Hook Handlers
	// =========================

	/**
	 * Create database tables and add default options.
	 *
	 * @since 1.0.0
	 *
	 * @uses Loader::plugin_security_check() to check for activation nonce.
	 *
	 * @global wpdb $wpdb The database abstraction class instance.
	 */
	final public static function plugin_activate() {
		global $wpdb;

		if ( ! static::plugin_security_check( 'activate' ) ) {
			return;
		}

		// Attempt to upgrade, in case we're activating after an plugin update
		if ( ! static::upgrade() ) {
			// Otherwise just install the options/tables
			static::install();
		}
	}

	/**
	 * Empty deactivation hook for now.
	 *
	 * @since 1.0.0
	 *
	 * @uses Loader::plugin_security_check() to check for deactivation nonce.
	 *
	 * @global wpdb $wpdb The database abstraction class instance.
	 */
	final public static function plugin_deactivate() {
		global $wpdb;

		if ( ! static::plugin_security_check( 'deactivate' ) ) {
			return;
		}

		// to be written
	}

	/**
	 * Delete database tables and any options.
	 *
	 * @since 1.0.0
	 *
	 * @uses Loader::plugin_security_check() to check for WP_UNINSTALL_PLUGIN.
	 *
	 * @global wpdb $wpdb The database abstraction class instance.
	 */
	final public static function plugin_uninstall() {
		if ( ! static::plugin_security_check() ) {
			return;
		}

		delete_option( "pomoedit_options" );
	}

	// =========================
	// ! Install Logic
	// =========================

	/**
	 * Install the default options.
	 *
	 * @since 1.0.0
	 *
	 * @uses Registry::get_defaults() to get the default option values.
	 */
	final protected static function install() {
		// Default options
		$default_options = Registry::get_defaults();
		add_option( 'pomoedit_options', $default_options );
	}

	// =========================
	// ! Upgrade Logic
	// =========================

	/**
	 * Install/Upgrade the database tables, converting them if needed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Wether or not an upgrade was performed.
	 */
	final public static function upgrade() {
		// to be written
	}
}
