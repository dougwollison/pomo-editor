<?php
/**
 * POMOEdit Parser API
 *
 * @package POMOEdit
 * @subpackage Tools
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Translator API
 *
 * A toolkit for opening, editing, and recompiling
 * gettext files, using the POMO libraries from WordPress.
 *
 * @package POMOEdit
 * @subpackage Tools
 *
 * @api
 *
 * @since 1.0.0
 */

class Parser {
	/**
	 * Proxy for importing a PO file into a PO object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename The name of the file to open.
	 *
	 * @return \PO The PO object.
	 */
	public static function load( $filename ) {
		require_once( ABSPATH . WPINC . '/pomo/po.php' );

		$po = new \PO();

		if ( ! file_exists( $filename ) ) {
			throw new Exception( 'PO file not found.' );
		}

		$po->import_from_file( $filename );

		// Convert entries to a numeric array
		$po->entries = array_values( $po->entries );

		return $po;
	}
}
