<?php
/**
 * POMOEdit Project Model
 *
 * @package POMOEdit
 * @subpackage Tools
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Project Model
 *
 * A proxy for opening, editing, and saving PO/MO objects.
 *
 * @package POMOEdit
 * @subpackage Tools
 *
 * @api
 *
 * @since 1.0.0
 */

class Project {
	// =========================
	// ! Properties
	// =========================

	/**
	 * The file it was loaded from and should save to.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $filename = '';

	/**
	 * The PO interface.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var \PO
	 */
	protected $po;

	// =========================
	// ! Methods
	// =========================

	/**
	 * Create a new object from provided details,
	 * or load a specified file.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $data Optional The data of the object or the path to a file to open.
	 */
	public function __construct( $data = null ) {
		// Load necessary libraries
		require_once( ABSPATH . WPINC . '/pomo/po.php' );

		// Create the PO interface
		$this->po = new \PO();

		if ( is_array( $data ) ) {
			$this->update( $data );
		} elseif ( ! is_null( $data ) ) {
			$this->filename = $data;
			$this->import();
		}
	}

	/**
	 * Load data from the file into the PO interface.
	 *
	 * @since 1.0.0
	 *
	 * @uses Project::$filename
	 */
	public function import() {
		if ( ! file_exists( $this->filename ) ) {
			throw new Exception( "File not found ({$this->filename})" );
		}

		$this->po->import_from_file( $this->filename );
	}

	/**
	 * Update with the provided data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data    The data to update with.
	 * @param bool  $replace Optional Replace all headers/entries/metadata with those provided?
	 */
	public function update( $data, $replace = false ) {
		// Update headers if present
		if ( isset( $data['headers'] ) ) {
			if ( $replace ) {
				// empty all headers
				$this->po->headers = array();
			}

			$this->po->set_headers( $data['headers'] );
		}
		// Update metadata if present
		if ( isset( $data['metadata'] ) ) {
			if ( $replace ) {
				// Delete all properties other than headers or entries
				foreach ( get_object_vars( $this->po ) as $prop => $value ) {
					if ( $prop !== 'headers' && $prop !== 'entries' ) {
						unset( $this->po->$prop );
					}
				}
			}

			foreach ( $data['metadata'] as $prop => $value ) {
				$this->po->$prop = $value;
			}
		}
		// Update entries if present
		if ( isset( $data['entries'] ) ) {
			if ( $replace ) {
				// empty all entries
				$this->po->entries = array();
			}

			foreach ( $data['entries'] as $entry ) {
				$this->po->add_entry( $entry );
			}
		}
	}

	/**
	 * Save the PO file and compile corresponding MO file.
	 *
	 * @since 1.0.0
	 *
	 * @uses \PO::export_to_file() to save the updated PO file.
	 * @uses \MO::export_to_file() to compile the MO file.
	 *
	 * @param string $file Optional The file path/name to use.
	 */
	public function export( $file = null ) {
		// Override file property with provided filename
		if ( $file ) {
			$this->filename = $file;
		}

		// Fail if no filename is available
		if ( ! $this->filename ) {
			throw new Exception( 'No path specified to save to.' );
		}

		// Load necessary libraries
		require_once( ABSPATH . WPINC . '/pomo/mo.php' );
		$mo = new \MO();

		// Create the .po and .mo filenames appropriately
		if ( substr( $this->filename, -3 ) == '.po' ) {
			// .po extension exists...
			$po_file = $this->filename;
			// ...replace with .mo
			$mo_file = substr( $this->filename, 0, -3 ) . '.mo';
		} else {
			// No extension, add each
			$po_file = $this->filename . '.po';
			$mo_file = $this->filename . '.mo';
		}

		// Copy all properties from the PO interface to the MO one
		foreach ( get_object_vars( $this->po ) as $key => $val ) {
			$mo->$key = $val;
		}

		// Export the PO file
		$this->po->export_to_file( $po_file );

		// Compile the MO file
		$mo->export_to_file( $mo_file );
	}

	/**
	 * Dump the PO interface properties as an associative array.
	 *
	 * The entries are exported as a numeric array.
	 *
	 * @since 1.0.0
	 */
	public function dump() {
		$data = array(
			'headers' => $this->po->headers,
			'entries' => array_values( $this->po->entries ),
			'metadata' => array(),
		);

		// All other properties go in 'metadata'
		foreach ( get_object_vars( $this->po ) as $prop => $value ) {
			if ( $prop !== 'headers' && $prop !== 'entries' ) {
				$data['metadata'][ $prop ] = $value;
			}
		}

		return $data;
	}
}
