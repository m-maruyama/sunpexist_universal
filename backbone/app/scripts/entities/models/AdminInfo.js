define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminInfo = Backbone.Model.extend({
			url: App.api.IN0010,
			
			getReq: function() {
				var result = {
					index : null,
					display_order : null,
					open_date : null,
					close_date : null,
					message : null
				};
				if(this.get('index')) {
					result.index = this.get('index');
				}
				if(this.get('display_order')) {
					result.display_order = this.get('display_order');
				}
				if(this.get('open_date')) {
					result.open_date = this.get('open_date');
				}
				if(this.get('close_date')) {
					result.close_date = this.get('close_date');
				}
				if(this.get('message')) {
					result.message = this.get('message');
				}
				return result;
			}
		});
	});
});
