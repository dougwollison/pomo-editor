<?php
/**
 * POMOEdit System
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Main System
 *
 * Sets up the database table aliases, the Registry,
 * and all the Handler classes.
 *
 * @api
 *
 * @since 1.0.0
 */
final class System extends Handler {
	// =========================
	// ! Master Setup Method
	// =========================

	/**
	 * Register hooks and load options.
	 *
	 * @since 1.0.0
	 *
	 * @uses Registry::load() to load the options.
	 * @uses Loader::register_hooks() to setup plugin management.
	 * @uses System::register_hooks() to setup global functionality.
	 * @uses Backend::register_hooks() to setup backend functionality.
	 * @uses AJAX::register_hooks() to setup AJAX functionality.
	 * @uses Manager::register_hooks() to setup admin screens.
	 * @uses Documenter::register_hooks() to setup admin documentation.
	 */
	final public static function setup() {
		// Setup the registry
		Registry::load();

		// Register the Installer stuff
		Installer::register_hooks();

		// Register global hooks
		static::register_hooks();

		// Register the hooks of the subsystems
		Backend::register_hooks();
		AJAX::register_hooks();
		Manager::register_hooks();
		Documenter::register_hooks();
	}

	// =========================
	// ! Setup Utilities
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	final public static function register_hooks() {
		// to be written
	}
}
