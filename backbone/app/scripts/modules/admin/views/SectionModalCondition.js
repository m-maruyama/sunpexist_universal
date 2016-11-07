define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'typeahead',
	'bloodhound'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionModalCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.sectionModalCondition,
			ui: {
				'search': '.search',
				'rntl_sect_cd': '#rntl_sect_cd',
				'rntl_sect_name': '#rntl_sect_name'
			},
			bindings: {
				'rntl_sect_cd': '#rntl_sect_cd',
				'rntl_sect_name': '#rntl_sect_name'
			},
			onRender: function() {
			},
			events: {
				'click @ui.search': function(e){
					e.preventDefault();
					this.triggerMethod('hideAlerts');
					this.model.set('rntl_sect_cd', this.ui.rntl_sect_cd.val());
					this.model.set('rntl_sect_name', this.ui.rntl_sect_name.val());
					this.model.set('agreement_no', $('#agreement_no').val());
					if (document.getElementById("corporate") != null) {
						this.model.set('corporate', $('#corporate').val());
						this.model.set('corporate_flg', true);
					}
					// this.model.set('sort_key', 'rntl_sect_cd');
					// this.model.set('order','asc');
					var errors = this.model.validate();
					if(errors) {
						this.triggerMethod('showAlerts', errors);
						return;
					}
					this.triggerMethod('click:section_search',this.model.get('sort_key'),this.model.get('order'));
					//$('#modal_page').css('display', '');
					//$('#modal_listTable').css('display', '');
				},
			},
			fetchSection: function(model) {
				var that = this;
				this.model = model;
				this.model.fetchMx({
					data:this.model.getReq(),
					success: function(){
						that.triggerMethod('fetched');
					}
				});
			},

		});
	});
});
