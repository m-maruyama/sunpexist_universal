define([
	'app',
	'../Templates',
	'blockUI',
	'./PrintListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'order_req_no';
		Views.PrintListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.printListList,
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.printEmpty,
            }),
			childView: Views.PrintListItem,
			childViewContainer: "tbody",
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
					var order = this.model.get('order');
					if(this.model.get('sort_key') == e.target.id){
						if(order=='asc'){
							order = 'desc';
						}else{
							order = 'asc';
						}
					} else {
						//ソートキーが変更された場合は昇順
						order = 'asc';
					}
					var sort_key = e.target.id;

					this.model.set('sort_key',sort_key);
					this.model.set('order',order);
					this.triggerMethod('sort', e.target.id,order);
				}
			},
			fetch:function(printListConditionModel){
				var cond = {
					"scr": '返却伝票印刷',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": printListConditionModel.getReq()
				};
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				this.collection.fetchMx({
					data: cond,
					success: function(res){
						$.unblockUI();
                        console.log(res)
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
