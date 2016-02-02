<?php
/*
Plugin Name: PO/MO Edit
Plugin URI: https://github.com/dougwollison/pomo-edit
Description: Edit gettext po/mo files within WordPress.
Version: 1.0.0
Author: Doug Wollison
Author URI: http://dougw.me
Tags: pomo, po file, mo file, gettext, file editor
License: GPL2
Text Domain: pomoedit
*/

// =========================
// ! Constants
// =========================

/**
 * Reference to the plugin file.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'POMOEDIT_PLUGIN_FILE', __FILE__ );

/**
 * Reference to the plugin directory.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'POMOEDIT_PLUGIN_DIR', dirname( POMOEDIT_PLUGIN_FILE ) );

// =========================
// ! Includes
// =========================

require( POMOEDIT_PLUGIN_DIR . '/inc/autoloader.php' );
require( POMOEDIT_PLUGIN_DIR . '/inc/functions-pomoedit.php' );

// =========================
// ! Setup
// =========================

POMOEdit\System::setup();
