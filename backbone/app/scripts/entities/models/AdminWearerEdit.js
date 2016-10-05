define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminWearerEdit = Backbone.Model.extend({
			url: App.api.CM0030,
			getReq: function(){
				return {
					'no': this.get('no')
				};
			}
		});
	});
});
