define([
	'app',
	'../Templates',
	'../behaviors/Alerts',
	'bootstrap',
	'bootstrap-datetimepicker'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoModal = Marionette.ItemView.extend({
			template: App.Admin.Templates.infoModal,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'display': '.display',
				'modal': '#info_modal',
				'upBtn': '.update',
				'close': '.close',
				'cancel': '.cancel',
				'title': '#infoTitle',
				'index': '.index',
				'display_order': '#display_order',
				'open_date': '#open_date',
				'close_date': '#close_date',
				'message': '#message',
				'display_type': '#display_type',
				'datetimepicker': '.datetimepicker'
			},
			bindings: {
			},
			onRender: function() {
				this.ui.datetimepicker.datetimepicker({
					format: 'YYYY/MM/DD HH:mm',
					//useCurrent: false,
					minDate: new Date(),
					locale: 'ja',
					sideBySide:true
				});
			},
			events: {
				"click @ui.close": function(e){
					var model = new this.collection.model();
					this.collection.unshift(model);
					this.ui.modal.modal('hide');
					this.reset();
				},
				"click @ui.cancel": function(e){
					var model = new this.collection.model();
					this.collection.unshift(model);
					this.ui.modal.modal('hide');
					this.reset();
				},
				"click @ui.upBtn": function(e){
					e.preventDefault();
					var model = new this.collection.model();
					model.set('index', this.ui.index.val());
					model.set('display_order', this.ui.display_order.val());
					model.set('open_date', this.ui.open_date.val());
					model.set('close_date', this.ui.close_date.val());
					model.set('message', this.ui.message.val());
					model.set('display_type', this.ui.display_type.val());
					var that = this;
					var errors = model.validate();
					if (errors){
						this.triggerMethod('showAlerts', errors);
						return;
					}
					
					model.url = App.api.IN0020;
					var cond = {
						"scr": this.ui.display.text(),
						"type": this.ui.display_type.val(),
						"cond": model.getReq()
					};
					model.fetchMx({
						data:cond,
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								that.triggerMethod('showAlerts', errors);
								return;
							}
							that.collection.unshift(model);
							that.ui.modal.modal('hide');
							var type = that.ui.display_type.val();
							if( type == '1'){
								alert('お知らせを編集しました。');
							}else if(type == '2'){
								alert('お知らせを削除しました。');
							}else {
								alert('お知らせを登録しました。');
							}
							that.reset();
							that.triggerMethod('reload');
						}
						
					});
					
				}
			},
			reset: function(){
				this.triggerMethod('hideAlerts');
				this.ui.index.val('');
				this.ui.display_order.val('');
				this.ui.open_date.val('');
				this.ui.close_date.val('');
				this.ui.message.val('');
				this.ui.display.text('お知らせ追加');
				this.ui.upBtn.text('追加');
				this.ui.index.text('');
				this.ui.display_type.val('');
				this.ui.display_order.attr('readonly',false);
				this.ui.open_date.attr('readonly',false);
				this.ui.close_date.attr('readonly',false);
				this.ui.message.attr('readonly',false);
			},
			showMessage: function(model, display_type) {
				this.ui.modal.modal('show');
				this.ui.display_type.val(display_type);
					this.ui.index.val(model.get('index'));
					this.ui.index.text('ID : ' + model.get('index'));
					this.ui.display_order.val(model.get('display_order'));
					this.ui.open_date.val(model.get('open_date'));
					this.ui.close_date.val(model.get('close_date'));
					this.ui.message.val(model.get('message'));
				if (display_type === '1') {
					var display_str = 'お知らせ編集';
					var button_str = '更新';
				} else if (display_type === '2') {
					var display_str = '以下のお知らせを削除してもよろしいですか？';
					var button_str = '削除';
					this.ui.display_order.attr('readonly',true);
					this.ui.open_date.attr('readonly',true);
					this.ui.close_date.attr('readonly',true);
					this.ui.message.attr('readonly',true);
				} else { 
					var display_str = 'お知らせ追加';
					var button_str = '追加';
				}
				this.ui.display.text(display_str);
				this.ui.upBtn.text(button_str);
			}
		});
	});
});