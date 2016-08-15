'use strict';
define([
	"jquery",
	"underscore"
],
function($,_) {
	var ImpactChart = function(options){
		this.options = {};
		options || (options = {});
		_.extend(this.options, options);
		var target = this.options.target;
		this.setOptions = function(options) {
			options || (options = {});
			_.extend(this.options, options);
		};
		this.draw = function(data) {
			var that = this;
			var canvas = $('<canvas width="' + this.options.width + '" height="' + this.options.height + '" />').get(0);
			target.empty();
			target.append(canvas);
			if ( ! canvas || ! canvas.getContext ) { return false; }
			var ctx = canvas.getContext('2d');

			var m = this.options.width / 180;

			ctx.fillStyle = 'rgb(0,0,0)';
			ctx.fillRect(0, 0, this.options.width, this.options.height);

			ctx.font = (8 * m) + "px 'Oxygen Mono'";
			ctx.fillStyle = "white";
			ctx.fillText(data.unit, 32 * m , 94 * m);

			var xMax = data.unit * 3;
			var yMax = this.options.yaxis.tickSize * this.options.yaxis.tickNum * 2;



			ctx.beginPath();
			/*
			ctx.moveTo(5 * m, 46 * m);
			ctx.lineTo(30 * m, 50 * m);
			ctx.lineTo(60 * m, 58 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(90 * m, 68 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(120 * m, 80 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(150 * m, 95 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			//ctx.closePath();
			*/


			ctx.moveTo(0 * m, this.options.height / 2);
			_.each(data.sell, function(v){
				ctx.lineTo(that.options.width * v.W / xMax, that.options.height / 2 +  (v.Q - data.px) / yMax * that.options.height);
			});
			ctx.lineWidth = 2;
			ctx.strokeStyle = "rgba(80, 80, 255, 0.7)";
// これらの座標に対して線を引く指令
			ctx.stroke();


			ctx.beginPath();
			/*
			ctx.moveTo(5 * m, 42 * m);
			ctx.lineTo(30 * m, 41 * m);
			ctx.lineTo(60 * m, 38 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(90 * m, 30 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(120 * m, 21 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(150 * m, 10 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			ctx.lineTo(170 * m, 5 * m + Math.floor((Math.random() - 0.5) * 3 * m));
			//ctx.closePath();
			*/
			ctx.moveTo(0 * m, this.options.height / 2);
			_.each(data.buy, function(v){
				ctx.lineTo(that.options.width * v.W / xMax, that.options.height / 2 +  (v.Q - data.px) / yMax * that.options.height);
			});
			ctx.lineWidth = 2;
			ctx.strokeStyle = "rgba(255, 0, 180, 0.7)";
// これらの座標に対して線を引く指令
			ctx.stroke();

		}
	};
	return ImpactChart;
});
