define([
	'app',
	'handlebars',
	'../Templates',
	'./InquiryListItem',
	"entities/models/InquiryAbstract"
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InquiryListItem = Marionette.ItemView.extend({
			template: App.Admin.Templates.inquiryListItem,
			tagName: "tr",
			ui: {
				"inquiryDetail": ".inquiry_detail_btn"
			},
			onRender: function() {
			},
			events: {
				'click @ui.inquiryDetail': function(e){
					e.preventDefault();
					var target_id = e.target.id;
					window.sessionStorage.setItem("inquiry_id", target_id);

					var cond = new Array(
						$("select[name='corporate']").val(),
						$("select[name='agreement_no']").val(),
						$("input[name='answer_kbn0']").prop('checked'),
						$("input[name='answer_kbn1']").prop('checked'),
						$("input[name='contact_day_from']").val(),
						$("input[name='contact_day_to']").val(),
						$("input[name='answer_day_from']").val(),
						$("input[name='answer_day_to']").val(),
						$("select[name='section']").val(),
						$("input[name='interrogator_name']").val(),
						$("select[name='genre']").val(),
						$("input[name='interrogator_info']").val(),
						document.getElementsByClassName("active")[0].getElementsByTagName("a")[0].text
					);
					var arr_str = cond.toString();
					//console.log(arr_str);
					// 検索項目値、ページ数のセッション保持
					window.sessionStorage.setItem("inquiry_cond", arr_str);
					location.href = "inquiry_detail.html";
				}
			}
		});
	});
});
