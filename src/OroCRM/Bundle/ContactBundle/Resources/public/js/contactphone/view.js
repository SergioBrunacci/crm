/* global define */
define(['underscore', 'backbone', 'jquery.select2'],
function(_, Backbone) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  orocrm/contactphone/view
     * @class   orocrm.contactphone.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({

        /**
         * List of events
         *
         * @property
         */        
        events: {
            'change': 'selectionChanged'
        },

        /**
         * Select element of contact's phones numbers.
         *
         * @property
         */
        phonesList: null,

        /**
         * Input field for phone number
         *
         * @property
         */
        phonePlain: null,

        /**
         * Phone list template
         *
         * @property
         */
        phonesListTemplate: _.template(
            '<% _.each(contactphones, function(p, i) { %>' + 
                '<option <% if (p.get("primary")) { %> selected="selected" <% } %> value=<%= p.get("id") %>><%= p.get("phone") %></option>' +
            '<% }); %>' +
                '<option value="">...</option>'
        ),         
        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function(options) {
            
            this.phonesList = $(options.target);
            this.phonePlain = $(options.simpleEl);

            this.phonesList.closest('.controls').append(this.phonePlain);
            this.phonesList.on('change', _.bind(function(e) {
                if ($(e.target.selectedOptions).val() == "") {
                    this.showPlain(false);
                } 
            }, this));
            
            this.phonePlain.attr('type', 'text');

            if (!options.showSelect) {
                this.phonePlain.show();
            } else {
                this.phonePlain.hide();
            }

            this.displaySelect2(options.showSelect);
            this.phonesList.on('select2-init', _.bind(function() {
                this.displaySelect2(options.showSelect);
            }, this));

            this.listenTo(this.collection, 'reset', this.render);
        },

        /**
         * Show/hide select 2 element
         *
         * @param {Boolean} display
         */
        displaySelect2: function(display) {
            if (display) {
                this.phonesList.select2('container').show();
            } else {
                this.phonesList.select2('container').hide();
            }
        },

        /**
         * onChange event listener
         *
         * @param e {Object}
         */
        selectionChanged: function(e) {
            var contactId = $(e.currentTarget).val();
            console.log(contactId);
            if (contactId) {
                this.collection.setContactId(contactId);
                this.collection.fetch();
            } else {
                this.showPlain(true);
            }
        },

        /**
         * Render list and or input field
         */
        render: function() {
            if (this.collection.models.length > 0) {
                this.showOptions();
            } else {                
                this.showPlain(true);
            }
        },
        
        /**
         * Show plain phone input field
         */        
        showPlain: function(hide) {
            if (hide) {
                this.phonesList.hide();
                this.displaySelect2(false);                
                $('#uniform-' + this.phonesList[0].id).hide();                
            }
                this.phonePlain.show();            
        },

        /**
         * Show phone seleciton dropdown
         */
        showOptions: function() {
                this.phonesList.show();
                this.displaySelect2(true);
                $('#uniform-' + this.phonesList[0].id).show();
                this.phonesList.find('option[value!=""]').remove();
                this.phonesList.append(this.phonesListTemplate({contactphones: this.collection.models}));
                this.phonesList.trigger('change');
                this.phonePlain.hide();
                this.phonePlain.val('');
        }
    });
});
