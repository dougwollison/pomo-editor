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

				<table id="pomoedit-listing" class="fixed striped widefat">
					<thead>
						<tr>
							<th class="pme-source"><?php _e( 'Source Text' ); ?></th>
							<th class="pme-translation"><?php _e( 'Translated Text' ); ?></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>

				<script type="text/template" id="pomoedit-entry-template">
					<td class="pme-entry pme-source" data-context="<%- context %>">
						<span class="pme-value pme-singular"><%- singular %></span>
						<span class="pme-value pme-plural"><%- plural %></span>

						<div class="pme-fields">
							<textarea class="pme-input pme-singular"><%- singular %></textarea>
							<textarea class="pme-input pme-plural"><%- plural %></textarea>
						</div>
					</td>
					<td class="pme-entry pme-translation">
						<span class="pme-value pme-singular"><%- translations[0] %></span>
						<span class="pme-value pme-plural"><%- translations[1] %></span>

						<div class="pme-fields">
							<textarea class="pme-input pme-singular"><%- translations[0] %></textarea>
							<textarea class="pme-input pme-plural"><%- translations[1] %></textarea>
						</div>
					</td>
				</script>

				<script>
				POMOEdit.Project = new POMOEdit.Framework.Project(<?php echo json_encode( $translation ); ?>);

				POMOEdit.Editor = new POMOEdit.Framework.ProjectTable( {
					el: document.getElementById( 'pomoedit-listing' ),

					model: POMOEdit.Project,

					rowTemplate: document.getElementById( 'pomoedit-entry-template' ),
				} );
				</script>
			</form>
			<?php endif;?>
		</div>
		<?php
	}
}

