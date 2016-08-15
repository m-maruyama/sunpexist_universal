define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.AdminAcount = Backbone.Model.extend({
			url: App.api.AC0010,
			
			getReq: function() {
				var result = {
					user_id : null,
					user_name : null,
					position_name : null,
					user_type : null,
					lock : null,
					edit : null,
					del : null
				};
				if(this.get('user_id')) {
					result.user_id = this.get('user_id');
				}
				if(this.get('user_name')) {
					result.user_name = this.get('user_name');
				}
				if(this.get('position_name')) {
					result.position_name = this.get('position_name');
				}
				if(this.get('user_type')) {
					result.user_type = this.get('user_type');
				}
				if(this.get('lock')) {
					result.lock = this.get('lock');
				}
				if(this.get('edit')) {
					result.edit = this.get('edit');
				}
				if(this.get('del')) {
					result.del = this.get('del');
				}
				return result;
			}
		});
	});
});
