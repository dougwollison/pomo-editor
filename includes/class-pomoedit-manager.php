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
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Manager extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	final public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_backend() ) {
			return;
		}

		// Settings & Pages
		static::add_action( 'admin_menu', 'add_menu_pages' );
		static::add_action( 'admin_init', 'register_settings' );
	}

	// =========================
	// ! Utilities
	// =========================

	// to be written

	// =========================
	// ! Settings Page Setup
	// =========================

	/**
	 * Register admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @uses Manager::settings_page() for general options page output.
	 * @uses Documenter::register_help_tabs() to register help tabs for all screens.
	 */
	final public static function add_menu_pages() {
		// Main Interface page
		$interface_page_hook = add_management_page(
			__( 'PO/MO Editor' ), // page title
			__( 'PO/MO Editor' ), // menu title
			'manage_options', // capability
			'pomoedit', // slug
			array( get_called_class(), 'admin_page' ) // callback
		);

		// Setup the help tabs for each page
		Documenter::register_help_tabs( array(
			$interface_page_hook => 'interface',
		) );
	}

	// =========================
	// ! Settings Saving
	// =========================

	/**
	 * Check if a file is specified for loading.
	 *
	 * Also save changes to it if posted.
	 *
	 * @since 1.0.0
	 */
	final public static function process_request() {
		// Skip if no file is specified
		if ( ! isset( $_REQUEST['pomoedit_file'] ) ) {
			return;
		}

		// If file was specified via $_POST, check for manage nonce action
		if ( isset( $_POST['pomoedit_file'] ) && ( ! isset( $_POST['_pomoedit_nonce'] ) || ! wp_verify_nonce( $_POST['_pomoedit_nonce'], 'pomoedit-manage-' . md5( $_POST['pomoedit_file'] ) ) ) ) {
			cheatin();
		}

		// Check if the file exists...
		$file = $_REQUEST['pomoedit_file'];
		$path = realpath( WP_CONTENT_DIR . '/' . $file );
		if ( ! file_exists( $path ) ) {
			wp_die( sprintf( __( 'That file cannot be found: %s' ), $path ) );
		} else {
			// Load the file
			$project = new Project( $path );
			$project->load();

			// Check if update info was passed
			if ( isset( $_POST['pomoedit_data'] ) ) {
				// Update
				$project->update( json_decode( stripslashes( $_POST['pomoedit_data'] ), true ) );
				// Save
				$project->export();
			}

			// Stash it in the cache for global access
			wp_cache_set( 'pomoedit', $project, $file );
		}
	}

	// =========================
	// ! Settings Page Output
	// =========================

	/**
	 * Output for generic settings page.
	 *
	 * @since 1.0.0
	 *
	 * @global string $plugin_page The slug of the current admin page.
	 */
	final public static function admin_page() {
		global $plugin_page;
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<?php
			if ( isset( $_GET['pomoedit_file'] ) ) {
				static::project_editor();
			} else {
				static::project_index();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Output the Project Index interface.
	 *
	 * @since 1.0.0
	 */
	final protected static function project_index() {
		$projects = new Projects();
		$projects->scan();
		?>
		<table id="pomoedit-projects" class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th id="pmeproject-file" class="manage-column column-pmeproject-file"><?php _e( 'File' ); ?></th>
					<th id="pmeproject-title" class="manage-column column-pmeproject-title column-primary"><?php _e( 'Project' ); ?></th>
					<th id="pmeproject-type" class="manage-column column-pmeproject-type"><?php _e( 'Type' ); ?></th>
					<th id="pmeproject-language" class="manage-column column-pmeproject-language"><?php _e( 'Language' ); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

		<script type="text/template" id="pomoedit-item-template">
			<td class="column-pmeproject-file"><%= file.dirname %>/<strong><%= file.basename %></strong></td>
			<td class="column-pmeproject-title"><%= pkginfo.name %></td>
			<td class="column-pmeproject-type"><%= pkginfo.type %></td>
			<td class="column-pmeproject-language"><%= language.name %></td>
		</script>

		<script>
		POMOEdit.Projects = new POMOEdit.Framework.Projects(<?php echo json_encode( $projects->dump() ); ?>);

		POMOEdit.List = new POMOEdit.Framework.ProjectsList( {
			el: document.getElementById( 'pomoedit-projects' ),

			collection: POMOEdit.Projects,

			itemTemplate: document.getElementById( 'pomoedit-item-template' ),
		} );
		</script>
		<?php
	}

	/**
	 * Output the Project Editor interface.
	 *
	 * @since 1.0.0
	 */
	final protected static function project_editor() {
		$file = $_GET['pomoedit_file'];
		// Load the file from the cache
		$project = wp_cache_get( 'pomoedit', $file );
		?>
		<form method="post" action="tools.php?page=<?php echo $plugin_page; ?>" id="<?php echo $plugin_page; ?>-manage">
			<input type="hidden" name="pomoedit_file" value="<?php echo $file; ?>" />
			<?php wp_nonce_field( 'pomoedit-manage-' . md5( $file ), '_pomoedit_nonce' ); ?>

			<h2><?php printf( __( 'Editing: <code>%s</code>' ), $file ); ?></h2>

			<table id="pomoedit-editor" class="fixed striped widefat">
				<thead>
					<tr>
						<th class="pme-source"><?php _e( 'Source Text' ); ?></th>
						<th class="pme-translation"><?php _e( 'Translated Text' ); ?></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>

			<?php submit_button( __( 'Update Project' ) ); ?>

			<script type="text/template" id="pomoedit-entry-template">
				<td class="pme-entry pme-source" data-context="<%- context %>">
					<span class="pme-value pme-singular"><%- singular %></span>
					<span class="pme-value pme-plural"><%- plural %></span>

					<div class="pme-fields">
						<textarea class="pme-input pme-singular"><%- singular %></textarea>
						<textarea class="pme-input pme-plural"><%- plural %></textarea>

						<button type="button" class="pme-save button button-secondary"><?php _e( 'Save' ); ?></button>
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
			POMOEdit.Project = new POMOEdit.Framework.Project(<?php echo json_encode( $project->dump() ); ?>);

			POMOEdit.Editor = new POMOEdit.Framework.ProjectTable( {
				el: document.getElementById( 'pomoedit-editor' ),

				model: POMOEdit.Project,

				rowTemplate: document.getElementById( 'pomoedit-entry-template' ),
			} );
			</script>
		</form>
		<?php
	}
}