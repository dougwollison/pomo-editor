/* globals _, POMOEdit */
jQuery( function( $ ) {
	var $filters = {
		type: $( '#filter-by-type' ),
		slug: $( '#filter-by-package' ),
		lang: $( '#filter-by-language' )
	};

	$( '.pomoedit-filter' ).change( function() {
		var filter = {
			type: $filters.type.val(),
			slug: $filters.slug.val(),
			lang: $filters.lang.val()
		};

		var visible = {
			type: [],
			slug: [],
			lang: []
		};

		_( POMOEdit.List.children ).each( function( view ) {
			view.$el.show();

			var type = view.model.get( 'pkginfo' ).type,
				slug = view.model.get( 'pkginfo' ).slug,
				lang = view.model.get( 'language' ).code;

			if ( filter.type && type !== filter.type ){
				view.$el.hide();
				return;
			}

			if ( filter.slug && slug !== filter.slug ){
				view.$el.hide();
				return;
			}

			if ( filter.lang && lang !== filter.lang ){
				view.$el.hide();
				return;
			}

			visible.type.push( type );
			visible.slug.push( slug );
			visible.lang.push( lang );
		} );

		visible.type = _( visible.type ).uniq();
		visible.slug = _( visible.slug ).uniq();
		visible.lang = _( visible.lang ).uniq();

		_( $filters ).each( function( $filter, key ) {
			$filter.find( 'option' ).show();
			if ( ! filter[ key ] ) {
				$filter.find( 'option[value!=""]' ).each( function() {
					if ( _( visible[ key ] ).indexOf( $( this ).attr( 'value' ) ) === -1 ) {
						$( this ).hide();
					}
				} );
			}
		} );
	} );

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
