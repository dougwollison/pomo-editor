<?php
/**
 * POMOEdit AJAX Handler
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The AJAX Request Handler
 *
 * Add necessary wp_ajax_* hooks to fullfill any
 * custom AJAX requests.
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */

class AJAX extends Handler {
	// =========================
	// ! Properties
	// =========================

	/**
	 * The name of the class.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $name;

	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public static function register_hooks() {
		// Don't do anything if not doing an AJAX request
		if ( ! defined( 'DOING_AJAX' ) || DOING_AJAX !== true ) {
			return;
		}
		
		// to be written
	}
}

