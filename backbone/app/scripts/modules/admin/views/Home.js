define([
	'app',
	'../Templates',
	'backbone.stickit'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Home = Marionette.ItemView.extend({
			template: App.Admin.Templates.home,
			model: new Backbone.Model(),
			ui: {
				    "text_1": ".text_1",
					"wearer_input": "#wearer_input",
					"wearer_end": "#wearer_end",
					"document": ".document",
					"document_li": ".document li a"
			},
			bindings: {
				'.text_1': 'text_1'
			},
			onShow: function () {
				var that = this;
				var cond = {
					"scr": 'トップページ'
				};
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.HM0010;
				modelForUpdate.fetchMx({
					data: cond,
					success: function (res) {
						var errors = res.get('errors');
						if (errors) {
							var errorMessages = errors.map(function (v) {
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}

						that.render();
						//マニュアルのファイルが存在する場合に、ホーム画面に表示
						if (res.get('manual_list')) {
							var manual_list = res.get('manual_list');
							for (var i = 0; i < manual_list.length; i++) {
								$(".document").append("<li>" + "<a id='" + manual_list[i].name + "-" + manual_list[i].file + "-" + manual_list[i].corporate + "'>" + manual_list[i].name + "</a>" + "</li>");
							}
						}
					},
					complete:function () {
						//ボタンの数を数えてゼロ個だったら、ホーム画面の１〜５の大項目を非表示にする。
						for(var i=1;i<=5; i++){
							var counter = 0;
							$('#list-box-0'+i+' li').each(function () {
								counter++;
							});
							if(counter == 0){
								$('#box-0'+i).css('display', 'none');
							}
						}
					}
				});

				//契約noの確認
				var cond = {
					"scr": '契約no'
				};
				var modelForUpdate2 = this.model;
				modelForUpdate2.url = App.api.CM0160;
				modelForUpdate2.fetchMx({
					data: cond,
					success: function (res) {
						var errors = res.get('errors');
						if (errors) {
							var errorMessages = errors.map(function (v) {
								return v.error_message;
							});
							that.triggerMethod('showAlerts', errorMessages);
						}else{
							var rntl_cont_no_list = res.get('rntl_cont_no');
							//契約noの確認
							var cond = {
								"scr": 'ホーム画面件数取得',
								"rntl_cont_no": rntl_cont_no_list,
								"rntl_cont_no_length": rntl_cont_no_list.length
							};
							var modelForUpdate = that.model;
							modelForUpdate.url = App.api.HM0012;
							modelForUpdate.fetchMx({
								data: cond,
								success: function (res) {
									var errors = res.get('errors');
									if (errors) {
										var errorMessages = errors.map(function (v) {
											return v.error_message;
										});
										that.triggerMethod('showAlerts', errorMessages);
									}else{
										$(".emply_cd_no_regist_cnt").text(res.attributes.emply_cd_no_regist_cnt);
										$(".no_recieve_cnt").text(res.attributes.no_recieve_cnt);
										$(".no_return_cnt").text(res.attributes.no_return_cnt);

									}
								}
							});
						}
					}
				});
			},

			events: {
				'click @ui.wearer_input': function(e){
					var $form = $('<form/>', {'action': '/universal/wearer_input.html', 'method': 'post'});

					window.sessionStorage.setItem('wearer_input_ref', 'home');
					window.sessionStorage.setItem('referrer', 'home');
					$form.appendTo(document.body);
					$form.submit();
				},
				'click @ui.wearer_end': function(e){
					var $form = $('<form/>', {'action': '/universal/wearer_end.html', 'method': 'post'});

					window.sessionStorage.setItem('referrer', 'home');
					$form.appendTo(document.body);
					$form.submit();
				},

				'click @ui.document_li': function(e){
					e.preventDefault();
					var manualArray = e.target.id.split("-");

					var manualData = new Object();
					manualData["name"] = manualArray[0];
					manualData["file"] = manualArray[1];
					manualData["corporate"] = manualArray[2];

					// var msg = "データ量により、ダウンロード処理に時間がかかる可能性があります。ダウンロードを実施してよろしいですか？";
					// if (window.confirm(msg)) {
						var cond = {
							"scr": 'マニュアルダウンロード',
							"cond": manualData
						};
						var form = $('<form action="' + App.api.HM0011 + '" method="post"></form>');
						var data = $('<input type="hidden" name="data" />');
						data.val(JSON.stringify(cond));
						form.append(data);
						$('body').append(form);
						form.submit();
						data.remove();
						form.remove();
						form=null;
					// }
				}
			},
		});
	});
});