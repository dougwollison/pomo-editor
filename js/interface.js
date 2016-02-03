/* globals POMOEdit */
jQuery( function( $ ) {
	$( '#pomoedit-manage' ).submit( function() {
		var Project = POMOEdit.Project;
		var $storage = $('<textarea name="pomoedit_data"></textarea>').hide().appendTo(this);

		var data = {
			entries: []
		};
		for ( var attr in Project.attributes ) {
			data[attr] = Project.get( attr );
		}

		Project.Translations.each( function( entry ) {
			data.entries.push( entry.attributes );
		} );

		$storage.val( JSON.stringify( data ) );
	} );
} );
