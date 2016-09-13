define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminWearer = Backbone.Model.extend({
			url: App.api.WE0020,
			getReq: function(){
				return {
					'no': this.get('no')
				};
			}
		});
	});
});
