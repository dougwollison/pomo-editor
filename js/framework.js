/* globals _, Backbone */
( function( $ ) {
	var POMOEdit = window.POMOEdit = {};
	var Framework = POMOEdit.Framework = {};

	var Translation = Framework.Translation = Backbone.Model.extend( {
		defaults: {
			context: '',
			singular: '',
			plural: '',
			translations: [],
			translator_comments: '',
			extracted_comments: '',
			references: [],
			flags: [],
		},

		initialize: function() {
			if ( ! ( this.attributes.translations instanceof Array ) ) {
				this.attributes.translations = [];
			}
			if ( ! ( this.attributes.references instanceof Array ) ) {
				this.attributes.references = [];
			}
			if ( ! ( this.attributes.flags instanceof Array ) ) {
				this.attributes.flags = [];
			}
		},

		key: function() {
			var key;

			if ( this.get( 'singular' ) === null || this.get( 'singular' ) === '' ) {
				key = this.cid;
			}

			if ( this.get( 'context' ) ) {
				key = this.get( 'context' ) + String.fromCharCode( 4 ) + this.get( 'singular' );
			} else {
				key = this.get( 'singular' );
			}

			key = key.replace( /[\r\n]+/, '\n' );

			return key;
		},

		mergeWith: function( entry ) {
			this.set( 'flags', this.get( 'flags' ).concat( entry.get( 'flags' ) ) );
			this.set( 'references', this.get( 'references' ).concat( entry.get( 'references' ) ) );

			if ( this.get( 'extracted_comments' ) !== entry.get( 'extracted_comments' ) ) {
				this.set( 'extracted_comments', this.get( 'extracted_comments' ) + entry.get( 'extracted_comments' ) );
			}
		}
	} );

	var Translations = Framework.Translations = Backbone.Collection.extend( {
		model: Translation
	} );

	var TranslationRow = Framework.TranslationRow = Backbone.View.extend( {
		tagName: 'tr',

		className: 'pme-entry',

		events: {
			'click': 'toggle',
		},

		initialize: function( options ) {
			if ( options.template ) {
				if ( options.template instanceof HTMLElement ) {
					options.template = options.template.innerHTML;
				}

				this.template = _.template( options.template );
			}

			this.$el.toggleClass( 'has-context', this.model.get( 'context' ) !== null );
			this.$el.toggleClass( 'has-plural', this.model.get( 'plural' ) !== null );

			this.listenTo( this.model, 'change', this.render );
			this.render();
		},

		render: function( fresh ) {
			var template = this.template( this.model.attributes );
			this.$el.html( template );
			return this;
		},

		toggle: function( e ) {
			if ( e && ( ! $( e.target ).hasClass( 'pme-entry' ) && ! $( e.target ).hasClass( 'pme-value' ) ) ) {
				return this;
			}

			this.$el.toggleClass( 'open' );
			if ( this.$el.hasClass( 'open' ) ) {
				this.$el.siblings().removeClass( 'open' );
			}
			return this;
		}
	} );

	var Project = Framework.Project = Backbone.Model.extend( {
		constructor: function() {
			this.Translations = new Translations( arguments[0].entries );
			delete arguments[0].entries;

			Backbone.Model.apply( this, arguments );
		}
	} );

	var ProjectTable = Framework.ProjectTable = Backbone.View.extend( {
		initialize: function( options ) {
			console.log(options);
			this.model.Translations.each( function( entry ) {
				var row = new TranslationRow( {
					model: entry,
					template: options.rowTemplate,
				} );

				row.$el.appendTo( this.$el.find( 'tbody' ) );
			}.bind( this ) );
		}
	} );
} )( jQuery );