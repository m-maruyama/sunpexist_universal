define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.Pager = Backbone.Model.extend({
			initialize: function(data){
				var that = this;

				this.defaults = _.extend(this.defaults,data);

				this.calc();
				this.on("change:records_per_page change:page_number change:total_records",function(){
					if(this.attributes.page_number === 0){
						this.attributes.page_number = this.defaults.page_number;
					}
					if(this.attributes.records_per_page === 0){
						this.attributes.records_per_page = this.defaults.records_per_page;
					}
					that.calc();
				});
			},
			/**
			 * ページャーに必要な情報を計算する
			 */
			calc: function() {
				var pageNumber = this.get("page_number");
				var totalPage = Math.ceil(this.get("total_records") / this.get("records_per_page"));
				var delta = this.get("delta");
				var left = Math.floor((delta - 1) / 2);
				var right = delta - left - 1;
				var pages = [];
				var s = pageNumber - left;
				var e = pageNumber + right;
				if (1 > s) {
					e = e - s + 1;
					s = 1;
				}
				if(totalPage < e) {
					s = s - ( e - totalPage );
					e = totalPage;
				}
				if(s < 1) {
					s = 1;
				}
				if(e < 1) {
					s = 0;
				} else {
					var i = s;
					for (i;i<=e;i++) {
						pages.push(i);
					}
				}
				var start_num = (pageNumber - 1)*delta;
				if(start_num==0){
					start_num = 1;
				}
				var end_num = pageNumber*delta;
				this.set({
					total_page: totalPage,
					pages: pages,
					first: s > 1,
					last: e < totalPage,
					next: totalPage > pageNumber,
					prev: pageNumber > 1,
					start_num: start_num,
					end_num: end_num,
					prev_page: pageNumber - 1,
					next_page: pageNumber + 1
				});
			},
			defaults:{
				records_per_page: 20,
				page_number: 1,
				total_records: 0,
				total_page: 0,
				delta: 15,
				pages: [],
				first: false,//最初へ戻るフラグ
				last: false,//最後へ戻るフラグ
				prev: false,//前へがあるかフラグ
				next: false,//次へがあるかフラグ,
				start_num: 0,
				end_num: 0,
				prev_page:0,
				next_page:0
			},
			getPageRequest: function() {
				return {
					"sort_key" : this.get("sort_key"),
					"order" : this.get("order"),
					"records_per_page" : this.get("records_per_page"),
					"page_number" : this.get("page_number")
				};
			}
		});
	});
});
