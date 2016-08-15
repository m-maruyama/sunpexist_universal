define(function(){
	"use strict";
	var utils = {};
	
	/**
	 * 渡された配列をrgba形式で返します
	 * @param array rgbaArray rgba配列
	 * @return stinrg rgba形式の文字列
	 */
	utils.convertToRgba = function(rgbaArray) {
		return 'rgba(' + rgbaArray[0]+ ',' + rgbaArray[1] + ',' + rgbaArray[2] + ',' + rgbaArray[3] + ')';
	};

	/**
	 * 渡された配列をrgba形式で返します
	 * @param array rgbaArray rgba配列
	 * @return stinrg rgba形式の文字列
	 */
	utils.convertToRgb = function(rgbaArray) {
		return 'rgb(' + rgbaArray[0]+ ',' + rgbaArray[1] + ',' + rgbaArray[2] + ')';
	};

	/**
	 * 渡された配列からを透明度の値を返します
	 * @param array rgbaArray rgba配列
	 * @return number rgba形式の文字列
	 */
	utils.getFillOpacity = function(rgbaArray) {
	  return rgbaArray[3];
	};
	
	/**
	 * 渡された時刻（integer：HHMMSS)から時を返します
	 * @param integer nTime
	 * @return number 時
	 */
	utils.getHH = function(nTime) {
		return nTime / 10000;
	};

	/**
	 * 渡された時刻（integer：HHMMSS)から分を返します
	 * @param integer nTime
	 * @return number 分
	 */
	utils.getMM = function (nTime) {
		var rest = nTime % 10000;
		return rest / 100;
	};

	/**
	 * 渡された時刻（integer：HHMMSS)から秒を返します
	 * @param integer nTime
	 * @return number 秒
	 */
	utils.getSS = function (nTime) {
		var rest = nTime % 10000;
		return rest % 100;
	};
	
	return utils;
});
