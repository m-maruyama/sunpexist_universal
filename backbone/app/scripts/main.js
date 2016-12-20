/*global location */

//toLocaleStringがsafariで使えないのでその対応
(function () {
	'use strict';
	if ((1000).toLocaleString() !== "1,000") {
		Number.prototype.toLocaleString = function () {
			var neg = this < 0;
			var f = this.toString().slice(+neg).split('.');
			var r = (neg ? "-" : "") + f[0].replace(/(?=(?!^)(?:\d{3})+(?!\d))/g, ',');
			if (f.length === 2) {
				r = r + '.' + f[1];
			}
			return r;
		};
	}
})();

/*
* レンダリングの順番調整のための関数
 */
function Sleep( T ){
	var d1 = new Date().getTime();
	var d2 = new Date().getTime();
	while( d2 < d1+1000*T ){    //T秒待つ
		d2=new Date().getTime();
	}
	return;
}
require([
		'jquery',
		'backbone',
		'app',
		'backbone.marionette',
		'handlebars'
	],
	function($, Backbone, App, Marionette, Handlebars) {
		'use strict';

		//PUTとDELETEの代わりにPOST
		Backbone.emulateHTTP = true;
		App.container.networkFailure = false;

		var fetchMx = function(options) {
			if (!options) {
				options = {};
			}
			if (!options.type) {
				options.type = "POST";
			}
			if (options.data) {
				options.rawData = options.data;
				options.data = JSON.stringify(options.data);
			}
			if (!options.contentType) {
				options.contentType = "application/json";
			}
			if (!options.error) {
				options.error = function(modelOrCollection, res, options){
					if(!App.container.logout){
						switch(res.status){
							case 403:
								window.location.href = App.const.logedOutUrl;
								break;
							case 503:
								var url = location.href.substr(0, location.href.lastIndexOf("/")) + '/maintenance.html';
								window.location.href = url;
								break;
							case 0:
								App.container.networkFailure++;
								if( App.container.networkFailure > 5) {
									alert("通信エラーが発生いたしました。");
								}
								break;
							default://400,413,500等や接続不能
								alert("予期せぬエラーが発生いたしました。ブラウザの更新、ログインのし直しで改善しない場合はお問い合わせ下さい。status: " + res.status);
								console.log(res);
						}
					}
				};
			}
			var success = options.success;
			options.success = function(model, resp, options) {
				if (success) {
					success(model, resp, options);
				}
				App.container.networkFailure = 0;
			};

			if(App.container.withCredentials){
				options.xhrFields = {
					withCredentials: App.container.withCredentials
				};

			}
			//ディレイ用
			//var that = this;
			//	setTimeout(function(){
			//	that.fetch(options);
			//}, 500);
			this.fetch(options);
		};



		/**
		 * サーバー仕様に合わせてfetchを改造
		 * Model用（Collectionとわざわざ別にしてある）
		 * @param options
		 */
		Backbone.Model.prototype.fetchMx = fetchMx;

		/**
		 * サーバー仕様に合わせてfetchを改造
		 * Collection用
		 * @param options
		 */
		Backbone.Collection.prototype.fetchMx = fetchMx;

		//Handlebarsを使用するようにした。
		Marionette.TemplateCache.prototype.compileTemplate = function(rawTemplate) {
			// use Handlebars.js to compile the template
			return Handlebars["default"].compile(rawTemplate);
		};


		/**
		 * tagNameで強制的にラップされないようにした。
		 * regionで複数のエレメントが設定（セレクタにクラスを使っていたり）でも両方に描画されるようにした。
		 * @param view
		 * @param options
		 * @returns {Marionette.Region}
		 */
		Marionette.Region.prototype.showNoWrap = function(view, options){
			this._ensureElement();
			var showOptions = options || {};
			var isDifferentView = view !== this.currentView;
			var preventDestroy =  !!showOptions.preventDestroy;
			var forceShow = !!showOptions.forceShow;

			// we are only changing the view if there is a view to change to begin with
			var isChangingView = !!this.currentView;

			// only destroy the view if we don't want to preventDestroy and the view is different
			var _shouldDestroyView = !preventDestroy && isDifferentView;

			if (_shouldDestroyView) {
				this.empty();
			}

			// show the view if the view is different or if you want to re-show the view
			var _shouldShowView = isDifferentView || forceShow;

			if (_shouldShowView) {
				//view.setElement(this.el);//追加
				view.setElement(this.$el);//追加
				//_ensureElementでthis.elは一つにされちゃっているので複数の場所にviewを表示できなかったため。
				//何か副作用があるかもしれない。
				view.render();

				if (isChangingView) {
					this.triggerMethod('before:swap', view);
				}

				this.triggerMethod('before:show', view);
				this.triggerMethod.call(view, 'before:show');

				//this.attachHtml(view);//削除
				this.currentView = view;

				if (isChangingView) {
					this.triggerMethod('swap', view);
				}

				this.triggerMethod('show', view);

				if (_.isFunction(view.triggerMethod)) {
					view.triggerMethod('show');
				} else {
					this.triggerMethod.call(view, 'show');
				}

				return this;
			}
			return this;
		};

		/**
		 * イコールのヘルパーを追加
		 */
		Handlebars.registerHelper('equals', function(v1, v2, options) {
			if(v1 === v2) {
				return options.fn(this);
			}
			return options.inverse(this);
		});
		/**
		 * ノットイコールのヘルパーを追加
		 */
		Handlebars.registerHelper('notEquals', function(v1, v2, options) {
			if(v1 !== v2) {
				return options.fn(this);
			}
			return options.inverse(this);
		});
		/**
		 * 改行対応
		 */
		Handlebars.registerHelper('breaklines', function(text) {
			text = Handlebars.Utils.escapeExpression(text);
			text = text.replace(/(\r\n|\n|\r)/gm, '<br>');
			return new Handlebars.SafeString(text);
		});
		/**
		 * 全角半角変換のヘルパーを追加
		 */
		Handlebars.registerHelper('z2h', function(context) {
			var result = context;
			if(typeof context === 'function'){
				result = context.call(this);
			}
			if(typeof result === 'string'){
				result =  result.replace(/　/,' ').replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
					return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
				});
			}
			return result;
		});
		App.start();



	}
);