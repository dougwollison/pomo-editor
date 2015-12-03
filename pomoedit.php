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
Text Domain: POMOEdit
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
define( 'PM_SELF', __FILE__ );

/**
 * Reference to the plugin directory.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PM_DIR', __DIR__ );

/**
 * Shortcut for the TextDomain.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PM_TXTDMN', 'POMOEdit' );

// =========================
// ! Includes
// =========================

require( PM_DIR . '/inc/autoloader.php' );
require( NL_DIR . '/inc/functions-pomoedit.php' );

// =========================
// ! Setup
// =========================

POMOEdit\System::setup();
