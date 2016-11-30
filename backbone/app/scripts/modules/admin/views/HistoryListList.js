define([
	'app',
	'../Templates',
	'blockUI',
	'./HistoryListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'order_req_no';
		Views.HistoryListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.historyListList,
			emptyView: Backbone.Marionette.ItemView.extend({
            	tagName: "tr",
				template: App.Admin.Templates.historyEmpty,
      	}),
			childView: Views.HistoryListItem,
			childViewContainer: "tbody",
			ui: {
			},
			onRender: function() {
				this.listenTo(this.collection, 'parsed', function(res){
					this.options.pagerModel.set(res.page);
				});
			},
			onShow: function(e) {
			},
			events: {
				"click .sort": function(e) {
					e.preventDefault();
					var that = this;
					//同じソートキーの場合は昇順降順切り替え
					if(sort_key == e.target.id){
						if(order == 'asc'){
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
			fetch:function(historyListConditionModel){
				var cond = {
					"scr": '発注状況照会',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": historyListConditionModel.getReq()
				};
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				this.collection.fetchMx({
					data: cond,
					success: function(model){
						$.unblockUI();
					},
					complete:function(res){
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
