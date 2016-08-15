define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Pagination = Marionette.ItemView.extend({
			template: App.Admin.Templates.pagination,
			initialize: function(){
				var that = this;
				this.listenTo(this.model, 'change',function(){
					that.render();
				});
			},
			ui: {
			},
			onRender: function(){
			},
			events:{
				"click a": function(e) {
					e.preventDefault();
					this.triggerMethod("selected", parseInt($(e.currentTarget).attr("data-page"), 10));
				}
			}

		});
	});
});