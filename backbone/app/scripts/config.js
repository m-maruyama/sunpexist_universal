requirejs.config({
	urlArgs: "ver=%timestamp%",
	baseUrl: "/app/scripts",
	paths: {
		"jquery": '../bower_components/jquery/dist/jquery.min',
		"underscore": '../bower_components/lodash/dist/lodash',
		"backbone": '../bower_components/backbone/backbone',
		"backbone.marionette": '../bower_components/marionette/lib/core/backbone.marionette',
		'handlebars': '../bower_components/handlebars/handlebars.runtime',
		"backbone.stickit": '../bower_components/backbone.stickit/backbone.stickit',
		'backbone.wreqr': '../bower_components/backbone.wreqr/lib/backbone.wreqr',
		'backbone.babysitter': '../bower_components/backbone.babysitter/lib/backbone.babysitter',
		'backbone.validation': '../bower_components/backbone-validation/dist/backbone-validation-amd-min',
		'bootstrap': '../bower_components/bootstrap/dist/js/bootstrap.min',
		'bootstrap-datetimepicker':'../bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min',
		//'bootstrap-datetimepicker':'../bower_components/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker',
		'moment': '../bower_components/moment/min/moment-with-locales.min',
		"spin": '../bower_components/spinjs/spin',
		'jquery-spin': '../bower_components/spinjs/jquery.spin',
		'typeahead': './lib/typeahead.jquery.kai',
		'bloodhound': '../bower_components/typeahead.js/dist/bloodhound.min',
		'cookie': '../bower_components/jquery-cookie/jquery.cookie',
		'jquery-ui-origin': './lib/jquery-ui/jquery-ui.min',
		'jquery-ui': './lib/jquery-ui.custom',
		//"easySelectBox": "./lib/easySelectBox",
		//"jquery.jscrollpane": "./lib/jquery.jscrollpane.min",
		'flotr2': './lib/chart/flotr2.amd',
		'chartUtils': './lib/chart/utils',
		'bean': './lib/chart/bean',
		'impactChart': './lib/chart/impactChart',
		'mainChart': './lib/chart/mainChart',
		'marketExecChart': './lib/chart/marketExecChart',
		'marketOrderChart': './lib/chart/marketOrderChart',
		'tablefix': './lib/jquery.tablefix',
		'blockUI': './lib/jquery.blockUI',
		'jquery_s': './lib/jquery.min',
		'floatThead': './lib/jquery.floatThead',
		'floatThead_': './lib/jquery.floatThead._',
		'sirius': './lib/sirius',
		"Entities": "entities"

	},
	shim: {
		handlebars: {
			exports: 'Handlebars'
		}
		/*
		underscore: {
			exports: '_'
		},

		template: ['handlebars']
		*/
	},
	deps: ['main'] // <-- run our app
});

