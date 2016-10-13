define([
	'app',
	'../Templates',
	'backbone.stickit',
	'bootstrap-datetimepicker',
	'../behaviors/Alerts',
	'../controllers/ImportCsv',
	'typeahead',
	'blockUI',
	'bloodhound'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ImportCsvCondition = Marionette.LayoutView.extend({
			template: App.Admin.Templates.importCsvCondition,
			model: new Backbone.Model(),
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"agreement_no": ".agreement_no",
			},
			ui: {
				'agreement_no': '#agreement_no',
				'csv_form': '#csv_form',
				'import_csv': '.import_csv',
				'file_input': '#file_input',
			},

			onShow: function() {
				//var that = this;
				//var modelForUpdate = this.model;
				//	modelForUpdate.url = App.api.IM0020;
				//var cond = {
				//	"scr": 'CSV取込'
				//};
				//	modelForUpdate.fetchMx({
				//		data:cond,
				//		success:function(res){
				//			var errors = res.get('errors');
				//			if(errors) {
				//				that.render();
				//				$("#open").prop("disabled", true);
				//				$("#import_csv").prop("disabled", true);
				//				alert(errors);
				//				return;
				//			}
				//			that.render();
				//		}
				//	});
			},
		events: {
			'click @ui.import_csv': function(e){
				e.preventDefault();
				var that = this;
				if(!$("#file_input").prop("files")[0]){
					this.triggerMethod('showAlerts', new Array('取込ファイル名が未入力です。'));
					return;
				}
				var fileName = ($("#file_input").prop("files")[0]);
				var type = fileName.name.split('.');
				var fileExt = type[type.length - 1].toLowerCase();
				if (( fileExt == 'csv') || (fileExt == 'xlsx' )) {

					$("#open").prop("disabled", true);
					$("#import_csv").prop("disabled", true);

					if(confirm($("#file_input").prop("files")[0].name + 'を取り込んでもよろしいですか？')){
						$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });

						var fd = new FormData();

						if ( $("#file_input").val() !== '' ) {
							fd.append( "file", $("#file_input").prop("files")[0] );
						}
						var url = App.api.IM0010;
						var postData = {
							type : "POST",
							data : fd,
							processData : false,
							contentType : false,
							dataType: "json",
							timeout: 600000
						};

						// ajax送信
						$.ajax( url, postData ).done(function( res ){
							console.log(res);
							console.log('done');

							var errors = res['errors'];
							if(errors) {
								var errorMessages = new Array();
								that.triggerMethod('showAlerts', errors.slice(0,20));
								alert('CSVデータの登録に失敗しました。');
								$("#open").prop("disabled", false);
								$("#import_csv").prop("disabled", false);
								$.unblockUI();
							} else {
								alert('CSVデータを登録しました。');
								$("#open").prop("disabled", false);
								$("#import_csv").prop("disabled", false);
								$.unblockUI();
							}
						});
						that.render();
					}else{
						$("#open").prop("disabled", false);
						$("#import_csv").prop("disabled", false);
					}

				}else{
					alert('拡張子がcsvまたはxlsxではありません。');


				}






			}//click ui.import_csv
		}//event
		});
	});
});