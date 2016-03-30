<title><?php _e( 'Overview', 'pomoedit' ); ?></title>

<p><?php _e( 'This screen allows you to edit the individual translation entries in the selected file. ', 'pomoedit' ); ?></p>

<p><?php printf( __( 'Click the %s icon to open an entry for editing. By default, the source inputs are disabled from editing; click to enable them should you actually want to change these. When you’re done with your changes to an entry, click the %2$s icon to save, or otherwise the %3$s icon to discard your changes. ', 'pomoedit' ), '<i class="dashicons dashicons-edit"></i>', '<i class="dashicons dashicons-yes"></i>', '<i class="dashicons dashicons-no"></i>' ); ?></p>

<p><?php printf( __( 'By default, editing of the source text and context is disabled, since you would need to edit the associated package’s PHP code to match. If you know what you’re doing though and need to edit these, click the %s icon to enable advanced editing.' ), '<i class="dashicons dashicons-lock"></i>' ); ?></p>

<p><?php _e( 'When you’re done editing translation entries, click the <strong>Save Translations</strong> button to update the .po file and regenerate the .mo file.', 'pomoedit' ); ?> <em><?php _e( 'It is recommended you backup the original translation files to be safe, in the event an error occurs with updating/compiling.', 'pomoedit' ); ?></em></p>

<p><?php _e( 'Some entries include a <em>context</em> qualifier, which allows multiple translations of the same basic text for different uses. The context for an entry cannot be changed.', 'pomoedit' ); ?></p>