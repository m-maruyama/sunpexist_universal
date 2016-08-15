define([
	'jquery',
	'flotr2',
	'chartUtils'
], function ($, flotr2, utils) {

	"use strict";

	/**
	 * construtor
	 */
	var MainChart = function(anyOptions){

		//グラフ描画取得用のプロパティ
		this.data = '';

		//各グラフの名称を定義
		this.n_ohlc = "ohlc";
		this.n_px   = "px";
		this.n_twap = "twap";
		this.n_volume = "volume";
		this.n_averageVolume = "averageVolume";
		this.n_balanceChangePlus = "balanceChangePlus";
		this.n_balanceChangeMinus = "balanceChangeMinus";
		this.n_balanceChangePlusAvg = "balanceChangePlusAvg";
		this.n_balanceChangeMinusAvg = "balanceChangeMinusAvg";
		this.n_basePrice = "basePrice";

		// グラフ描画フラグ初期化
		this.f_ohlc = true;
		this.f_px = true;
		this.f_twap = true;
		this.f_volume = true;
		this.f_avgVolume = true;
		this.f_balPlus = true;
		this.f_balMinus = true;
		this.f_balPlusAvg = true;
		this.f_balMinusAvg = true;
		this.f_basePrice = true;

		//データ配列
		this.d_ohlc = [];		// 四本値（ローソク）
		this.d_px = [];		  // 価格(折れ線)
		this.d_twap = [];		// TWAP(折れ線)
		this.d_volume = [];	  // 当日の累積出来高(折れ線)
		this.d_avgVolume = [];   // 過去5日間の平均累積出来高(折れ線 y軸2)
		this.d_balChange = [];   // 板バランス変化(三角プロット)
		this.d_balPlusAvg = [];  // 正の板バランス変化の平均(水平線)
		this.d_balMinusAvg = []; // 負の板バランス変化の平均(水平線)
		this.d_basePrice = [];   // 前日の終値(水平線)
		this.d_base = [];		// x軸,y軸固定用
		this.d_base2 = [];	  // x2軸,y2軸固定用

		// Y2軸生成用 最大値
		this.m_maxDataY2 = 0;

		// mainChartのオプションデフォルト値
		this.m_options = {
			ignoreMouseDown: true,
			width: 480, //canvas幅
			height: 320, //canvas高さ
			y2TitleMargin:27, //Y2軸のtitleの長さに合わせたマージンを設定
			textColor: [255, 255, 255, 1],
			backgroundColor: [0, 0, 0, 0], //背景色
			onMouseOverRange: 5,//マウスオーバーで出るボードのレンジ（分）
			xaxis: {
				showLabels: true,			   //横（項目）軸ラベル
				color_cd:[255, 255, 255, 1],	   //線,目盛の色
				showLine: false,				//軸の線
				smallTickNum: 3,				//小目盛の数
				tickLength: 8,				  //目盛の長さ
				tickLengthShort: 4,			 //短い目盛の長さ
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1,				   //線の幅
					labelMargin:3			   //ラベルマージン 軸位置などの調整用
				}
			},
			yaxis: {  //左Y軸 必須：最小値、目盛幅、目盛数
				showLabels: true,			   //縦（値）軸ラベル
				color_cd:[255, 255, 255, 1],	   //線,目盛の色
				showLine: false,				//軸の線を描画するかどうか
				min: 0,						 //最小値
				tickSize: 0,					//目盛幅
				tickNum: 0,					 //目盛の数
				tickLength: 8,				  //目盛の長さ
				tickLengthShort: 4,			 //短い目盛の長さ
				smallTickNum: 5,				//小目盛の数
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1,				   //線の幅
					labelMargin:10			  //ラベルマージン 軸位置などの調整用
				}
			},
			yaxis2: {//右Y軸  必須：最小値、目盛幅、目盛数
				showLabels: true,			  //縦（値）軸ラベル
				color_cd:[255, 255, 255, 1],	  //線,目盛の色
				showLine: false,			   //軸の線
				min: 0,						//最小値
				tickSize: 0,				   //目盛幅
				tickNum: 4,					//目盛の数
				smallTickNum: 5,			   //小目盛の数
				halfAxisFlg: true,			  //軸の高さを半分にするためのフラグ
				hide: false
			},
			charts: {
				ohlc: {//四本値（キャンドル）
					color_cd:[234,85,20,1],	//線の色,透明度
					upFillColor:[255,60,120,1],//ローソクの色（上昇）
					downFillColor:[80,80,255,1],//ローソクの色（下降）
					fillOpacity: 1,
					width:180000,				  //線の幅
					shadowSize:false,		 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],	//影の色
					shadowBlur:0,			 //ぼかしの大きさ
					shadowOffsetX:0,		 //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0		  //テキストを基準にしたシャドウの垂直方向の距離
				},
				px: {//価格
					color_cd:[255,255,255,1],	//線の色,透明度
					width:1.5,				  //線の幅
					shadowSize:false,		 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],	//影の色
					shadowBlur:0,			 //ぼかしの大きさ
					shadowOffsetX:0,		 //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0,		  //テキストを基準にしたシャドウの垂直方向の距離
					points: {//寄りと引け値の点
						plusColor : 'rgb(200, 0, 0)',
						minusColor : 'rgb(0, 0, 255)',
						openColor : 'rgb(255,255,255)',
						closeColor : 'rgb(255,255,255)',
						radius : 4
					}
				},
				twap: {//TWAP
					color_cd:[100,150,255,1],	//線の色,透明度
					width:1.5,				  //線の幅
					shadowSize:false,		 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],	//影の色
					shadowBlur:0,			 //ぼかしの大きさ
					shadowOffsetX:0,		  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0		   //テキストを基準にしたシャドウの垂直方向の距離
				},
				volume: {//当日の累積出来高
					color_cd:[193,255,0,1],	//線の色,透明度
					fill: true,
					fillColor: [193,255,0,0.3],
					width:1.5,				  //線の幅
					shadowSize:false,		 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],	//影の色
					shadowBlur:0,			 //ぼかしの大きさ
					shadowOffsetX:0,		  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0		   //テキストを基準にしたシャドウの垂直方向の距離
				},
				averageVolume: {//過去5日間の平均累積出来高
					color_cd:[235,116,19,1],	//線の色,透明度
					fill: true,
					fillColor: [235,116,19,0.2],
					width:1.5,				  //線の幅
					shadowSize:false,		 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],	//影の色
					shadowBlur:0,			 //ぼかしの大きさ
					shadowOffsetX:0,		  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0		   //テキストを基準にしたシャドウの垂直方向の距離
				},
				balanceChange: { //バランス変化共通
					color_cd:[255, 255, 255, 1], //クリックしたポイントの色,透明度
					shadowSize:false,		 //flotr2のデフォルトの影非表示
					shadowColor:[255, 255, 255, 1],	//クリックしたポイントの影の色
					shadowBlur:2,			 //クリックしたポイントのぼかしの大きさ
					shadowOffsetX:0,		  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0,		  //テキストを基準にしたシャドウの垂直方向の距離
					size:4,				   //ポイントする三角形のサイズ。
					onClick:function(time, val, type){//ポイントをクリックしたら実行する関数
						//time: クリックしたポイントの時間
						//val: 価格
						//type:("plus" | "minus") クリックしたポイントの種類
					}
				},
				balanceChangePlus: {//正の板バランス変化
					color_cd:[228, 0, 127, 1],		 //ポイントの色,透明度
					lineColor:[255,180,180, 1],
					shadowSize:false,			 //flotr2のデフォルトの影非表示
					shadowColor:[255,255,0,0],		//影の色
					shadowBlur:2,				 //ぼかしの大きさ
					shadowOffsetX:0,			  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0,			  //テキストを基準にしたシャドウの垂直方向の距離
					size:5						//三角形のサイズ。
				},
				balanceChangeMinus: {//負の板バランス変化
					color_cd:[0, 160, 234, 1],			//ポイントの色,透明度
					lineColor:[180,180,255, 1],
					shadowSize:false,			 //flotr2のデフォルトの影非表示
					shadowColor:[0,255,0,0],		//影の色
					shadowBlur:2,				 //ぼかしの大きさ
					shadowOffsetX:0,			  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0,			  //テキストを基準にしたシャドウの垂直方向の距離
					size:5						//ポイントする三角形のサイズ。特に単位はないです。
				},
				balanceChangePlusAvg: {//正の板バランス変化の平均
					color_cd:[228, 0, 127,1],		//線の色,透明度
					width:1,					  //線の幅
					shadowSize:false,			 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],		//影の色
					shadowBlur:0,				 //ぼかしの大きさ
					shadowOffsetX:0,			  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0			   //テキストを基準にしたシャドウの垂直方向の距離
				},
				balanceChangeMinusAvg: {//負の板バランス変化の平均
					color_cd:[0, 160, 234,1],		//線の色,透明度
					width:1,					  //線の幅
					shadowSize:false,			 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],		//影の色
					shadowBlur:0,				 //ぼかしの大きさ
					shadowOffsetX:0,			  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0			   //テキストを基準にしたシャドウの垂直方向の距離
				},
				basePrice: {//前日の終値
					color_cd:[255,255,0,1],	   //線の色,透明度
					width:1,					  //線の幅
					shadowSize:false,			 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],		//影の色
					shadowBlur: 0,				//ぼかしの大きさ
					shadowOffsetX:0,			  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0			   //テキストを基準にしたシャドウの垂直方向の距離
				}
			},
			reDrawFlg: true					   //true の場合、グラフクリック時にグラフを再描画
		};

		// オプションをセット
		this.setOptions(anyOptions);

	};

	/**
	 * prototype public method
	 */
	MainChart.prototype = {


		on: function(gTrget){
			return this.toggle(gTrget, true);
		},
		off: function(gTrget){
			return this.toggle(gTrget, false);
		},

		toggle: function(gTrget, bool){
			if(!bool) {
				bool = false;
			}
			if (gTrget === this.n_ohlc) {
				this.f_ohlc = bool;
			} else if (gTrget === this.n_px) {
				this.f_px = bool;
			} else if (gTrget === this.n_twap) {
				this.f_twap = bool;
			} else if (gTrget === this.n_volume) {
				this.f_volume = bool;
			} else if (gTrget === this.n_averageVolume) {
				this.f_avgVolume = bool;
			} else if (gTrget === this.n_balanceChangePlus) {
				this.f_balPlus = bool;
			} else if (gTrget === this.n_balanceChangeMinus) {
				this.f_balMinus = bool;
			} else if (gTrget === this.n_balanceChangePlusAvg) {
				this.f_balPlusAvg = bool;
			} else if (gTrget === this.n_balanceChangeMinusAvg) {
				this.f_balMinusAvg = bool;
			} else if (gTrget === this.n_basePrice) {
				this.f_basePrice = bool;
			}
			return this;
		},
		onAll: function(){
			return this.toggleAll(true);
		},
		offAll: function(){
			return this.toggleAll(false);
		},
		toggleAll: function(bool){
			if(!bool) {
				bool = false;
			}
			this.f_ohlc = bool;
			this.f_px = bool;
			this.f_twap = bool;
			this.f_volume = bool;
			this.f_avgVolume = bool;
			this.f_balPlus = bool;
			this.f_balMinus = bool;
			this.f_balPlusAvg = bool;
			this.f_balMinusAvg = bool;
			this.f_basePrice = bool;
			return this;
		},
		/**
		 * グラフOFF
		 * @param string gTrget 描画をオフするグラフ
		 */
		hide: function(gTrget){
			this.off(gTrget);
			var graph = this._drawGraph();
			return this;
		},
		/**
		 * グラフON
		 * @param string gTrget 描画をオフするグラフ
		 */
		show: function(gTrget){
			this.on(gTrget);
			var graph = this._drawGraph();
			return this;
		},

		/**
		 * 全グラフOFF
		 */
		hideAll: function(){
			this.offAll();
			var graph = this._drawGraph();
			return this;
		},
		/**
		 * 全グラフON
		 */
		showAll: function(){
			this.onAll();
			var graph = this._drawGraph();
			return this;
		},

		/**
		 * オプションをセット
		 * @param array anyOptions セットするオプション
		 */
		setOptions: function(anyOptions){
			// オプションのマージ
			$.extend(true, this.m_options, anyOptions);
			return this;
		},

		/**
		 * 指定データを元にチャートを描画
		 * @param array data データ
		 * （注）描画しない場合はキーを含めてデータとして存在しない
		 */
		draw: function(data){

			//グラフ描画用データを格納
			this.data = data;

			// 指定要素へのスタイル付与
			var backgroundColor = utils.convertToRgba(this.m_options.backgroundColor);
			$(this.m_options.target).css({
				width: this.m_options.width + 'px',
				height: this.m_options.height + 'px',
				backgroundColor: backgroundColor
			});

			var timeInfo = this.m_gTimeInfo;	//連想配列取得

			//配列の初期化
			this.d_ohlc = [];
			this.d_px = [];
			this.d_twap = [];
			this.d_volume = [];
			this.d_avgVolume = [];
			this.d_balChange = [];
			this.d_balPlusAvg = [];
			this.d_balMinusAvg = [];
			this.d_basePrice = [];

			var i, nTime, time, nLength;
			var now = new Date();

			//ローソク足用データ配列作成
			if (this.n_ohlc in data) {
				nLength = data.ohlc.length;
				for (i = 0; i < nLength; i++) {   //四本値
					nTime = data.ohlc[i].time;
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					this.d_ohlc.push([time, data.ohlc[i].val.open, data.ohlc[i].val.high, data.ohlc[i].val.low, data.ohlc[i].val.close]);
				}
			}

			//折れ線用データ配列作成
			if (this.n_px in data) {
				nLength = data.px.length;
				for (i = 0; i < nLength; i++) {	//価格
					nTime = data.px[i].time;
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					this.d_px.push([time, data.px[i].val]);
				}
			}
			if (this.n_twap in data) {
				nLength = data.twap.length;
				for (i = 0; i < nLength; i++) {	//TWAP
					nTime = data.twap[i].time;
					/*
					 if(nTime === 90000){
					 continue;
					 }
					 */
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					this.d_twap.push([time, data.twap[i].val]);
				}
			}

			if (this.n_volume in data) {
				nLength = data.volume.length;
				for (i = 0; i < nLength; i++) {	//当日の累積出来高
					nTime = data.volume[i].time;
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					this.d_volume.push([time, data.volume[i].val]);
				}
			}
			if (this.n_averageVolume in data) {
				nLength = data.averageVolume.length;
				for (i = 0; i < nLength; i++) {	//過去5日間の平均累積出来高
					nTime = data.averageVolume[i].time;
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					this.d_avgVolume.push([time, data.averageVolume[i].val]);
				}
			}

			//板バランスポイント用データ配列作成（正・負を１つの配列で管理）
			var balChange = [];
			if (this.f_balPlus && this.n_balanceChangePlus in data) {
				nLength = data.balanceChangePlus.length;
				for (i = 0; i < nLength; i++) {	//正の板バランス変化
					nTime = data.balanceChangePlus[i].time;
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					balChange.push([time, data.balanceChangePlus[i].val, nTime, 'plusTriangle']);
				}
			}
			if (this.f_balMinus && this.n_balanceChangeMinus in data) {
				nLength = data.balanceChangeMinus.length;
				for (i = 0; i < nLength; i++) {	//負の板バランス変化
					nTime = data.balanceChangeMinus[i].time;
					time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);
					balChange.push([time, data.balanceChangeMinus[i].val, nTime, 'minusTriangle']);
				}
			}
			if (balChange.length > 0) {
				//配列のソート
				/* global _ */
				this.d_balChange = _.sortBy(balChange, function(data){
					return -(data[2]);
				});
			}


			//水平線用データ配列作成 （2点のみ作成する）
			var stime = now.setHours(9,0,0,0);
			var etime = now.setHours(15,0,0,0);
			if (this.n_balanceChangePlusAvg in data) {
				this.d_balPlusAvg.push([stime, data.balanceChangePlusAvg]);	//正の板バランス変化の平均
				this.d_balPlusAvg.push([etime, data.balanceChangePlusAvg]);
			}
			if (this.n_balanceChangeMinusAvg in data) {
				this.d_balMinusAvg.push([stime, data.balanceChangeMinusAvg]);	//負の板バランス変化の平均
				this.d_balMinusAvg.push([etime, data.balanceChangeMinusAvg]);
			}
			if (this.n_basePrice in data) {
				this.d_basePrice.push([stime, data.basePrice]);	//前日の終値
				this.d_basePrice.push([etime, data.basePrice]);
			}

			//X軸固定用ダミーデータ
			var dummyTime = stime;
			for(dummyTime;dummyTime<=etime;dummyTime+=300000){
				this.d_base.push([dummyTime, -10]);
				this.d_base2.push([dummyTime, -10000000]);
			}

			// Y2軸用
			// 当日の累積出来高, 過去5日間の平均累積出来高の最大値の取得
			/* global _ */
			var maxDataVol = {time: 0, val: 0},
				maxDataVolAvg = {time: 0, val: 0};
			if (this.n_volume in data && _.isArray(data.volume) && data.volume.length > 0) {
				maxDataVol = _.max(data.volume, function (volume) {
					return volume.val;
				});
			}
			if (this.n_averageVolume in data && _.isArray(data.averageVolume) && data.averageVolume.length > 0) {
				maxDataVolAvg = _.max(data.averageVolume, function (averageVolume) {
					return averageVolume.val;
				});
			}

			this.m_maxDataY2 = (maxDataVol.val > maxDataVolAvg.val) ? maxDataVol.val : maxDataVolAvg.val;


			this.hourTicks = [];
			for(var t=9;t<=15;t++){
				this.hourTicks.push(now.setHours(t,0,0,0));
				this.hourTicks.push(now.setHours(t,30,0,0));
			}

			// Graph描画
			var graph = this._drawGraph();

			return this;
		},

		/**
		 * グラフ描画
		 * @return Object returns a new graph object and of course draws the graph.
		 */
		_drawGraph: function() {

			//Y軸目盛ラベル作成
			var yticks = [],
				y2ticks = [],
				i, j, num, axis;
			for (i = 0 ; i < this.m_options.yaxis.tickNum; i++) {	//Y軸
				num = this.m_options.yaxis.min + this.m_options.yaxis.tickSize * i;
				if (this.m_options.yaxis.showLabels) {
					axis = (Math.round(num * 10) / 10).toLocaleString();	//3桁区切り
				} else {
					axis = '';
				}
				yticks.push([num, axis]);
			}

			if(!this.m_options.yaxis2.hide){
				for (i = 0 ; i < this.m_options.yaxis2.tickNum; i++) {  //Y2軸
					j = this.m_options.yaxis2.min + this.m_options.yaxis2.tickSize * i;
					num = this._getY2ticks(j, this.m_maxDataY2);
					if (this.m_options.yaxis2.showLabels) {
						axis = num.toLocaleString();	//3桁区切り
					} else {
						axis = '';
					}
					y2ticks.push([j, axis]);
				}
			}


			//グラフのデータセット作成
			var gDataSet = [];

			// ローソク足:X2軸
			if (this.f_ohlc) {
				var candleColor = utils.convertToRgba(this.m_options.charts.ohlc.color_cd);
				gDataSet.push({
					data:this.d_ohlc,
					candles : {
						show : true,
						candleWidth : this.m_options.charts.ohlc.width,
						barcharts: false,
						upFillColor: utils.convertToRgba(this.m_options.charts.ohlc.upFillColor),
						downFillColor: utils.convertToRgba(this.m_options.charts.ohlc.downFillColor),
						fillOpacity: this.m_options.charts.ohlc.fillOpacity,
						shadowSize: this.m_options.charts.ohlc.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.ohlc.shadowColor),
						shadowBlur: this.m_options.charts.ohlc.shadowBlur,
						shadowOffsetX: this.m_options.charts.ohlc.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.ohlc.shadowOffsetY
					},
					mouse:false
				});
			}
			if (this.f_twap) {	//TWAP
				gDataSet.push({
					data:this.d_twap,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.twap.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.twap.color_cd),
						lineWidth: this.m_options.charts.twap.width,
						shadowSize: this.m_options.charts.twap.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.twap.shadowColor),
						shadowBlur: this.m_options.charts.twap.shadowBlur,
						shadowOffsetX: this.m_options.charts.twap.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.twap.shadowOffsetY
					}
				});
			}

			//折れ線:Y2軸
			if (this.f_avgVolume) {	 //２５日の累積出来高平均
				gDataSet.push({
					data:this.d_avgVolume,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.averageVolume.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.averageVolume.fillColor),
						fillColor:utils.convertToRgba(this.m_options.charts.averageVolume.fillColor),
						fill:this.m_options.charts.averageVolume.fill,
						lineWidth: this.m_options.charts.averageVolume.width,
						shadowSize: this.m_options.charts.averageVolume.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.averageVolume.shadowColor),
						shadowBlur: this.m_options.charts.averageVolume.shadowBlur,
						shadowOffsetX: this.m_options.charts.averageVolume.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.averageVolume.shadowOffsetY
					},
					yaxis: 2
				});
			}
			if (this.f_volume) {	 //当日の累積出来高
				gDataSet.push({
					data:this.d_volume,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.volume.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.volume.fillColor),
						fillColor:utils.convertToRgba(this.m_options.charts.volume.fillColor),
						fill:this.m_options.charts.volume.fill,
						lineWidth: this.m_options.charts.volume.width,
						shadowSize: this.m_options.charts.volume.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.volume.shadowColor),
						shadowBlur: this.m_options.charts.volume.shadowBlur,
						shadowOffsetX: this.m_options.charts.volume.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.volume.shadowOffsetY
					},
					yaxis: 2
				});
			}


			//板バランス変化
			var arrDrawData = [];
			if (this.f_balPlus && this.f_balMinus) {
				arrDrawData = this.d_balChange;
			} else {
				if (this.f_balPlus) {
					arrDrawData = _.select(this.d_balChange, function(data){
						return data[3] === 'plusTriangle';
					});
				}
				if (this.f_balMinus) {
					arrDrawData = _.select(this.d_balChange, function(data){
						return data[3] === 'minusTriangle';
					});
				}
			}
			if (arrDrawData.length > 0) {
				gDataSet.push({
					data:arrDrawData,
					points: {
						show: true,
						ptColor: utils.convertToRgba(this.m_options.charts.balanceChangePlus.color_cd),
						ptLineColor: utils.convertToRgba(this.m_options.charts.balanceChangePlus.lineColor),
						ptFillColor: utils.convertToRgba(this.m_options.charts.balanceChangePlus.color_cd),
						ptFillOpacity: utils.getFillOpacity(this.m_options.charts.balanceChangePlus.color_cd),
						ptRadius: this.m_options.charts.balanceChangePlus.size,
						ptShadowColor: utils.convertToRgba(this.m_options.charts.balanceChangePlus.shadowColor),
						ptShadowBlur: this.m_options.charts.balanceChangePlus.shadowBlur,
						ptShadowOffsetX: this.m_options.charts.balanceChangePlus.shadowOffsetX,
						ptShadowOffsetY: this.m_options.charts.balanceChangePlus.shadowOffsetY,

						mtColor: utils.convertToRgba(this.m_options.charts.balanceChangeMinus.color_cd),
						mtLineColor: utils.convertToRgba(this.m_options.charts.balanceChangeMinus.lineColor),
						mtFillColor: utils.convertToRgba(this.m_options.charts.balanceChangeMinus.color_cd),
						mtFillOpacity: utils.getFillOpacity(this.m_options.charts.balanceChangeMinus.color_cd),
						mtRadius: this.m_options.charts.balanceChangeMinus.size,
						mtShadowColor: utils.convertToRgba(this.m_options.charts.balanceChangeMinus.shadowColor),
						mtShadowBlur: this.m_options.charts.balanceChangeMinus.shadowBlur,
						mtShadowOffsetX: this.m_options.charts.balanceChangeMinus.shadowOffsetX,
						mtShadowOffsetY: this.m_options.charts.balanceChangeMinus.shadowOffsetY
					}
				});
			}

			if (this.f_balPlusAvg) {	//正の板バランス変化の平均
				gDataSet.push({
					data:this.d_balPlusAvg,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.balanceChangePlusAvg.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.balanceChangePlusAvg.color_cd),
						lineWidth: this.m_options.charts.balanceChangePlusAvg.width,
						shadowSize: this.m_options.charts.balanceChangePlusAvg.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.balanceChangePlusAvg.shadowColor),
						shadowBlur: this.m_options.charts.balanceChangePlusAvg.shadowBlur,
						shadowOffsetX: this.m_options.charts.balanceChangePlusAvg.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.balanceChangePlusAvg.shadowOffsetY
					}
				});
			}
			if (this.f_balMinusAvg) {	//負の板バランス変化の平均
				gDataSet.push({
					data:this.d_balMinusAvg,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.balanceChangeMinusAvg.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.balanceChangeMinusAvg.color_cd),
						lineWidth: this.m_options.charts.balanceChangeMinusAvg.width,
						shadowSize: this.m_options.charts.balanceChangeMinusAvg.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.balanceChangeMinusAvg.shadowColor),
						shadowBlur: this.m_options.charts.balanceChangeMinusAvg.shadowBlur,
						shadowOffsetX: this.m_options.charts.balanceChangeMinusAvg.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.balanceChangeMinusAvg.shadowOffsetY
					}
				});
			}
			if (this.f_basePrice) {	//前日の終値
				gDataSet.push({
					data:this.d_basePrice,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.basePrice.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.basePrice.color_cd),
						lineWidth: this.m_options.charts.basePrice.width,
						shadowSize: this.m_options.charts.basePrice.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.basePrice.shadowColor),
						shadowBlur: this.m_options.charts.basePrice.shadowBlur,
						shadowOffsetX: this.m_options.charts.basePrice.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.basePrice.shadowOffsetY
					}
				});
			}


			//折れ線:Y軸
			if (this.f_px) {	//価格
				gDataSet.push({
					data:this.d_px,
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.charts.px.color_cd),
						fillOpacity: utils.getFillOpacity(this.m_options.charts.px.color_cd),
						lineWidth: this.m_options.charts.px.width,
						shadowSize: this.m_options.charts.px.shadowSize,
						shadowColor: utils.convertToRgba(this.m_options.charts.px.shadowColor),
						shadowBlur: this.m_options.charts.px.shadowBlur,
						shadowOffsetX: this.m_options.charts.px.shadowOffsetX,
						shadowOffsetY: this.m_options.charts.px.shadowOffsetY
					}
				});

				if(this.d_px.length > 0){//寄りと引け値をポイントする
					var _that = this;
					var plusColor = this.m_options.charts.px.points.plusColor;
					var minusColor = this.m_options.charts.px.points.minusColor;
					var openColor = this.m_options.charts.px.points.openColor;
					var closeColor = this.m_options.charts.px.points.closeColor;
					var radius = this.m_options.charts.px.points.radius;

					if(this.d_basePrice.length > 0) {
						if(this.d_basePrice[0][1] > this.d_px[0][1]) {
							openColor = minusColor;
						} else if(this.d_basePrice[0][1] < this.d_px[0][1]) {
							openColor = plusColor;
						}
					}


					//寄り値をポイント
					gDataSet.push({
						data:[this.d_px[0]],
						points : {
							show: true,
							lineWidth: _that.m_options.charts.px.width,
							color_cd: utils.convertToRgba(_that.m_options.charts.px.color_cd),
							fillColor: openColor,
							radius:radius
						}
					});

					//引け値をポイント
					var closeTime = (new Date()).setHours(15,0,0,0);
					_.each(this.d_px,function(v){
						if(v[0] === closeTime){
							if(_that.d_basePrice[0][1] > v[1]) {
								closeColor = minusColor;
							} else if(_that.d_basePrice[0][1] < v[1]) {
								closeColor = plusColor;
							}
							gDataSet.push({
								data:[v],
								points : {
									show: true,
									lineWidth: _that.m_options.charts.px.width,
									color_cd: utils.convertToRgba(_that.m_options.charts.px.color_cd),
									fillColor: closeColor,
									radius:radius
								}
							});
						}
					});

				}

			}

			//x軸,y軸固定用ダミーグラフデータ
			gDataSet.push({
				data:this.d_base,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.backgroundColor)
				}
			});
			gDataSet.push({
				data:this.d_base2,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.backgroundColor)
				},
				yaxis:2
			});

			//Y2軸 最小値・最大値の取得
			var y2min = this.m_options.yaxis2.min;
			var y2max = y2min + (this.m_options.yaxis2.tickSize * (this.m_options.yaxis2.tickNum - 1));
			var y2max_dummy = y2max + (y2max - y2min);

			//グラフのオプションセット作成
			var today = new Date();
			var that = this;

			var tickFormatter;
			if (this.m_options.yaxis2.showLabels){
				tickFormatter = function(n) {
					var tickDate = new Date(n);
					var hh = ("0"+ tickDate.getHours()).slice(-2);  // 1桁の数字を0埋めで2桁にする
					var mm = ("0"+ tickDate.getMinutes()).slice(-2);  // 1桁の数字を0埋めで2桁にする
					return hh + ':' + mm;
				};
			}else{
				tickFormatter = function(n){return '';};
			}

			var gOptionsSet = {
				ignoreMouseDown: this.m_options.ignoreMouseDown,
				wrapper: this,
				fontColor: utils.convertToRgba(this.m_options.textColor),
				HtmlText: false,
				title: null,
				y2TitleMargin: this.m_options.y2TitleMargin,
				xaxis:{
					showLabels: this.m_options.xaxis.showLabels,
					showLine: this.m_options.xaxis.showLine,
					color_cd: utils.convertToRgba(this.m_options.xaxis.color_cd),
					mode: 'time',
					timeMode:'JST',
					noTicks: 24,
					tickFormatter: tickFormatter,
					smallTickNum: this.m_options.xaxis.smallTickNum,
					tickLength: this.m_options.xaxis.tickLength,
					tickLengthShort: this.m_options.xaxis.tickLengthShort,
					grid: {
						show: this.m_options.xaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.xaxis.grid.color_cd),
						width: this.m_options.xaxis.grid.width
					},
					ticks:this.hourTicks,
					min: (new Date()).setHours(8,50,0,0),
					max: (new Date()).setHours(15,10,0,0)
				},
				yaxis: {
					ticks: yticks,
					showLabels: true,
					color_cd: utils.convertToRgba(this.m_options.yaxis.color_cd),
					min: this.m_options.yaxis.min  - Math.floor(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //-Math.floor()部分：Y軸のマージン確保のため微調整
					max: this.m_options.yaxis.min + (this.m_options.yaxis.tickSize * (this.m_options.yaxis.tickNum - 1)) +
						//Math.ceil(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //+Math.ceil()部分Y軸のマージン確保のため微調整
					(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //+Math.ceil()部分Y軸のマージン確保のため微調整
					smallTickNum: this.m_options.yaxis.smallTickNum,
					tickLength: this.m_options.yaxis.tickLength,
					tickLengthShort: this.m_options.yaxis.tickLengthShort,
					grid: {
						show: this.m_options.yaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.yaxis.grid.color_cd),
						width: this.m_options.yaxis.grid.width
					}
				},

				y2axis: {
					ticks: y2ticks,
					showLabels: true,
					color_cd: utils.convertToRgba(this.m_options.yaxis2.color_cd),
					min: y2min  - Math.floor(this.m_options.yaxis2.tickSize / this.m_options.yaxis2.smallTickNum),  //-Math.floor()部分：Y軸のマージン確保のため微調整
					max: y2max_dummy,
					title: this._getY2ticksLabel(this.m_maxDataY2),
					titleAlign: 'left',
					titleAngle: 0,
					smallTickNum: this.m_options.yaxis2.smallTickNum,
					halfAxisFlg: this.m_options.yaxis2.halfAxisFlg
				},
				grid:{
					verticalLines: this.m_options.xaxis.grid.show,
					horizontalLines: this.m_options.yaxis.grid.show,
					color_cd: utils.convertToRgba(this.m_options.textColor),
					outline : this._getOutline(this.m_options.xaxis.showLine, this.m_options.yaxis.showLine, this.m_options.yaxis2.showLine),
					xAxisLabelMargin: this.m_options.xaxis.grid.labelMargin,
					yAxisLabelMargin: this.m_options.yaxis.grid.labelMargin
				},
				balanceChange: { //バランス変化共通
					color_cd: utils.convertToRgba(this.m_options.charts.balanceChange.color_cd),			 //クリックしたポイントの色,透明度
					shadowColor: utils.convertToRgba(this.m_options.charts.balanceChange.shadowColor), //クリックしたポイントの影の色
					shadowBlur: this.m_options.charts.balanceChange.shadowBlur,   //クリックしたポイントのぼかしの大きさ
					radius: this.m_options.charts.balanceChange.size,		   //ポイントする三角形のサイズ。
					onClick: this.m_options.charts.balanceChange.onClick		  //ポイントをクリックしたら実行する関数
				},
				reDrawFlg: this.m_options.reDrawFlg,
				mouse : {
					track: true,
					trackAll: false,
					trackY: false,
					trackDecimals: 0,
					lineColor: null,
					relative: true,
					position: 'se',
					marginY: 30,
					startTime: (new Date()).setHours(9,0,0,0),
					sensibility: 2,
					options:this.m_options,
					trackFormatter: function (o) {
						var html = '';
						//9時から五分おきの時間に変換
						var tTime = Math.floor((Number(o.x) - this.startTime) / 60000 / this.options.onMouseOverRange) * 60000 * this.options.onMouseOverRange + this.startTime;
						//時刻の表示文字列生成
						var d1 = new Date(Number(tTime));
						var sTime = ("0"+ d1.getHours()).slice(-2) + ':' + ("0"+ d1.getMinutes()).slice(-2);
						html  += '<tr><td>時刻</td><td>' + sTime + '</td></tr>';
						var i;
						var val;
						if (that.f_ohlc) {
							val = _.find(that.d_ohlc, function(value, key){
								return value[0] === tTime;
							});
							if(!val) {
								val = [0,0,0,0,0];
							}
							for( i=1;i<=4;i++){
								if ( val[i] ) {
									val[i] = val[i].toLocaleString();
								} else {
									val[i] = '-';
								}
							}
							html  += '<tr><td>始値</td><td>' + val[1]+ '</td></tr>';
							html  += '<tr><td>高値</td><td>' + val[2]+ '</td></tr>';
							html  += '<tr><td>安値</td><td>' + val[3]+ '</td></tr>';
							html  += '<tr><td>終値</td><td>' + val[4]+ '</td></tr>';
						}
						var list = [
							['px','価格','px'],
							['twap','TWAP','twap'],
							['volume','出来高','volume'],
							['avgVolume','25日平均出来高','averageVolume']
						];
						var fn = function(value, key){
							return value[0] === tTime;
						};
						for(i=0;i<list.length;i++){
							if(that['f_' + list[i][0]]) {
								val = _.find(that['d_' + list[i][0]], fn);
								if(val){
									if (list[i][0] === 'twap') {
										val = (Math.round(val[1] * 100) / 100).toLocaleString();
									}else{
										val = val[1].toLocaleString();
									}
								} else {
									val = '-';
								}
								html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts[list[i][2]].color_cd) + ';"><td>' + list[i][1] + '</td><td>' + val + '</td></tr>';
							}
						}

						if (that.f_basePrice) {
							html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.basePrice.color_cd) + ';"><td>前日終値</td><td>' + that.data.basePrice.toLocaleString() + '</td></tr>';
						}
						if (that.f_balPlusAvg) {
							html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.balanceChangePlusAvg.color_cd) + ';"><td>正変化平均</td><td>' + (Math.round(that.data.balanceChangePlusAvg * 10) / 10).toLocaleString() + '</td></tr>';
						}
						if (that.f_balMinusAvg) {
							html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.balanceChangeMinusAvg.color_cd) + ';"><td>負変化平均</td><td>' + (Math.round(that.data.balanceChangeMinusAvg * 10) / 10).toLocaleString() + '</td></tr>';
						}
						html = '<table>' + html + '</table>';
						return html;


					}
				},
				crosshair : {
					mode : 'x',
					color_cd: 'rgb(255, 255, 255)',
					hideCursor:false
				}
			};
			// Graph描画
			/* global Flotr */
			return Flotr.draw(this.m_options.target.get(0), gDataSet, gOptionsSet, this.data, this);
		},
		mousemove: function(){
			//this.m_options.target.trigger('mousemove');
			//$(this.m_options.target.get(0)).trigger('mousemove');
		},

		/**
		 * 渡された値からY2軸の目盛に表示する値を返します
		 * @param number 値
		 * @param number Max値
		 * @return number 表示する値
		 */
		_getY2ticks: function(val, maxVal) {
			//max値が5桁以上の場合：千単位、8桁以上の場合：百万単位にする
			var num = val;
			if (maxVal >= 10000000) {
				num = val / 1000000;
			} else if(maxVal >= 10000){
				num = val / 1000;
			}
			return num;
		},

		/**
		 * 渡されたMAX値からY2軸に表示するラベル（単位）を返します
		 * @param number Max値
		 * @return string ラベル（単位）
		 */
		_getY2ticksLabel: function(maxVal) {
			if (!this.m_options.yaxis2.showLabels || this.m_options.yaxis2.hide) {
				return '';
			}
			//max値が5桁以上の場合：千単位、8桁以上の場合：百万単位にする
			var str = "(株)";
			if (maxVal >= 10000000) {
				str = "(百万株)";
			} else if(maxVal >= 10000){
				str = "(千株)";
			}
			return str;
		},

		/**
		 * X軸、Y軸の線を描画するかどうか
		 * @param boolean x x軸の線を描画するかどうか
		 * @param boolean y y軸の線を描画するかどうか
		 * @param boolean y2 y2軸の線を描画するかどうか
		 * @return string 描画位置
		 */
		_getOutline: function(x, y, y2) {
			var outline = "";
			if (x){
				outline += "s";
			}
			if (y){
				outline += "w";
			}
			if (y2){
				outline += "e";
			}
			return outline;
		}

	};

	return MainChart;

});
