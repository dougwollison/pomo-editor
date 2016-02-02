<?php
/**
 * POMOEdit Options Registry
 *
 * @package POMOEdit
 * @subpackage Helpers
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Options Registry
 *
 * Stores all the configuration options for the system.
 *
 * @package POMOEdit
 * @subpackage Helpers
 *
 * @api
 *
 * @since 1.0.0
 */

class Registry {
	// =========================
	// ! Properties
	// =========================

	// to be written

	// =========================
	// ! Property Accessing
	// =========================

	/**
	 * Retrieve a property value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property The property name.
	 * @param mixed  $default  Optional. The default value to return.
	 *
	 * @return mixed The property value.
	 */
	public static function get( $property, $default = null ) {
		if ( property_exists( get_called_class(), $property ) ) {
			return static::$$property;
		}
		return $default;
	}

	/**
	 * Override a property value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property The property name.
	 * @param mixed  $value    The value to assign.
	 */
	public static function set( $property, $value = null ) {
		if ( property_exists( get_called_class(), $property ) ) {
			static::$$property = $value;
		}
	}

	// =========================
	// ! Setup Method
	// =========================

	/**
	 * Load the relevant options.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb The database abstraction class instance.
	 *
	 * @param bool $reload Should we reload the options?
	 */
	public static function load( $reload = false ) {
		global $wpdb;

		static $loaded = false;
		if ( $loaded && ! $reload ) {
			// Already did this
			return;
		}

		// Flag that we've loaded everything
		$loaded = true;
	}
}
