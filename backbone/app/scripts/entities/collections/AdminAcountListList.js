define([
	"app",
	"entities/models/AdminAcountListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminAcountListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminAcountListItem,
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
