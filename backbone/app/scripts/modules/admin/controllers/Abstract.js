define([
	'app',
	'../views/Nav',
	'../views/Footer',
	"entities/models/KeepSession",
	'bootstrap'
], function(App) {
	'use strict';
	App.module('Admin.Controllers', function(Controllers,App, Backbone, Marionette, $, _){
		Controllers.Abstract = Marionette.Controller.extend({
			keepSessionModel: null,
			navView:null,
			footView:null,
			setKeepSessionModel: function(){
				this.keepSessionModel = new App.Entities.Models.KeepSession();
			},
			keepSession: function(){
				this.keepSessionModel.fetchMx({
					dataType: 'text'//レスポンスが空なのでjsonだとエラーになる
				});
			},
			top: function(){
				this.setKeepSessionModel();
				var that = this;
				this.keepSessionModel.once('sync', function(){
					that._sync.call(that);
				});
				this.keepSession();
			},
			_sync: function(){},
			setNav: function(active){
				this.navView = new App.Admin.Views.Nav({'active': active});
				App.nav.show(this.navView);
				this.footView = new App.Admin.Views.Footer({'active': active});
				App.footer.show(this.footView);
			}
		});
	});
	return App.Admin.Controllers.Abstract;
});
