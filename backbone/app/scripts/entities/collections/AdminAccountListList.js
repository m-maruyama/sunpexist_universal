define([
	"app",
	"entities/models/AdminAccountListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminAccountListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminAccountListItem,
			url: App.api.AC0010,
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
