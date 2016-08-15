define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Behaviors', function(Behaviors, App, Backbone, Marionette, $, _){
		Behaviors.Alerts = Marionette.Behavior.extend({
			ui:{
			},
			initialize: function(){
				if (!this.options.targetSelector) {
					this.ui.alert = '.errors';
				} else {
					this.ui.alert = this.options.targetSelector;
				}
			},
			onHideAlerts: function(){
				this.ui.alert.empty();
			},
			errorView: null,
			onShowAlerts: function(messages, type){
				if (!type) {
					type = '';
				}
				if (!this.errorView) {
					this.errorsView = new (Marionette.ItemView.extend({
						template: App.Admin.Templates.alertsForBehavior,
						initialize: function(){
							this.model = new Backbone.Model();
						},
						getElement: function(messages){
							this.model.set('messages', messages);
							var icon = 'info-sign';
							if (type === 'success') {
								icon = 'ok-sign';
							} else if (type === 'info') {
								icon = 'info-sign';
							} else if (type === 'warning') {
								icon = 'exclamation-sign';
							} else {
								icon = 'exclamation-sign';
								type = 'danger';
							}
							this.model.set('type', type);
							this.model.set('icon', icon);
							this.render();
							return this.$el;
						}
					}))();
				}
				this.ui.alert.empty().append(this.errorsView.getElement(messages));
			}
		});
	});
});
