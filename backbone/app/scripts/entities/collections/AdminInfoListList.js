define([
	"app",
	"entities/models/AdminInfoListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminInfoListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminInfoListItem,
			url: App.api.IN0010,
			parse:function(res, xhr){
				if(res['redirect']=='1'){
					location.href = './home.html';
					return;
				}
				this.trigger('parsed',res);
				return res.list;
			}
		});
	});
});
