define([
	'jquery',
	'flotr2',
	'chartUtils'
], function ($, flotr2, utils) {

	"use strict";

	var MarketExecChart = function(anyOptions){
		this.unitScaling = 1e+9;
		this.unitScalingText = '十億円';
		this.m_options = {
			width: 480, //canvas幅
			height: 320, //canvas高さ
			textColor: [255, 255, 255, 1],
			backgroundColor: [0, 0, 0, 1], //背景色
			targetMarket: 0,//"対象範囲: 0=全部、1=東証一部のみ、2=東証一部以外"
			xaxis: {
				showLabels: true,		  //横（項目）軸ラベル
				color_cd: [255, 255, 255, 1], //線、目盛の色
				smallTickNum: 3,		   //小目盛の数
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1,				   //線の幅
					labelMargin:3			   //ラベルマージン 軸位置などの調整用
				}
			},
			yaxis: {  //必須：最小値、目盛幅、目盛の数
				showLabels: true,		  //縦（値）軸ラベル
				color_cd: [255, 255, 255, 1], //線、目盛の色
				min: 0,					//最小値
				tickSize: 0,			   //目盛幅
				tickNum: 0,				//ｙ軸の目盛の数
				smallTickNum: 5,		   //小目盛の数
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1,				   //線の幅
					labelMargin:20			  //ラベルマージン 軸位置などの調整用
				}
			},
			charts: {
				current: {//当日の累積売買代金
					color_cd: [255, 255, 255, 1], //線の色,透明度
					width:  2,				 //線の幅
					shadowSize:false,		  //flotr2のデフォルトの影非表示
					shadowColor: [0, 0, 0, 1], //影の色
					shadowBlur: 2,			 //ぼかしの大きさ
					shadowOffsetX:2,		   //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:2			//テキストを基準にしたシャドウの垂直方向の距離
				},
				avr_max: {//25日平均上限
					color_cd: [255, 255, 255, 1], //線の色,透明度
					width:  2,				 //線の幅
					shadowSize:false,		  //flotr2のデフォルトの影非表示
					shadowColor: [0, 0, 0, 1], //影の色
					shadowBlur: 2,			 //ぼかしの大きさ
					shadowOffsetX:2,		   //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:2			//テキストを基準にしたシャドウの垂直方向の距離
				},
				avr_min: {//25日平均下限
					color_cd: [255, 255, 255, 1], //線の色,透明度
					width:  2,				 //線の幅
					shadowSize:false,		  //flotr2のデフォルトの影非表示
					shadowColor: [0, 0, 0, 1], //影の色
					shadowBlur: 2,			 //ぼかしの大きさ
					shadowOffsetX:2,		   //テキストを基準にしたシャドウの水平方向の距離
					shadowOffsetY:2			//テキストを基準にしたシャドウの垂直方向の距離
				},
			},
			reDrawFlg: false				   //true の場合、グラフクリック時にグラフを再描画
		};

		// オプションをセット
		this.setOptions(anyOptions);
	};


	MarketExecChart.prototype = {
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
		draw: function(data){
		
			// 指定要素へのスタイル付与
			var backgroundColor = utils.convertToRgba(this.m_options.backgroundColor);
			$(this.m_options.target).css({
				width: this.m_options.width + 'px',
				height: this.m_options.height + 'px',
				backgroundColor: backgroundColor,
			});

			//Y軸目盛ラベル作成
			var yticks = [],
				i, j, num, axis;
			for (i = 0 ; i < this.m_options.yaxis.tickNum; i++) {
				num = this.m_options.yaxis.min + this.m_options.yaxis.tickSize * i;
				axis = this._getYticks(num).toString().replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,' );	//3桁区切り
				yticks.push([num, axis]);
			}

			var d_current = [],   //当日の累積売買代金
				d_avr_max = [],   //25日平均上限
				d_avr_min = [],   //25日平均下限
				d_base = [];	  //ダミー
			var nTime, time;
			var today = new Date();

			//折れ線用データ配列作成
			var now = new Date();
			for (i = 0; i < data.length; i++) {
				nTime = data[i].time;
				//time = new Date(today.getFullYear(), today.getMonth(), today.getDate(), utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime)).getTime();
				time = now.setHours(utils.getHH(nTime), utils.getMM(nTime), utils.getSS(nTime),0);//setHoursはミリ秒を返す
				if (data[i].current === -1) {
					d_current.push([time, null]);
				}else {
					d_current.push([time, data[i].current]);
				}
				d_avr_max.push([time, data[i].avr_max]);
				d_avr_min.push([time, data[i].avr_min]);
			}

			//x軸に表示する目盛（時間）を作っている
			var hourTicks = [];
			for(var t=9;t<=15;t++){
				hourTicks.push(now.setHours(t,0,0,0));
				hourTicks.push(now.setHours(t,30,0,0));
			}

			//グラフのデータセット作成
			var gDataSet = [];


			gDataSet.push({		//25日平均下限
				data:d_avr_min,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.avr_min.color_cd),
					fill: true,
					setClipPath:true,
					fillColor: utils.convertToRgba(this.m_options.charts.avr_min.color_cd),
					lineWidth: this.m_options.charts.avr_min.width,
					shadowSize: this.m_options.charts.avr_min.shadowSize,
					shadowColor: utils.convertToRgba(this.m_options.charts.avr_min.shadowColor),
					shadowBlur: this.m_options.charts.avr_min.shadowBlur,
					shadowOffsetX: this.m_options.charts.avr_min.shadowOffsetX,
					shadowOffsetY: this.m_options.charts.avr_min.shadowOffsetY
				}
			});
			gDataSet.push({		//25日平均上限
				data:d_avr_max,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.avr_max.color_cd),
					fillOpacity: 0.3,
					fill: true,
					clip:true,
					fillColor: utils.convertToRgba(this.m_options.charts.avr_max.color_cd),
					lineWidth: this.m_options.charts.avr_max.width,
					shadowSize: this.m_options.charts.avr_max.shadowSize,
					shadowColor: utils.convertToRgba(this.m_options.charts.avr_max.shadowColor),
					shadowBlur: this.m_options.charts.avr_max.shadowBlur,
					shadowOffsetX: this.m_options.charts.avr_max.shadowOffsetX,
					shadowOffsetY: this.m_options.charts.avr_max.shadowOffsetY
				}
			});
			gDataSet.push({		//当日の累積売買代金
				data:d_current,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.current.color_cd),
					fillOpacity: utils.getFillOpacity(this.m_options.charts.current.color_cd),
					lineWidth: this.m_options.charts.current.width,
					shadowSize: this.m_options.charts.current.shadowSize,
					shadowColor: utils.convertToRgba(this.m_options.charts.current.shadowColor),
					shadowBlur: this.m_options.charts.current.shadowBlur,
					shadowOffsetX: this.m_options.charts.current.shadowOffsetX,
					shadowOffsetY: this.m_options.charts.current.shadowOffsetY
				}
			});
			var precise = 2;
			if(this.m_options.targetMarket === 2){
				precise = 3;
			}
			// オプションのセット
			var maxTime = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 15, 10, 0).getTime(); //X軸のマージン確保のため微調整
			var gOpitons = {
				tytitle:"（" + this.unitScalingText + "）",
				fontColor: utils.convertToRgba(this.m_options.textColor),
				HtmlText: false,
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
					min: this.m_options.yaxis.min  - Math.floor(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //-Math.floor()部分：Y軸のマージン確保のため微調整
					max: this.m_options.yaxis.min + (this.m_options.yaxis.tickSize * (this.m_options.yaxis.tickNum - 1)) + 
						   Math.ceil(this.m_options.yaxis.tickSize / this.m_options.yaxis.smallTickNum),  //+Math.ceil()部分Y軸のマージン確保のため微調整
					smallTickNum: this.m_options.yaxis.smallTickNum,
					grid: {
						show: this.m_options.yaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.yaxis.grid.color_cd),
						width: this.m_options.yaxis.grid.width
					}
				},
				grid: {
					verticalLines: this.m_options.xaxis.grid.show,
					horizontalLines: this.m_options.yaxis.grid.show,
					color_cd: utils.convertToRgba(this.m_options.textColor), 
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
						var html = '';
						//表示データ取得
						/* global _ */
						var tTime = Number(o.x);
						var current = _.find(d_current, function(value, key){
							return value[0] === tTime;
						});
						var max = _.find(d_avr_max, function(value, key){
							return value[0] === tTime;
						});
						var min = _.find(d_avr_min, function(value, key){
							return value[0] === tTime;
						});
						//時刻の表示文字列生成
						var d1 = new Date(Number(tTime));
						var sTime = ("0"+ d1.getHours()).slice(-2) + ':' + ("0"+ d1.getMinutes()).slice(-2);

						//十億単位に変換
						var sCurrentVal =  (Math.round(current[1] / this.unitScaling  * this.pow) / this.pow).toLocaleString();
						var sMaxVal = (Math.round(max[1] / this.unitScaling * this.pow) / this.pow).toLocaleString();
						var sMinVal = (Math.round(min[1] / this.unitScaling * this.pow) / this.pow).toLocaleString();

						//3桁区切り文字列に変換
						var currentVal = "";
						if (sCurrentVal !== null){
							currentVal =  sCurrentVal;
						}else {
							currentVal = "-";
						}
						var maxVal = sMaxVal;
						var minVal = sMinVal;

						html  += '<tr><td>時刻</td><td>' + sTime + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.avr_max.color_cd) + ';"><td>＋σ</td><td>' + maxVal + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.current.color_cd) + ';"><td>当日</td><td>' + currentVal + '</td></tr>';
						html  += '<tr style="color_cd:' + utils.convertToRgba(this.options.charts.avr_min.color_cd) + ';"><td>−σ</td><td>' + minVal + '</td></tr>';
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
			// グラフの描画
			/* global Flotr */
			var graph = Flotr.draw(this.m_options.target.get(0), gDataSet, gOpitons, this);

			return this;
		},

		/**
		 * 渡された値からY軸の目盛に表示する値を返します
		 * @param number 値
		 * @return number 表示する値
		 */
		_getYticks: function(val) {
			//百万単位にする
			var num = val;
			/*
			if (val >= 1000000000) {
				num = val / 1000000000;
			}
			*/
			num = val / this.unitScaling;
			return num;
		}

	};
	return MarketExecChart;
});
