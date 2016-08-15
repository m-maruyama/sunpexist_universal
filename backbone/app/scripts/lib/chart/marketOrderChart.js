define([
	'jquery',
	'flotr2',
	'chartUtils'
], function ($, flotr2, utils) {

	"use strict";

	var MarketOrderChart = function(anyOptions){
		this.unitScaling = 1000000000;
		this.unitScalingText = '十億円';
		this.m_options = {
			width: 480, //canvas幅
			height: 320, //canvas高さ
			textColor: [255, 255, 255, 1],
			backgroundColor: [0, 0, 0, 1],   //背景色
			targetMarket: 0,//"対象範囲: 0=全部、1=東証一部のみ、2=東証一部以外"
			xaxis: {
				color_cd: [255, 255, 255, 1],   //線、目盛の色
				showLabels: true,			//横（項目）軸ラベル
				smallTickNum: 3,			 //小目盛の数
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1,				   //線の幅
					labelMargin:3			   //ラベルマージン 軸位置などの調整用
				}
			},
			yaxis: {  //必須：最小値、目盛幅、目盛の数
				showLabels: true,			//縦（値）軸ラベル
				color_cd: [255, 255, 255, 1],   //線、目盛の色
				min: 0,					  //最小値
				tickSize: 0,				 //目盛幅
				tickNum: 0,				  //ｙ軸の目盛の数
				smallTickNum: 5,			 //小目盛の数
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1,				   //線の幅
					labelMargin:20			  //ラベルマージン 軸位置などの調整用
				}
			},
			yaxis2: {  //必須：最小値、目盛幅、目盛の数
				showLabels: true,			//縦（値）軸ラベル
				color_cd: [255, 255, 255, 1],   //線、目盛の色
				min: 0,					  //最小値
				tickSize: 0,				 //目盛幅
				tickNum: 0,				  //ｙ2軸の目盛の数
				smallTickNum: 5			 //小目盛の数
			},
			charts: {
				px: { //株価
					color_cd: [255, 255, 255, 1],  //線の色,透明度
					width: 1,				   //線の幅
					shadowSize:false,		   //flotr2のデフォルトの影非表示
					shadowColor: [0, 0, 0, 1],  //影の色
					shadowBlur: 2,			  //ぼかしの大きさ
					shadowOffsetX:2,			//テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:2			 //テキストを基準にしたシャドウの垂直方向の距離
				},
				offer_amt: { //売注文金額
					color_cd: [255, 255, 255, 1],  //線の色,透明度
					width: 1,				   //線の幅
					shadowSize:false,		   //flotr2のデフォルトの影非表示
					shadowColor: [0, 0, 0, 1],  //影の色
					shadowBlur: 2,			  //ぼかしの大きさ
					shadowOffsetX:2,			//テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:2			 //テキストを基準にしたシャドウの垂直方向の距離
				},
				bid_amt: { //買注文金額
					color_cd: [255, 255, 255, 1],  //線の色,透明度
					width: 1,				   //線の幅
					shadowSize:false,		   //flotr2のデフォルトの影非表示
					shadowColor: [0, 0, 0, 1],  //影の色
					shadowBlur: 2,			  //ぼかしの大きさ
					shadowOffsetX:2,			//テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:2			 //テキストを基準にしたシャドウの垂直方向の距離
				},
				prevClose: {//前日の終値
					color_cd:[255, 255, 87, 1],	   //線の色,透明度
					width:1,					  //線の幅
					shadowSize:false,			 //flotr2のデフォルトの影非表示
					shadowColor:[0,0,0,1],		//影の色
					shadowBlur: 0,				//ぼかしの大きさ
					shadowOffsetX:0,			  //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:0			   //テキストを基準にしたシャドウの垂直方向の距離
				}
			},
			reDrawFlg: false					//true の場合、グラフクリック時にグラフを再描画
		};

		// オプションをセット
		this.setOptions(anyOptions);
		
	};


	MarketOrderChart.prototype = {
		/**
		 * オプションをセットします
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
		 */
		draw: function(_data){

			var data = _data.graph;


			// 指定要素へのスタイル付与
			var backgroundColor = utils.convertToRgba(this.m_options.backgroundColor);
			var textColor = utils.convertToRgba(this.m_options.textColor);
			$(this.m_options.target).css({
				width: this.m_options.width + 'px',
				height: this.m_options.height + 'px',
				backgroundColor: backgroundColor,
				color_cd: textColor
			});

			//Y軸目盛ラベル作成
			var yticks = [],
				y2ticks = [],
				i, j, num, axis;
			for (i = 0 ; i < this.m_options.yaxis.tickNum; i++) {	//Y軸
				num = this.m_options.yaxis.min + this.m_options.yaxis.tickSize * i;
				axis = this._addComma(this._getYticks(num));
				yticks.push([num, axis]);
			}
			for (i = 0 ; i < this.m_options.yaxis2.tickNum; i++) {  //Y2軸
				num = this.m_options.yaxis2.min + this.m_options.yaxis2.tickSize * i;
				axis = this._addComma(num);
				y2ticks.push([num, axis]);
			}

			var d_px = [],		//株価
				d_offer_amt = [], //売注文金額
				d_bid_amt = [],   //買注文金額
				d_prevClose = [],   //前日終値
				d_base = [],	  //ダミー
				d_base2 = [];	  //ダミー
			var nTime, time;
			var today = new Date();

			//折れ線用データ配列作成
			var now = new Date();
			for (i = 0; i < data.length; i++) {
				nTime = data[i].time;
				//time = new Date(today.getFullYear(), today.getMonth(), today.getDate(), utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime)).getTime();
				time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);//setHoursはミリ秒を返す
				d_px.push([time, data[i].px]);
				d_offer_amt.push([time, data[i].offer_amt]);
				d_bid_amt.push([time, data[i].bid_amt]);
			}

			//水平線用データ配列作成 （2点のみ作成する）
			var stime = now.setHours(9,0,0,0);
			var etime = now.setHours(15,0,0,0);
			d_prevClose.push([stime, _data.prev_px.close]);	//前日の終値
			d_prevClose.push([etime, _data.prev_px.close]);

			//x軸に表示する目盛（時間）を作っている
			var hourTicks = [];
			for(var t=9;t<=15;t++){
				hourTicks.push(now.setHours(t,0,0,0));
				hourTicks.push(now.setHours(t,30,0,0));
			}

			//X軸固定用ダミーデータ
			var dummyTime = stime;
			for(dummyTime;dummyTime<=etime;dummyTime+=300000){
				d_base.push([dummyTime, -10]);
				d_base2.push([dummyTime, -10000000]);
			}


			//グラフのデータセット作成
			var gDataSet = [];
			gDataSet.push({		//買注文金額
				data:d_bid_amt,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.bid_amt.color_cd),
					fillOpacity: 0.2,
					fill:true,
					fillColor:utils.convertToRgba(this.m_options.charts.bid_amt.color_cd),
					setClipPath:true,
					lineWidth: this.m_options.charts.bid_amt.width,
					shadowSize: this.m_options.charts.bid_amt.shadowSize,
					shadowColor: utils.convertToRgba(this.m_options.charts.bid_amt.shadowColor),
					shadowBlur: this.m_options.charts.bid_amt.shadowBlur,
					shadowOffsetX: this.m_options.charts.bid_amt.shadowOffsetX,
					shadowOffsetY: this.m_options.charts.bid_amt.shadowOffsetY
				}
			});
			gDataSet.push({		//売注文金額
				data:d_offer_amt,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.offer_amt.color_cd),
					fillOpacity: 0.2,
					fill:true,
					fillColor:utils.convertToRgba(this.m_options.charts.offer_amt.color_cd),
					clip:true,
					lineWidth: this.m_options.charts.offer_amt.width,
					shadowSize: this.m_options.charts.offer_amt.shadowSize,
					shadowColor: utils.convertToRgba(this.m_options.charts.offer_amt.shadowColor),
					shadowBlur: this.m_options.charts.offer_amt.shadowBlur,
					shadowOffsetX: this.m_options.charts.offer_amt.shadowOffsetX,
					shadowOffsetY: this.m_options.charts.offer_amt.shadowOffsetY
				}
			});
			gDataSet.push({		//株価
				data:d_px,
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
				},
				yaxis: 2
			});
			gDataSet.push({//前日終値
				data: d_prevClose,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.prevClose.color_cd),
					fillOpacity: utils.getFillOpacity(this.m_options.charts.prevClose.color_cd),
					lineWidth: this.m_options.charts.prevClose.width,
					shadowSize: this.m_options.charts.prevClose.shadowSize,
					shadowColor: utils.convertToRgba(this.m_options.charts.prevClose.shadowColor),
					shadowBlur: this.m_options.charts.prevClose.shadowBlur,
					shadowOffsetX: this.m_options.charts.prevClose.shadowOffsetX,
					shadowOffsetY: this.m_options.charts.prevClose.shadowOffsetY
				},
				yaxis: 2
			});

			//x軸,y軸固定用ダミーグラフデータ
			gDataSet.push({
				data:d_base,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.backgroundColor)
				}
			});
			gDataSet.push({
				data:d_base2,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.backgroundColor)
				},
				yaxis:2
			});

			var precise = 1;
			if(this.m_options.targetMarket === 2){
				precise = 2;
			}
			// オプションのセット
			var gOpitons = {
				tytitle:"注文金額（" + this.unitScalingText + "）",
				ty2title:"株価（円）",
				fontColor: utils.convertToRgba(this.m_options.textColor),
				HtmlText: false,
				//selection : {
				//	mode : 'x'
				//},
				xaxis:{
				   showLabels: this.m_options.xaxis.showLabels,
				   color_cd: utils.convertToRgba(this.m_options.xaxis.color_cd),
				   mode: 'time',
					timeMode:'JST',
					noTicks: 24,
					tickFormatter: function(n) {
						var tickDate = new Date(n);
						var hh = ("0"+ tickDate.getHours()).slice(-2);  // 1桁の数字を0埋めで2桁にする
						var mm = ("0"+ tickDate.getMinutes()).slice(-2);  // 1桁の数字を0埋めで2桁にする
						return hh + ':' + mm;
					},
					smallTickNum: this.m_options.xaxis.smallTickNum,
					grid: {
						show: this.m_options.xaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.xaxis.grid.color_cd),
						width: this.m_options.xaxis.grid.width
					},
					ticks:hourTicks,
					min: (new Date()).setHours(8,55,0,0),
					max: (new Date()).setHours(15,5,0,0)
				},
				yaxis: {
					ticks: yticks,
					showLabels: this.m_options.yaxis.showLabels,
					color_cd:  utils.convertToRgba(this.m_options.yaxis.color_cd),
					min: this.m_options.yaxis.min - Math.floor(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //-Math.floor()部分：Y軸のマージン確保のため微調整
					max: this.m_options.yaxis.min + (this.m_options.yaxis.tickSize * (this.m_options.yaxis.tickNum - 1)) + 
							Math.ceil(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //+Math.ceil()部分Y軸のマージン確保のため微調整
					smallTickNum: this.m_options.yaxis.smallTickNum,
					grid: {
						show: this.m_options.yaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.yaxis.grid.color_cd),
						width: this.m_options.yaxis.grid.width
					}
				},
				y2axis: {
					ticks: y2ticks,
					showLabels: this.m_options.yaxis2.showLabels,
					color_cd:  utils.convertToRgba(this.m_options.yaxis2.color_cd),
					min: this.m_options.yaxis2.min - Math.floor(this.m_options.yaxis2.tickSize / this.m_options.yaxis2.smallTickNum),  //-Math.floor()部分：Y軸のマージン確保のため微調整
					max: this.m_options.yaxis2.min + (this.m_options.yaxis2.tickSize * (this.m_options.yaxis2.tickNum - 1)) + 
							Math.ceil(this.m_options.yaxis2.tickSize / this.m_options.yaxis2.smallTickNum),  //+Math.ceil()部分Y軸のマージン確保のため微調整
					smallTickNum: this.m_options.yaxis2.smallTickNum,
					halfAxisFlg: this.m_options.yaxis2.halfAxisFlg
				},
				grid: {
					verticalLines: this.m_options.xaxis.grid.show,
					horizontalLines: this.m_options.yaxis.grid.show,
					color_cd: utils.convertToRgba(this.m_options.textColor),	// => primary color_cd used for outline and labels
					outlineWidth: 0,
					xAxisLabelMargin: this.m_options.xaxis.grid.labelMargin,
					yAxisLabelMargin: this.m_options.yaxis.grid.labelMargin
				},
				mouse : {
					unitScaling: this.unitScaling,
					pow: Math.pow(10,precise),
					track: true,
					trackAll: false,
					trackY: false,
					trackDecimals: 0,
					lineColor: null,
					relative: true,
					position: 'se',
					sensibility: 1,
					options:this.m_options,
					trackFormatter: function (o) {
						var that = this;
						var html = '';
						//表示データ取得
						/* global _ */
						var tTime = Number(o.x);
						var px = _.find(d_px, function(value, key){
							return value[0] === tTime;
						});
						var offer = _.find(d_offer_amt, function(value, key){
							return value[0] === tTime;
						});
						var bid = _.find(d_bid_amt, function(value, key){
							return value[0] === tTime;
						});


						//時刻の表示文字列生成
						var d1 = new Date(Number(tTime));
						var sTime = ("0"+ d1.getHours()).slice(-2) + ':' + ("0"+ d1.getMinutes()).slice(-2);

						var pxVal,offerVal,bidVal;

						if (px) {
							pxVal = (Math.round(px[1] * 100) / 100).toLocaleString(); //3桁区切り
						} else {
							pxVal = '-';
						}

						if (offer) {
							offerVal = (Math.round(offer[1] / this.unitScaling * this.pow) / this.pow).toLocaleString(); //3桁区切り
						} else {
							offerVal = '-';
						}

						if (bid) {
							bidVal = (Math.round(bid[1] / this.unitScaling * this.pow) / this.pow).toLocaleString(); //3桁区切り
						} else {
							bidVal = '-';
						}

						var prevPx = _data.prev_px.close;
						if (prevPx) {
							prevPx = (Math.round(prevPx * 100) / 100).toLocaleString(); //3桁区切り
						} else {
							prevPx = '-';
						}


						html  += '<tr><td>時刻</td><td>' + sTime + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.offer_amt.color_cd) + ';"><td>売金額</td><td>' + offerVal + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.bid_amt.color_cd) + ';"><td>買金額</td><td>' + bidVal + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.px.color_cd) + ';"><td>株価</td><td>' + pxVal + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.prevClose.color_cd) + ';"><td>前日終値</td><td>' + prevPx + '</td></tr>';
						html = '<table>' + html + '</table>';
						return html;
					}
				},
				crosshair : {
					mode : 'x',
					color_cd: 'rgb(255, 255, 255)',
					hideCursor: true
				}
			};


			//拡大
			var that = this;
			Flotr.EventAdapter.observe(this.m_options.target.get(0), 'flotr:select', function (area) {
				gOpitons.xaxis.min = area.x1;
				gOpitons.xaxis.max = area.x2;
				Flotr.draw(that.m_options.target.get(0), gDataSet, gOpitons);
			});

			// グラフの描画
			/* global Flotr */
			// Hook into the 'flotr:select' event.
			var graph = Flotr.draw(this.m_options.target.get(0), gDataSet, gOpitons, this);

			return this;
		},

		/**
		 * 渡された値からY軸の目盛に表示する値を返します
		 * @param number 値
		 * @param number Max値
		 * @return number 表示する値
		 */
		_getYticks: function(val, maxVal) {
			//max値が5桁以上の場合：千単位、8桁以上の場合：百万単位にする
			var num = val / this.unitScaling;
			return num;
		},


		/**
		 * 渡された数値から3桁区切りの文字列を返します
		 * @param number num 数値
		 * @return string 3桁区切り文字列
		 */
		_addComma: function (num) {

			return num.toString().replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,' );
		}
	};
	return MarketOrderChart;
});
