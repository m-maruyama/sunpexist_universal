define([
	"app",
	"entities/models/AdminaccountListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminaccountListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminaccountListItem,
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
