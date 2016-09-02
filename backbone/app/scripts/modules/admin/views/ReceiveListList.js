define([
	'app',
	'../Templates',
	'./ReceiveListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'ship_no';
		Views.ReceiveListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.receiveListList,
			childView: Views.ReceiveListItem,
			childViewContainer: "tbody",
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.receiveEmpty,
            }),
			ui: {
			},
			onRender: function() {
				this.listenTo(this.collection, 'parsed', function(res){
					this.options.pagerModel.set(res.page);
				});
			},
			events: {
				"click .sort": function(e) {
					e.preventDefault();
					var that = this;
					//同じソートキーの場合は昇順降順切り替え
					if(sort_key == e.target.id){
						if(order=='asc'){
							order = 'desc';
						}else{
							order = 'asc';
						}
					} else {
						//ソートキーが変更された場合は昇順
						order = 'asc';
					}
					sort_key = e.target.id;
					this.triggerMethod('sort', e.target.id,order);
				}
			},
			fetch:function(receiveListConditionModel){
				var cond = {
					"scr": '受領確認',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": receiveListConditionModel.getReq()
				};
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				this.collection.fetchMx({
					data: cond,
					success: function(model){
						that.model.set('mode',null);
						for(var i=0;i<model.models.length;i++){
							var m = model.models[i];
							if(m.get('receipt_status')==2){
								m.set('updateFlag',true);
							}else{
								m.set('updateFlag',false);
							}
						}
						$.unblockUI();
					},
					complete:function(res){
						that.model.set('mode',null);
						$.unblockUI();
						// 個体管理番号表示/非表示制御
						if (res.responseJSON.individual_flag.valueOf()) {
							$('.tb_individual_num').css('display','');
						}
					}
				});
			}


		});
	});
});
