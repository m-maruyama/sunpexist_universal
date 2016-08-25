define([
	"app",
//	"entities/models/AdminHistoryListItem"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminCsvDownload = Backbone.Collection.extend({
//			model: App.Entities.Models.AdminCsvDownloadCondition,
			url: App.api.DL0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				return res;
			}
		});
	});
});
