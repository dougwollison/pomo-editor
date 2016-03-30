/* globals _, Backbone, pomoeditL10n, confirm */
( function( $ ) {
	var POMOEdit = window.POMOEdit = {};
	var Framework = POMOEdit.Framework = {};

	var Translation = Framework.Translation = Backbone.Model.extend( {
		defaults: {
			is_plural: false,
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

			this.on( 'change:translations', function() {
				var translations = this.get( 'translations' );
				if ( ! this.get( 'is_plural' ) ) {
					this.attributes.translations = translations.splice( 0, 1 );
				}
			} );
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
			'click .pme-edit': 'toggle',
			'click .pme-save': 'save',
			'click .pme-cancel': 'close',
			'change .pme-input': 'checkChanges',
		},

		initialize: function( options ) {
			this.model.view = this;

			if ( options.template ) {
				if ( options.template instanceof HTMLElement ) {
					options.template = options.template.innerHTML;
				}

				this.template = _.template( options.template );
			}

			this.$el.toggleClass( 'has-context', this.model.get( 'context' ) !== null );
			this.$el.toggleClass( 'has-plural', this.model.get( 'is_plural' ) );
			this.$el.toggleClass( 'no-plural', !this.model.get( 'is_plural' ) );

			this.listenTo( this.model, 'change:singular change:plural', this.renderSource );
			this.listenTo( this.model, 'change:translations', this.renderTranslation );

			this.render();
		},

		render: function( fresh ) {
			var template = this.template( this.model.attributes );
			this.$el.html( template );
			return this;
		},

		renderSource: function() {
			var singular = this.model.get( 'singular' );
			var plural = this.model.get( 'plural' );

			this.$el.find( '.pme-source .pme-value.pme-singular' ).html( singular );
			this.$el.find( '.pme-source .pme-input.pme-singular' ).val( singular );

			this.$el.find( '.pme-source .pme-value.pme-plural' ).html( plural );
			this.$el.find( '.pme-source .pme-input.pme-plural' ).val( plural );
		},

		renderTranslation: function() {
			var translations = this.model.get( 'translations' );

			this.$el.find( '.pme-translation .pme-value.pme-singular' ).html( translations[0] );
			this.$el.find( '.pme-translation .pme-input.pme-singular' ).val( translations[0] );

			this.$el.find( '.pme-translation .pme-value.pme-plural' ).html( translations[1] );
			this.$el.find( '.pme-translation .pme-input.pme-plural' ).val( translations[1] );
		},

		checkChanges: function() {
			this.$el.addClass( 'changed' );
		},

		toggle: function( e ) {
			if ( e && $( e.target ).hasClass( 'pme-input' ) ) {
				return this;
			}

			this.$el.toggleClass( 'open' );
			return this;
		},

		close: function( e, noconfirm ) {
			if ( this.$el.hasClass( 'changed' ) && noconfirm !== true ) {
				if ( confirm( pomoeditL10n.ConfirmCancel ) ) {
					this.renderSource();
					this.renderTranslation();
					this.$el.removeClass( 'open' );
				}
			} else {
				this.$el.removeClass( 'open' );
			}
		},

		save: function() {
			this.model.set( 'singular', this.$el.find( '.pme-source .pme-input.pme-singular' ).val() );
			this.model.set( 'plural', this.$el.find( '.pme-source .pme-input.pme-plural' ).val() );

			this.model.set( 'translations', [
				this.$el.find( '.pme-translation .pme-input.pme-singular' ).val(),
				this.$el.find( '.pme-translation .pme-input.pme-plural' ).val()
			] );

			this.close();
		}
	} );

	var Project = Framework.Project = Backbone.Model.extend( {
		defaults: {
			file: {},
			language: {},
			pkginfo: {},
			po_headers: {},
			po_metadata: {},
		},

		constructor: function() {
			if ( arguments[0].po_entries ) {
				this.Translations = new Translations( arguments[0].po_entries );
				delete arguments[0].po_entries;
			}

			Backbone.Model.apply( this, arguments );
		}
	} );

	var ProjectItem = Framework.ProjectItem = Backbone.View.extend( {
		tagName: 'tr',

		initialize: function( options ) {
			if ( options.template ) {
				if ( options.template instanceof HTMLElement ) {
					options.template = options.template.innerHTML;
				}

				this.template = _.template( options.template );
			}
		},

		render: function( fresh ) {
			var template = this.template( this.model.attributes );
			this.$el.html( template );
			return this;
		}
	} );

	var ProjectTable = Framework.ProjectTable = Backbone.View.extend( {
		initialize: function( options ) {
			this.model.Translations.each( function( entry ) {
				var row = new TranslationRow( {
					model: entry,
					template: options.rowTemplate,
				} );

				row.$el.appendTo( this.$el.find( 'tbody' ) );
			}.bind( this ) );
		}
	} );

	var Projects = Framework.Projects = Backbone.Collection.extend( {
		model: Project
	} );

	var ProjectsList = Framework.ProjectsList = Backbone.View.extend( {
		initialize : function( options ) {
			this.collection = options.collection || new Projects();
			this.children = [];

			options.collection.each( function( project ) {
				this.children.push( new ProjectItem( {
					model: project,
					template: options.itemTemplate
				} ) );
			}.bind( this ) );

			this.render();
		},

		render: function() {
			this.$el.find( 'tbody' ).empty();

			_( this.children ).each( function( view ) {
				this.$el.find( 'tbody' ).append( view.render().el );
			}.bind( this ) );
		}
	} );
} )( jQuery );