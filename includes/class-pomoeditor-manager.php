<?php
/**
 * POMOEditor Manager Funtionality
 *
 * @package POMOEditor
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Management System
 *
 * Hooks into the backend to add the interfaces for
 * managing the configuration of POMOEditor.
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
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_admin() ) {
			return;
		}

		// Settings & Pages
		static::add_action( 'admin_menu', 'add_menu_pages' );
		static::add_action( 'admin_init', 'process_request' );
		static::add_action( 'admin_head', 'display_help_tabs' );
		static::add_action( 'admin_notices', 'print_notices' );
	}

	// =========================
	// ! Settings Page Setup
	// =========================

	/**
	 * Register admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @uses Manager::settings_page() for general options page output.
	 */
	public static function add_menu_pages() {
		// Main Interface page
		$interface_page_hook = add_management_page(
			__( 'PO/MO Editor', 'pomoeditor' ), // page title
			__( 'PO/MO Editor', 'pomoeditor' ), // menu title
			'manage_options', // capability
			'pomoeditor', // slug
			array( get_called_class(), 'admin_page' ) // callback
		);
	}

	/**
	 * Setup the help tabs based on what's being displayed for the page.
	 *
	 * @since 1.0.0
	 *
	 * @uses Documenter::setup_help_tabs() to display the appropriate help tabs.
	 */
	public static function display_help_tabs() {
		$screen = get_current_screen();
		// Abort if not the admin page for this plugin
		if ( $screen->id != 'tools_page_pomoeditor' ) {
			return;
		}

		// If the file is specified, setup the interface help tabs
		if ( isset( $_GET['pofile'] ) ) {
			Documenter::setup_help_tabs( 'editor' );
		}
		// Otherwise, assume it's the index
		else {
			Documenter::setup_help_tabs( 'index' );
		}
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
	public static function process_request() {
		// Skip if no file is specified
		if ( ! isset( $_REQUEST['pofile'] ) ) {
			return;
		}

		// If file was specified via $_POST, check for manage nonce action
		if ( isset( $_POST['pofile'] ) && ( ! isset( $_POST['_pomoeditor_nonce'] ) || ! wp_verify_nonce( $_POST['_pomoeditor_nonce'], 'pomoeditor-manage-' . md5( $_POST['pofile'] ) ) ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
		}

		// Check if the file exists...
		$file = $_REQUEST['pofile'];
		$path = realpath( WP_CONTENT_DIR . '/' . $file );
		if ( strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) != 'po' ) {
			wp_die( sprintf( __( 'The requested file is not supported: %s', 'pomoeditor' ), $path ), 400 );
		}
		// Check the file is a .po file
		elseif ( ! file_exists( $path ) ) {
			wp_die( sprintf( __( 'The requested file cannot be found: %s', 'pomoeditor' ), $path ), 404 );
		}
		// Check the file is within permitted path
		elseif ( ! is_path_permitted( $path ) ) {
			wp_die( sprintf( __( 'The requested file is not within one of the permitted paths: %s', 'pomoeditor' ), $path ), 403 );
		}
		// Check the file is writable
		elseif ( ! is_writable( $path ) ) {
			wp_die( sprintf( __( 'The requested file is not writable: %s', 'pomoeditor' ), $path ), 403 );
		}
		// Check if the file is being updated
		elseif ( isset( $_POST['pomoeditor_data'] ) ) {
			// Load
			$project = new Project( $path );
			$project->load();

			// Update
			$project->update( json_decode( stripslashes( $_POST['pomoeditor_data'] ), true ), true );

			// Save
			$project->export();

			// Redirect
			wp_redirect( admin_url( "tools.php?page=pomoeditor&pofile={$file}&changes-saved=true" ) );
			exit;
		}
	}

	// =========================
	// ! Settings Page Output
	// =========================

	/**
	 * Output for generic settings page.
	 *
	 * @since 1.0.0
	 */
	public static function admin_page() {
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<?php
			if ( isset( $_REQUEST['pofile'] ) ) {
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
	 *
	 * @global string $plugin_page The slug of the current admin page.
	 */
	protected static function project_index() {
		global $plugin_page;

		$projects = new Projects();
		$projects->scan();
		?>
		<div class="tablenav top">
			<div class="alignleft actions">
				<label for="filter_by_type" class="screen-reader-text"><?php _e( 'Filter by type', 'pomoeditor' ); ?></label>
				<select id="filter_by_type" class="pomoeditor-filter">
					<option value=""><?php _e( 'All types', 'pomoeditor' ); ?></option>
					<?php foreach ( $projects->types() as $type => $label ) : ?>
					<option value="<?php echo $type; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
				<label for="filter_by_package" class="screen-reader-text"><?php _e( 'Filter by package', 'pomoeditor' ); ?></label>
				<select id="filter_by_package" class="pomoeditor-filter">
					<option value=""><?php _e( 'All packages', 'pomoeditor' ); ?></option>
					<?php foreach ( $projects->packages() as $package => $label ) : ?>
					<option value="<?php echo $package; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
				<label for="filter_by_language" class="screen-reader-text"><?php _e( 'Filter by type', 'pomoeditor' ); ?></label>
				<select id="filter_by_language" class="pomoeditor-filter">
					<option value=""><?php _e( 'All languages', 'pomoeditor' ); ?></option>
					<?php foreach ( $projects->languages() as $language => $label ) : ?>
					<option value="<?php echo $language; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<table id="pomoeditor_projects" class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="manage-column column-pmeproject-file"><?php _e( 'File', 'pomoeditor' ); ?></th>
					<th class="manage-column column-pmeproject-title column-primary"><?php _e( 'Package', 'pomoeditor' ); ?></th>
					<th class="manage-column column-pmeproject-type"><?php _e( 'Type', 'pomoeditor' ); ?></th>
					<th class="manage-column column-pmeproject-language"><?php _e( 'Language', 'pomoeditor' ); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

		<script type="text/template" id="pomoeditor_item_template">
			<td class="column-pmeproject-file"><a href="<?php echo admin_url( "tools.php?page={$plugin_page}&pofile=" ); ?><%= file.dirname %>/<%= file.basename %>" target="_blank">
				<%= file.dirname %>/<strong><%= file.basename %></strong>
			</a></td>
			<td class="column-pmeproject-title"><%= pkginfo.name %></td>
			<td class="column-pmeproject-type"><%= pkginfo.type %></td>
			<td class="column-pmeproject-language"><%= language.name %></td>
		</script>

		<script>
		POMOEditor.Projects = new POMOEditor.Framework.Projects(<?php echo json_encode( $projects->dump() ); ?>);

		POMOEditor.List = new POMOEditor.Framework.ProjectsList( {
			el: document.getElementById( 'pomoeditor_projects' ),

			collection: POMOEditor.Projects,

			itemTemplate: document.getElementById( 'pomoeditor_item_template' ),
		} );
		</script>
		<?php
	}

	/**
	 * Output the Project Editor interface.
	 *
	 * @since 1.0.0
	 *
	 * @global string $plugin_page The slug of the current admin page.
	 */
	protected static function project_editor() {
		global $plugin_page;

		$file = $_REQUEST['pofile'];
		// Load
		$path = realpath( WP_CONTENT_DIR . '/' . $file );
		$project = new Project( $path );
		$project->load();

		// Figure out the text direction for the translated text
		$direction = in_array( substr( $project->language( true ), 0, 2 ), Dictionary::$rtl_languages ) ? 'rtl' : 'ltr';
		?>
		<form method="post" action="tools.php?page=<?php echo $plugin_page; ?>" id="pomoeditor">
			<input type="hidden" name="pofile" value="<?php echo $file; ?>" />
			<?php wp_nonce_field( 'pomoeditor-manage-' . md5( $file ), '_pomoeditor_nonce' ); ?>

			<h2><?php printf( __( 'Editing: <code>%s</code>', 'pomoeditor' ), $file ); ?></h2>

			<p>
			<?php printf( __( '<strong>Package:</strong> %1$s (%2$s)', 'pomoeditor' ), $project->package( 'name' ), $project->package( 'type' ) ); ?><br />
			<?php printf( __( '<strong>Language:</strong> %1$s', 'pomoeditor' ), $project->language() ); ?>
			</p>

			<h3><?php _e( 'Translations', 'pomoeditor' ); ?></h3>

			<table id="pomoeditor_translations" class="fixed striped widefat pme-direction-<?php echo $direction; ?>">
				<thead>
					<tr>
						<th class="pme-edit-col">
							<button type="button" title="<?php _e( 'Add Translation Entry', 'pomoeditor' ); ?>" class="pme-button pme-add"><?php _e( 'Add Translation Entry', 'pomoeditor' ); ?></button>
						</th>
						<th class="pme-source"><?php _e( 'Source Text', 'pomoeditor' ); ?></th>
						<th class="pme-translation"><?php _e( 'Translated Text', 'pomoeditor' ); ?></th>
						<th class="pme-context"><?php _e( 'Context', 'pomoeditor' ); ?></th>
					</tr>
				</thead>
				<tfoot></tfoot>
				<tbody></tbody>
			</table>

			<div class="pomoeditor-advanced">
				<h3><?php _e( 'Headers', 'pomoeditor' ); ?></h3>

				<table id="pomoeditor_headers" class="fixed striped widefat">
					<thead>
						<tr>
							<th class="pme-edit-col">
								<button type="button" title="<?php _e( 'Add Translation Entry', 'pomoeditor' ); ?>" class="pme-button pme-add"><?php _e( 'Add Translation Entry', 'pomoeditor' ); ?></button>
							</th>
							<th class="pme-header-name"><?php _ex( 'Name', 'header name', 'pomoeditor' ); ?></th>
							<th class="pme-header-value"><?php _ex( 'Value', 'header value', 'pomoeditor' ); ?></th>
						</tr>
					</thead>
					<tfoot></tfoot>
					<tbody></tbody>
				</table>

				<h3><?php _e( 'Metadata', 'pomoeditor' ); ?></h3>

				<table id="pomoeditor_metadata" class="fixed striped widefat">
					<thead>
						<tr>
							<th class="pme-edit-col">&nbsp;</th>
							<th class="pme-header-name"><?php _ex( 'Name', 'header name', 'pomoeditor' ); ?></th>
							<th class="pme-header-value"><?php _ex( 'Value', 'header value', 'pomoeditor' ); ?></th>
						</tr>
					</thead>
					<tfoot></tfoot>
					<tbody></tbody>
				</table>
			</div>

			<p class="submit">
				<button type="submit" id="submit" class="button button-primary"><?php _e( 'Save Translations', 'pomoeditor' ); ?></button>
				<button type="button" id="pomoeditor_advanced" class="button button-secondary"><?php _e( 'Enable Advanced Editing', 'pomoeditor' ); ?></button>
			</p>

			<script type="text/template" id="pomoeditor_record_template">
				<th class="pme-edit-col">
					<button type="button" title="Delete Record" class="pme-button pme-delete"><?php _e( 'Delete', 'pomoeditor' ); ?></button>
				</th>
				<td class="pme-record-name">
					<input type="text" class="pme-input pme-name-input" value="<%- name %>" />
				</td>
				<td class="pme-record-value">
					<input type="text" class="pme-input pme-value-input" value="<%- value %>" />
				</td>
			</script>

			<script type="text/template" id="pomoeditor_translation_template">
				<td class="pme-edit-col">
					<button type="button" title="Edit Entry" class="pme-button pme-edit"><?php _e( 'Edit', 'pomoeditor' ); ?></button>
					<div class="pme-actions">
						<button type="button" title="Cancel (discard changes)" class="pme-button pme-cancel"><?php _e( 'Cancel', 'pomoeditor' ); ?></button>
						<button type="button" title="Save Changes" class="pme-button pme-save"><?php _e( 'Save', 'pomoeditor' ); ?></button>
						<button type="button" title="Delete Entry" class="pme-button pme-delete"><?php _e( 'Delete', 'pomoeditor' ); ?></button>
					</div>
				</td>
				<td class="pme-source">
					<div class="pme-previews">
						<div class="pme-preview pme-singular" title="<?php _e( 'Singular', 'pomoeditor' ); ?>"><%= singular %></div>
						<div class="pme-preview pme-plural" title="<?php _e( 'Plural', 'pomoeditor' ); ?>"><%= plural %></div>
					</div>
					<div class="pme-inputs">
						<textarea class="pme-input pme-singular" title="<?php _e( 'Singular', 'pomoeditor' ); ?>" rows="4" readonly><%- singular %></textarea>
						<textarea class="pme-input pme-plural" title="<?php _e( 'Plural', 'pomoeditor' ); ?>" rows="4" readonly><%- plural %></textarea>
					</div>
				</td>
				<td class="pme-translated">
					<div class="pme-previews">
						<div class="pme-preview pme-singular" title="<?php _e( 'Singular', 'pomoeditor' ); ?>"><%= translations[0] %></div>
						<div class="pme-preview pme-plural" title="<?php _e( 'Plural', 'pomoeditor' ); ?>"><%= translations[1] %></div>
					</div>
					<div class="pme-inputs">
						<textarea class="pme-input pme-singular" title="<?php _e( 'Singular', 'pomoeditor' ); ?>" rows="4"><%- translations[0] %></textarea>
						<textarea class="pme-input pme-plural" title="<?php _e( 'Plural', 'pomoeditor' ); ?>" rows="4"><%- translations[1] %></textarea>
					</div>
				</td>
				<td class="pme-context">
					<div class="pme-previews">
						<div class="pme-preview"><%= context %></div>
					</div>
					<div class="pme-inputs">
						<textarea class="pme-input" rows="4" readonly><%- context %></textarea>
					</div>
				</td>
			</script>

			<script>
			POMOEditor.Project = new POMOEditor.Framework.Project(<?php echo json_encode( $project->dump() ); ?>);

			POMOEditor.HeadersEditor = new POMOEditor.Framework.RecordsEditor( {
				el: document.getElementById( 'pomoeditor_headers' ),

				collection: POMOEditor.Project.Headers,

				rowTemplate: document.getElementById( 'pomoeditor_record_template' ),
			} );

			POMOEditor.MetadataEditor = new POMOEditor.Framework.RecordsEditor( {
				el: document.getElementById( 'pomoeditor_metadata' ),

				collection: POMOEditor.Project.Metadata,

				rowTemplate: document.getElementById( 'pomoeditor_record_template' ),
			} );

			POMOEditor.TranslationsEditor = new POMOEditor.Framework.TranslationsEditor( {
				el: document.getElementById( 'pomoeditor_translations' ),

				collection: POMOEditor.Project.Translations,

				rowTemplate: document.getElementById( 'pomoeditor_translation_template' ),
			} );
			</script>
		</form>
		<?php
	}

	/**
	 * Print any necessary notices.
	 *
	 * @since 1.0.0
	 */
	public static function print_notices() {
		// Return if not on the editor page
		if ( get_current_screen()->id != 'tools_page_pomoeditor' || ! isset( $_GET['pofile'] ) ) {
			return;
		}

		// Print update notice if changes were saved
		if ( isset( $_GET['changes-saved'] ) && $_GET['changes-saved'] ) {
			?>
			<div class="updated notice is-dismissible">
				<p><strong><?php _e( 'Translations saved and recompiled.', 'pomoeditor' ); ?></strong></p>
			</div>
			<?php
		}
	}
}
