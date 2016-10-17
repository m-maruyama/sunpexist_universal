define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminInquiry = Backbone.Model.extend({
			url: App.api.MI0020,
			getReq: function(){
				return {
					'no': this.get('no')
				};
			}
		});
	});
});
