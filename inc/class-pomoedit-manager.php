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

		// Pages
		static::add_action( 'admin_menu', 'add_menu_pages' );

		// Processing
		static::add_action( 'admin_init', 'process_request' );
	}

	// =========================
	// ! Utilities
	// =========================

	/**
	 * Scan a directory for .po files.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dir The directory to search.
	 *
	 * @return array The list of files found.
	 */
	protected static function find_projects( $dir ) {
		$files = array();

		foreach ( scandir( $dir ) as $file ) {
			if ( substr( $file, 0, 1 ) == '.' ) {
				continue;
			}

			$path = "$dir/$file";
			// If it's a directory (but not a link) scan it
			if ( is_dir( $path ) && ! is_link( $path ) ) {
				$files = array_merge( $files, static::find_projects( $path ) );
			} else
			// If it's a file with the .po extension, add it
			if ( is_file( $path ) && substr( $file, -3 ) === '.po' ) {
				$files[] = $path;
			}
		}

		return $files;
	}

	/**
	 * Attempt to identify a .po file.
	 *
	 * Will identify language and project context if possible.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file    The path to the file to identify.
	 * @param string $context A context to use for identifying the project.
	 *
	 * @return string An identifying name for the file.
	 */
	protected static function identify_project( $file, $context ) {
		// First, identify the language.
		$language = 'Unknown Language';
		if ( preg_match( '/(.*?)-?(\w+)\.po$/', $file, $matches ) ) {
			list(, $project, $language ) = $matches;
		} else {
			return basename( $file );
		}

		// Get and cache all themes/plugins available
		if ( ! $themes = wp_cache_get( 'pomoedit', 'all themes' ) ) {
			$themes = wp_get_themes();
			wp_cache_set( 'pomoedit', $themes, 'all themes' );
		}
		if ( ! $plugins = wp_cache_get( 'pomoedit', 'all plugins' ) ) {
			$plugins = get_plugins();
			wp_cache_set( 'pomoedit', $plugins, 'all plugins' );
		}

		switch ( $context ) {
			case 'theme':
				$path = substr( $file, strlen( WP_CONTENT_DIR . '/themes/' ) );
				$path = explode( '/', $path );
				$project = array_shift( $path );

				if ( isset( $themes[ $project ] ) ) {
					$project = $themes[ $project ]->name;
				}
				break;

			case 'plugin':
				$path = substr( $file, strlen( WP_PLUGIN_DIR . '/' ) );
				$path = explode( '/', $path );
				$project = array_shift( $path );

				foreach ( $plugins as $basename => $data ) {
					if ( dirname( $basename ) == $project ) {
						$project = $data['Name'];
						break;
					}
				}
				break;

			default:
				$path = substr( $project, strlen( WP_CONTENT_DIR . '/languages/' ) );
				$path = explode( '/', $path );
				$project = array_shift( $path );

				switch ( $project ) {
					case 'themes':
						// It's for a theme, fake the path and run it again as such
						$path = WP_CONTENT_DIR . '/themes/' . implode( '/', $path ) . '/' . $language . '.po';
						return static::identify_project( $path, 'theme' );

					case 'plugins':
						// It's for a plugin, fake the path and run it again as such
						$path = WP_PLUGIN_DIR . '/' . implode( '/', $path ) . '/' . $language . '.po';
						return static::identify_project( $path, 'plugin' );

					case 'admin':
						$project = 'WordPress Admin';
						break;
					case 'admin-network':
						$project = 'WordPress Network Admin';
						break;
					case '':
						$project = 'WordPress Core';
						break;
				}
				break;
		}

		return sprintf( '%s [%s]', $project, Dictionary::identify_language( $language ) );
	}

	// =========================
	// ! Admin Page Setup
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
	// ! Admin Page Processing
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
	// ! Admin Page Output
	// =========================

	/**
	 * Output for generic settings page.
	 *
	 * @since 1.0.0
	 *
	 * @global string $plugin_page The slug of the current admin page.
	 */
	public static function admin_page() {
		global $plugin_page;
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<?php if ( ! isset( $_GET['pomoedit_file'] ) ) : ?>
			<form method="get" action="tools.php" id="<?php echo $plugin_page; ?>-open">
				<input type="hidden" name="page" value="<?php echo $plugin_page; ?>" />

				<p>
					<label for="pomoedit_file"><?php _e( 'Select PO File to Edit' ); ?></label>
					<select name="pomoedit_file" id="pomoedit_file">
						<option value=""><?php _e( 'Select File' ); ?></option>
						<?php if ( $files = static::find_projects( WP_CONTENT_DIR . '/languages' ) ) :?>
						<optgroup label="Installed Languages">
							<?php foreach ( $files as $file ) : ?>
							<option value="<?php echo $file; ?>"><?php echo static::identify_project( $file ); ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endif;?>
						<?php if ( $files = static::find_projects( WP_CONTENT_DIR . '/themes' ) ) :?>
						<optgroup label="Installed Themes">
							<?php foreach ( $files as $file ) : ?>
							<option value="<?php echo $file; ?>"><?php echo static::identify_project( $file, 'theme' ); ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endif;?>
						<?php if ( $files = static::find_projects( WP_PLUGIN_DIR ) ) :?>
						<optgroup label="Installed Plugins">
							<?php foreach ( $files as $file ) : ?>
							<option value="<?php echo $file; ?>"><?php echo static::identify_project( $file, 'plugin' ); ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endif;?>
					</select>
				</p>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php _e( 'Open Translation' ); ?></button>
				</p>
			</form>
			<?php else:
			$file = $_GET['pomoedit_file'];
			// Load the file from the cache
			$project = wp_cache_get( 'pomoedit', $file );
			?>
			<form method="post" action="tools.php?page=<?php echo $plugin_page; ?>" id="<?php echo $plugin_page; ?>-manage">
				<input type="hidden" name="pomoedit_file" value="<?php echo $file; ?>" />
				<?php wp_nonce_field( 'pomoedit-manage-' . md5( $file ), '_pomoedit_nonce' ); ?>

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

