<?php
/**
 * POMOEdit Manager Funtionality
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEdit;

/**
 * The Management System
 *
 * Hooks into the backend to add the interfaces for
 * managing the configuration of POMOEdit.
 *
 * @package POMOEdit
 * @subpackage Handlers
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */

class Manager extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_backend() ) {
			return;
		}

		// Settings & Pages
		static::add_action( 'admin_menu', 'add_menu_pages' );
	}

	// =========================
	// ! Utilities
	// =========================

	// =========================
	// ! Settings Page Setup
	// =========================

	/**
	 * Register admin pages.
	 *
	 * @since 1.0.0
	 */
	public static function add_menu_pages() {
		add_management_page(
			__( 'PO/MO Editor' ), // page title
			__( 'PO/MO Editor' ), // menu title
			'manage_options', // capability
			'pomoedit', // slug
			array( get_called_class(), 'admin_page' ) // callback
		);
	}

	// =========================
	// ! Admin Pages Output
	// =========================

	/**
	 * Output for generic settings page.
	 *
	 * @since 1.0.0
	 *
	 * @global $plugin_page The slug of the current admin page.
	 */
	public static function admin_page() {
		global $plugin_page;

		$editing = false;
		if ( isset( $_GET['pomoedit_file'] ) ) {
			$file = $_GET['pomoedit_file'];
			$path = realpath( WP_CONTENT_DIR . '/' . $file );
			if ( ! file_exists( $path ) ) {
				wp_die( sprintf( __( 'That file cannot be found: %s' ), $path ) );
			} else {
				// Load the entries
				$translation = Parser::load( $path );
				$editing = true;
			}
		}
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<?php if ( ! $editing ) : ?>
			<form method="get" action="tools.php" id="<?php echo $plugin_page; ?>-form">
				<input type="hidden" name="page" value="<?php echo $plugin_page; ?>" />

				<label for="pomoedit_file"><?php _e( 'Path to PO file:' ); ?></label>
				<input type="text" name="pomoedit_file" id="pomoedit_file" />

				<p class="submit">
					<button type="submit" class="button button-primary"><?php _e( 'Open Translation' ); ?></button>
				</p>
			</form>
			<?php else: ?>
			<form method="post" action="tools.php?page=<?php echo $plugin_page; ?>" id="<?php echo $plugin_page; ?>-form">
				<input type="hidden" name="pomoedit_file" value="<?php echo $file; ?>" />

				<h2><?php printf( __( 'Editing: <code>%s</code>' ), $file ); ?></h2>

				<table id="pomoedit-listing" class="widefat">
					<thead>
						<tr>
							<th class="pomoedit-entry-source">Source Text</th>
							<th class="pomoedit-entry-translation">Translated Text</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>

				<script>
					var POMOEDIT_TRANSLATION_DATA = <?php echo json_encode( $translation->entries ); ?>;
				</script>
			</form>
			<?php endif;?>
		</div>
		<?php
	}
}

