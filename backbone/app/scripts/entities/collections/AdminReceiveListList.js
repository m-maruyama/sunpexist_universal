define([
	"app",
	"entities/models/AdminReceiveListItem",
	"lib/ecl"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminReceiveListList = Backbone.Collection.extend({
			model: App.Entities.Models.AdminReceiveListItem,
			url: App.api.RE0010,
			parse:function(res, xhr){
				this.trigger('parsed',res);
				// var args = '';
				// if(res.mode == 'download'){
					// var all_array = new Array();
					// for (var i in res.csv_list) {
						// var list =$.makeArray(res.csv_list[i]);
						// var value_plus = '';
						// var val = $.map( list[0], function( value, index ) {
							// if(index == 'receipt_status'){
								// $.each(value,function(index2,val2){
									// if (val2 == 1) {
										// value = " 未受領";
									// } else if (val2 == 2) {
										// value = " 受領済";
									// }
								// });
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
