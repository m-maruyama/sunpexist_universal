define([
	"app",
	"entities/models/AdminHome"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminHome = Backbone.Collection.extend({
			model: App.Entities.Models.AdminHome,
			url: App.api.HM0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return {
					'emply_cd_no_regist_cnt': res.emply_cd_no_regist_cnt,
					'no_recieve_cnt': res.no_recieve_cnt,
					'no_return_cnt': res.no_return_cnt,
					'list': res.list
				};
			}
		});
	});
});
