define([
	'jquery',
	'flotr2',
	'chartUtils'
], function ($, flotr2, utils) {

	"use strict";

	var ImpactChart = function(anyOptions){

		this.m_options = {
			width: 480,
			height: 320,
			textColor: [255, 255, 255, 1],
			backgroundColor: [0, 0, 0, 1],   //背景色
			midLine:{
				color_cd: [255, 255, 0, 1],	 //仲値の水平線の色
				width: 1					//線の幅
			},
			xaxis: {
				showLabels: true,			//横（項目）軸ラベル
				grid: {
					show: false,				//グリッド
					color_cd: [127, 127, 127, 1],  //グリッドの色,透明度
					width: 1					//線の幅
				}
			},
			yaxis: {  //必須：目盛の数、目盛幅
				showLabels: true,		   //縦（値）軸ラベル
				tickNum: 2,				 //ｙ軸の目盛の数
				tickSize: 0,				//目盛幅
				grid: {
					show: false,				//flotr2グリッド
					color_cd: [127, 127, 127, 1],  //flotr2グリッドの色,透明度
					width: 1					//flotr2線の幅
				}
			},
			grid: {
				color_cd: [127, 127, 127, 1], //グリッドの色,透明度
				width: 1				   //線の幅
			},
			charts: {
				buy: {//買いインパクトのチャート
					color_cd: [0, 160, 234, 1],  //線の色,透明度
					width:  2,				//線の幅
					points: {
						color_cd: [0, 160, 234, 0.7],  //線の色,透明度
						radius: 3				  //ポイントの半径
					},
					impact: {//インパクト矢印
						show: true,				 //矢印
						color_cd: [255, 60, 120, 1],	  //矢印の色
						width: 5				   //線の幅
					}
				},
				sell: {//売りインパクトのチャート
					color_cd: [228, 0, 127, 1],  //線の色,透明度
					width:  2,				//線の幅
					points: {
						color_cd: [228, 0, 127, 0.7],  //線の色,透明度
						radius: 3				  //ポイントの半径
					},
					impact: {//インパクト矢印
						show: true,				 //矢印
						color_cd: [80, 80, 255, 1],	  //矢印の色
						width: 5				   //線の幅
					}
				}
			},
			reDrawFlg: false						//true の場合、グラフクリック時にグラフを再描画
		};

		// オプションをセット
		this.setOptions(anyOptions);

	};

	ImpactChart.prototype = {
		/**
		 * オプションをセットします
		 * @param array anyOptions セットするオプション
		 */
		setOptions: function(anyOptions){
			// オプションのマージ
			$.extend(true, this.m_options, anyOptions);
		},

		/**
		 * 指定データを元にチャートを描画
		 * @param array data データ
		 */
		draw: function(data){

			//Y軸の真ん中
			var baseY = Math.ceil(data.mid / this.m_options.yaxis.tickSize) * this.m_options.yaxis.tickSize  ;


			// 指定要素へのスタイル付与
			var backgroundColor = utils.convertToRgba(this.m_options.backgroundColor);
			$(this.m_options.target).css({
				width: this.m_options.width + 'px',
				height: this.m_options.height + 'px',
				backgroundColor: backgroundColor
			});

			var xMax = data.unit * 3,
				ticksX = [],
				ticksY = [],
				sellData = [],
				buyData = [],
				midData = [
					[0, data.mid],
					[xMax, data.mid]
				],
				setData = [];

			// 売り用配列作成
			for (var i = 0; i < data.sell.length; i++) {
				sellData.push([data.sell[i].W, data.sell[i].Q]);
			}

			// 買い用配列作成
			for (var j = 0; j < data.buy.length; j++) {
				buyData.push([data.buy[j].W, data.buy[j].Q]);
			}

			// Y軸用配列作成
			for (var k = -this.m_options.yaxis.tickNum; k <= this.m_options.yaxis.tickNum; k++) {
				var axis = baseY + (this.m_options.yaxis.tickSize * k);
				// Y軸文言
				ticksY.push([axis, (Math.round(axis * 10) / 10).toLocaleString()]); //3桁カンマ区切り

				// グリッド用
				setData.push({
					shadowSize: 0,
					data: [
						[0, axis],
						[xMax, axis]
					],
					lines: {
						show: true,
						color_cd: utils.convertToRgba(this.m_options.grid.color_cd),
						fillOpacity: this.m_options.grid.color_cd[3],
						lineWidth: this.m_options.grid.width
					}
				});
			}

			// X軸文言
			var unit = data.unit;
			ticksX.push([unit, unit.toString().replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,' )]); //3桁カンマ区切り

			// 表示用データのセット
			setData.push({
				data: midData,
				shadowSize: 0,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.midLine.color_cd),
					fillOpacity: this.m_options.midLine.color_cd[3],
					lineWidth: this.m_options.midLine.width
				}
			});
			setData.push({
				data: buyData,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.buy.color_cd),
					fillOpacity: this.m_options.charts.buy.color_cd[3],
					lineWidth: this.m_options.charts.buy.width
				},
				points: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.buy.points.color_cd),
					fillColor: utils.convertToRgba(this.m_options.charts.buy.points.color_cd),
					fillOpacity: this.m_options.charts.buy.points.color_cd[3],
					radius: this.m_options.charts.buy.points.radius
				}
			});
			setData.push({
				data: sellData,
				lines: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.sell.color_cd),
					fillOpacity: this.m_options.charts.sell.color_cd[3],
					lineWidth: this.m_options.charts.sell.width
				},
				points: {
					show: true,
					color_cd: utils.convertToRgba(this.m_options.charts.sell.points.color_cd),
					fillColor: utils.convertToRgba(this.m_options.charts.sell.points.color_cd),
					fillOpacity: this.m_options.charts.sell.points.color_cd[3],
					radius: this.m_options.charts.sell.points.radius
				}
			});

			// オプションのセット
			var setOpitons = {
				fontColor: utils.convertToRgba(this.m_options.textColor),
				xaxis: {
					min: 0,
					max: xMax,
					ticks: ticksX,
					showLabels: this.m_options.xaxis.showLabels,
					grid: {
						show: this.m_options.xaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.xaxis.grid.color_cd),
						width: this.m_options.xaxis.grid.width
					}
				},
				yaxis: {
					min: baseY - (this.m_options.yaxis.tickSize * this.m_options.yaxis.tickNum) - Math.floor(this.m_options.yaxis.tickSize / 5), //-Math.floor()部分：Y軸のマージン確保のため微調整
					max: baseY + (this.m_options.yaxis.tickSize * this.m_options.yaxis.tickNum) + Math.ceil(this.m_options.yaxis.tickSize / 5),  //+Math.ceil()部分Y軸のマージン確保のため微調整
					ticks: ticksY,
					showLabels: this.m_options.yaxis.showLabels,
					grid: {
						show: this.m_options.yaxis.grid.show,
						color_cd: utils.convertToRgba(this.m_options.yaxis.grid.color_cd),
						width: this.m_options.yaxis.grid.width
					}
				},
				grid: {
					verticalLines: false,
					horizontalLines: false,
					color_cd: utils.convertToRgba(this.m_options.textColor),
					tickColor: utils.convertToRgba(this.m_options.grid.color_cd),
					outlineWidth: 0
				}
			};

			// グラフの描画
			/* global Flotr */
			var graph = Flotr.draw(this.m_options.target.get(0), setData, setOpitons);

			//矢印描画処理
			if (this.m_options.charts.buy.impact.show || this.m_options.charts.sell.impact.show){
			
				// キャンバスの取得
				var context = graph.octx;

				// グラフの描画開始位置
				var graphLeft = graph.plotOffset.left;
				var graphTop = graph.plotOffset.top;

				// 矢印のX軸位置
				var arrowX = this._getPointX(graph.series[setData.length - 3], data.unit, graphLeft);

				// 矢印のY軸開始位置
				var arrowYStart = this._getPointY(graph.series[setData.length - 3], data.mid, graphTop);

				// 買い矢印の描画
				if (this.m_options.charts.buy.impact.show) {
					var arrowYEndBuy = this._getPointY(graph.series[setData.length - 2], data.mid + data.impact_buy, graphTop);
					this._drawArrow(
						context,
						graphLeft,
						[arrowX, arrowYStart],
						[arrowX, arrowYEndBuy],
						this.m_options.charts.buy.impact.width * 1.5,
						this.m_options.charts.buy.impact.width * 1.5,
						this.m_options.charts.buy.impact.width,
						utils.convertToRgba(this.m_options.charts.buy.impact.color_cd)
					);
				}
				
				// 売り矢印の描画
				if (this.m_options.charts.sell.impact.show) {
					var arrowYEndSell = this._getPointY(graph.series[setData.length - 1], data.mid - data.impact_sell, graphTop);
					this._drawArrow(
						context,
						graphLeft,
						[arrowX, arrowYStart],
						[arrowX, arrowYEndSell],
						this.m_options.charts.sell.impact.width * 1.5,
						this.m_options.charts.sell.impact.width * 1.5,
						this.m_options.charts.sell.impact.width,
						utils.convertToRgba(this.m_options.charts.sell.impact.color_cd)
					);
				}
			}

			return this;
		},

		/**
		 * X軸の描画位置を取得します
		 * @param object series
		 * @param number value 描画位置の値
		 * @param  int graphLeft グラフの描画開始位置（左）
		 * @return number 描画位置
		 */
		_getPointX: function(series, value, graphLeft) {
			return series.xaxis.d2p(value) + graphLeft;
		},

		/**
		 * Y軸の描画位置を取得します
		 * @param object series
		 * @param number value 描画位置の値
		 * @param  int graphTop  グラフの描画開始位置（上）
		 * @return number 描画位置
		 */
		_getPointY: function(series, value, graphTop) {
			return series.yaxis.d2p(value) + graphTop;
		},

		/**
		 * 矢印の描画
		 * @param  string context 表示要素
		 * @param  int graphLeft グラフの左位置
		 * @param  array A 矢印の始点
		 * @param  array B 矢印の終点（矢印の頂点）
		 * @param  int w 矢印の幅
		 * @param  int h 矢印の高さ
		 * @param  int lineWidth 線の太さ
		 * @param  string color_cd 矢印の色
		 */
		_drawArrow: function(context, graphLeft, A, B, w, h, lineWidth, color_cd){
			var L = new Array(2);
			var R = new Array(2);

			var Vx = B[0] - A[0];
			var Vy = B[1] - A[1];
			var v  = Math.sqrt(Vx * Vx + Vy * Vy);
			var Ux = Vx / v;
			var Uy = Vy / v;
			L[0] = B[0] - Uy * w - Ux * h;
			L[1] = B[1] + Ux * w - Uy * h;
			R[0] = B[0] + Uy * w - Ux * h;
			R[1] = B[1] - Ux * w - Uy * h;

			context.save();
			context.strokeStyle = color_cd;	// 線の色指定
			context.fillStyle = color_cd;	  // 塗り色指定
			context.beginPath();			// A～Bの線
			context.lineWidth = lineWidth;
			context.moveTo(A[0], A[1]);
			context.lineTo(B[0] - Ux * h * 0.95, B[1] - Uy * h * 0.95);//はみ出し対応
			context.stroke();			 // 線を描く
			context.beginPath();		  // 矢尻
			context.moveTo(L[0], L[1]);
			context.lineTo(B[0], B[1]);
			context.lineTo(R[0], R[1]);
			context.closePath();		  // 閉曲線にする
			context.fill();			   // 塗り
			context.restore();

		}
	};
	return ImpactChart;
});
