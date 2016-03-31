<?php
/**
 * POMOEditor System
 *
 * @package POMOEditor
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Main System
 *
 * Sets up all the Handler classes.
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
	 * @uses Backend::register_hooks() to setup backend functionality.
	 * @uses Manager::register_hooks() to setup admin screens.
	 * @uses Documenter::register_hooks() to setup admin documentation.
	 */
	public static function setup() {
		// Register the hooks of the subsystems
		Backend::register_hooks();
		Manager::register_hooks();
		Documenter::register_hooks();
	}
}
