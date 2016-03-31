=== PO/MO Edit ===
Contributors: dougwollison
Tags: pomo, po file, mo file, gettext, file editor
Requires at least: 4.0.0
Tested up to: 4.5.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Edit gettext .po files within WordPress.

== Description ==

PO/MO Edit adds a versatile interface to WordPress for editing and recompiling translation files.

= .PO File Searching =

Any .po files found within the wp-content directory will be listed for editing, with the associated project name and language identified for easier organization and filtering to find what file you need to edit.

Should you want to limit what directories are scanned, you can define the `POMOEDIT_SCAN_BLACKLIST` and `POMOEDIT_SCAN_WHITELIST` constants with a list of directories to exclude or exclusively include (preferably in your wp-config.php file). Multiple paths can be separated by a colon (:), similar to `$PATH` in Linux.

= Basic and Advanced File Editing =

The editor by default only allows you to edit the translated text value of each translation entry. If you need to edit the source text or context values, you can click *Enable Advanced Editing*, which will also enable editing of the files headers and other metadata.

Each entry must be explicitly saved or the changes to be recorded. When you're done making changes to the file, click *Save Translations* to have the .po updated an the .mo file recompiled from it.

As a precaution, the system will backup the original files before overwriting them with the updated data. You will however need to manually restore them via FTP or some other file management method.

== Installation ==

1. Upload the contents of `pomoedit.tar.gz` to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go into the plugin interface page under Tools > PO/MO Editor.
4. Start editing your gettext files.

== Changelog ==

**Details on each release can be found [on the GitHub releases page](https://github.com/dougwollison/pomoedit/releases) for this project.**

= 1.0 =
Initial public release.