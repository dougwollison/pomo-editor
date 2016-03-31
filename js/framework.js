/* globals _, Backbone, pomoeditL10n, confirm */
( function( $ ) {
	var POMOEdit = window.POMOEdit = {};
	var Framework = POMOEdit.Framework = {};

	// =========================
	// ! Miscellaneous
	// =========================

	POMOEdit.advanced = false; // Wether or not advanced editing is enabled

	// =========================
	// ! Models/Collections
	// =========================

	var Record = Framework.Record = Backbone.Model.extend( {
		defaults: {
			name: '',
			value: '',
		}
	} );

	var Records = Framework.Records = Backbone.Collection.extend( {
		model: Record,

		reset: function( models, options ) {
			if ( ! ( models instanceof Array ) ) {
				var _models = [];
				for ( var k in models ) {
					_models.push( { name: k, value: models[ k ] } );
				}
				models = _models;
			}

			Backbone.Collection.prototype.reset.call( this, models, options );
		}
	} );

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

	var Project = Framework.Project = Backbone.Model.extend( {
		defaults: {
			file: {},
			language: {},
			pkginfo: {},
		},

		constructor: function( attributes, options ) {
			this.Headers = new Records();
			this.Metadata = new Records();
			this.Translations = new Translations();

			if ( attributes.po_headers ) {
				this.Headers.reset( attributes.po_headers );
				//delete attributes.po_headers;
			}
			if ( attributes.po_metadata ) {
				this.Metadata.reset( attributes.po_metadata );
				//delete attributes.po_metadata;
			}
			if ( attributes.po_entries ) {
				this.Translations.reset( attributes.po_entries );
				//delete attributes.po_entries;
			}

			Backbone.Model.call( this, attributes, options );
		}
	} );

	var Projects = Framework.Projects = Backbone.Collection.extend( {
		model: Project
	} );

	// =========================
	// ! Views
	// =========================

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

	// =========================
	// ! - Editor Rows
	// =========================

	var EditorRow = Framework.EditorRow = Backbone.View.extend( {
		tagName: 'tr',

		className: 'pme-row',

		events: {
			'click .pme-delete': 'destroy',
			'change .pme-input': 'save',
		},

		isBlank: function() {
			return this.$el.find( 'input,textarea' ).val() === '';
		},

		remove: function() {
			this.$el.remove();
		},

		initialize: function( options ) {
			this.model.view = this;

			if ( options.template ) {
				if ( options.template instanceof HTMLElement ) {
					options.template = options.template.innerHTML;
				}

				this.template = _.template( options.template );
			}

			this.listenTo( this.model, 'destroy', this.remove );

			this.render();
		},

		render: function() {
			var template = this.template( this.model.attributes );
			this.$el.html( template );
			return this;
		},

		destroy: function() {
			if ( ! this.isBlank() && ! confirm( pomoeditL10n.ConfirmDelete ) ) {
				return;
			}

			this.model.destroy();
		}
	} );

	var RecordRow = Framework.RecordRow = EditorRow.extend( {
		events: {
			'click .pme-delete': 'destroy',
			'keyup .pme-name-input': 'updateName',
			'keyup .pme-value-input': 'updateValue',
		},

		updateName: function( e ) {
			// Only upate if advanced editing is enabled
			if ( POMOEdit.advanced ) {
				this.model.set( 'name', $( e.target ).val() );
			}
		},

		updateValue: function( e ) {
			console.log(e.target);
			// Only upate if advanced editing is enabled
			if ( POMOEdit.advanced ) {
				this.model.set( 'value', $( e.target ).val() );
			}
		}
	} );

	var TranslationRow = Framework.TranslationRow = EditorRow.extend( {
		isOpen: false,

		className: 'pme-translation',

		events: {
			'click .pme-delete': 'destroy',
			'click .pme-edit': 'toggle',
			'click .pme-save': 'save',
			'click .pme-cancel': 'close',
			'change .pme-input': 'checkChanges',
		},

		initialize: function() {
			this.$el.toggleClass( 'has-context', this.model.get( 'context' ) !== null );
			this.$el.toggleClass( 'has-plural', this.model.get( 'is_plural' ) );
			this.$el.toggleClass( 'no-plural', !this.model.get( 'is_plural' ) );

			this.listenTo( this.model, 'change:singular change:plural', this.renderSource );
			this.listenTo( this.model, 'change:translations', this.renderTranslation );

			EditorRow.prototype.initialize.apply( this, arguments );
		},

		renderSource: function() {
			var singular = this.model.get( 'singular' );
			var plural = this.model.get( 'plural' );

			this.$el.find( '.pme-source .pme-preview.pme-singular' ).html( singular );
			this.$el.find( '.pme-source .pme-input.pme-singular' ).val( singular );

			this.$el.find( '.pme-source .pme-preview.pme-plural' ).html( plural );
			this.$el.find( '.pme-source .pme-input.pme-plural' ).val( plural );
		},

		renderTranslation: function() {
			var translations = this.model.get( 'translations' );

			this.$el.find( '.pme-translated .pme-preview.pme-singular' ).html( translations[0] );
			this.$el.find( '.pme-translated .pme-input.pme-singular' ).val( translations[0] );

			this.$el.find( '.pme-translated .pme-preview.pme-plural' ).html( translations[1] );
			this.$el.find( '.pme-translated .pme-input.pme-plural' ).val( translations[1] );
		},

		renderContext: function() {
			var context = this.model.get( 'context' );

			this.$el.find( '.pme-context .pme-preview' ).html( context );
			this.$el.find( '.pme-context .pme-input' ).val( context );
		},

		checkChanges: function() {
			this.$el.addClass( 'changed' );
		},

		toggle: function() {
			return this.isOpen ? this.close() : this.open();
		},

		open: function() {
			this.$el.addClass( 'open' );
			this.isOpen = true;
			return this;
		},

		close: function( e, noconfirm ) {
			if ( this.$el.hasClass( 'changed' ) && noconfirm !== true ) {
				if ( confirm( pomoeditL10n.ConfirmCancel ) ) {
					// Reset
					this.renderSource();
					this.renderTranslation();
					this.renderContext();
					this.$el.removeClass( 'changed' );
				} else {
					return;
				}
			}

			this.$el.removeClass( 'open' );
			this.isOpen = false;
			return this;
		},

		save: function() {
			// Only save context/source changes if advanced editing is enabled
			if ( POMOEdit.advanced ) {
				this.model.set( 'context', this.$el.find( '.pme-context .pme-input' ).val() );
				this.model.set( 'singular', this.$el.find( '.pme-source .pme-input.pme-singular' ).val() );
				this.model.set( 'plural', this.$el.find( '.pme-source .pme-input.pme-plural' ).val() );
			}

			this.model.set( 'translations', [
				this.$el.find( '.pme-translation .pme-input.pme-singular' ).val(),
				this.$el.find( '.pme-translation .pme-input.pme-plural' ).val()
			] );

			this.$el.removeClass( 'changed' );
			this.close();

			this.model.isSaved = true;
		}
	} );

	// =========================
	// ! - Editors
	// =========================

	var Editor = Framework.Editor = Backbone.View.extend( {
		events: {
			'click .pme-add': 'addEntry',
		},

		entryView: Backbone.View,

		initialize: function( options ) {
			// Save the row template
			this.rowTemplate = options.rowTemplate;

			// Generate the rows for each entry
			this.collection.each( this.addEntry.bind( this ) );
		},

		addEntry: function( entry ) {
			// Create a blank entry if no valid Translation was provided
			if ( ! ( entry instanceof this.entryModel ) ) {
				entry = new this.entryModel();
				this.collection.add( entry );
			}

			// Creat the row and add it
			var row = new this.entryView( {
				model: entry,
				template: this.rowTemplate,
			} );
			row.$el.appendTo( this.$el.find( 'tbody' ) );

			return row;
		}
	} );

	var RecordsEditor = Framework.RecordsEditor = Editor.extend( {
		entryModel: Record,
		entryView: RecordRow,

		addEntry: function( entry ) {
			// Abort if adding a new entry while not in advanced editing mode
			if ( ! ( entry instanceof this.entryModel ) && ! POMOEdit.advanced ) {
				return;
			}

			Editor.prototype.addEntry.apply( this, arguments );
		}
	} );

	var TranslationsEditor = Framework.TranslationsEditor = Editor.extend( {
		entryModel: Translation,
		entryView: TranslationRow,

		addEntry: function() {
			var row = Editor.prototype.addEntry.apply( this, arguments );

			// If newly generated, open for editing
			if ( row.isBlank() ) {
				row.open();
			}
		}
	} );
} )( jQuery );