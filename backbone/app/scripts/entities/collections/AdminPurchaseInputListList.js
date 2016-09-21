define([
	"app",
	"entities/models/AdminPurchaseInputListCondition"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminPurchaseInputListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminPurchaseInputListCondition,
			url: App.api.PI0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);//phpからjsonのデータを受け取る
				// test.val() = res.page['total_record'];
				console.log(res);
				$('#rntl_cont_no_val').val(res.list['rntl_cont_no']);
				$('#total_records').val(res.page['total_records']);
				return res.list;
			}
		});
	});
});
