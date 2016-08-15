define([
	"app",
	"entities/models/AdminDeliveryListItem",
	"lib/ecl"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminDeliveryListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminDeliveryListItem,
			url: App.api.DE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				// var args = '';
				// if(res.mode == 'download'){
					// var all_array = new Array();
					// for (var i in res.csv_list) {
						// var list =$.makeArray(res.csv_list[i]);
						// var value_plus = '';
						// var val = $.map( list[0], function( value, index ) {
						// var str = '';
							// if(index == 'order_status'){
								// if (value == 1) {
									// str = "未出荷";
								// } else if (value == 2) {
									// str = "出荷済";
								// }
								// value_plus = str;
							// }
							// if(index == 'receipt_status'){
								// if (value == 1) {
									// value = value_plus + " 未受領";
								// } else if (value == 2) {
									// value = value_plus + " 受領済";
								// }
								// value_plus = '';
							// }
							// if(index == 'order_sts_kbn'){
								// if (value == 1) {
								  // value = "貸与";
								// } else if (value == 2) {
									// value = "サイズ交換";
								// } else if (value == 3) {
									// value = "消耗交換";
								// } else if (value == 4) {
									// value = "異動";
								// }
							// }
							// return value;
						// });
						// all_array.push(val);
					// }
					// downloadCsv(all_array);
				// }
				return res.list;
			}
		});
	});
});
