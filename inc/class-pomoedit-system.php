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
 * Main System Class
 *
 * Sets up the Registry and all the Handler classes.
 *
 * @package POMOEdit
 * @subpackage Helpers
 *
 * @api
 *
 * @since 1.0.0
 */

class System extends Handler {
	// =========================
	// ! Master Setup Method
	// =========================

	/**
	 * Register hooks and load options.
	 *
	 * @since 1.0.0
	 */
	public static function setup() {
		// Register the loader hooks
		Loader::register_hooks();

		// Setup the registry
		Registry::load();

		// Register own hooks
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
	public static function register_hooks() {
		// to be written
	}
}
