define([
	'app',
	'../Templates',
	'blockUI',
	'./AcountListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AcountListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.acountListList,
			//emptyView: Backbone.Marionette.ItemView.extend({
      //          tagName: "tr",
			//	template: App.Admin.Templates.lendEmpty,
      //      }),
			childView: Views.AcountListItem,
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
			fetch:function(acountListConditionModel){
				var cond = {
					"scr": 'アカウント管理',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": acountListConditionModel.getReq()
				};
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

				this.collection.fetchMx({
					data: cond,
					success: function(model,res,req){
						that.model.set('mode',null);
						$.unblockUI();
					},
					complete:function(res){
						$.unblockUI();
						// 個体管理番号表示/非表示制御
						//if (res.responseJSON.individual_flag.valueOf()) {
						//	$('.tb_individual_num').css('display','');
						//}
					},


				});
			}


		});
	});
});
