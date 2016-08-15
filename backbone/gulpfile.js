var gulp = require('gulp');
var gutil = require('gulp-util');
var uglify = require('gulp-uglify');
var handlebars = require('gulp-handlebars');
var rename = require('gulp-rename');
var concat = require("gulp-concat");
var defineModule = require('gulp-define-module');
var declare = require('gulp-declare');
//var sourcemaps = require('gulp-sourcemaps');
var wrapper = require("gulp-wrapper");
var jshint = require("gulp-jshint");
var rimraf = require('gulp-rimraf');
var replace = require("gulp-replace");

gulp.task("uglify", function() {
    return gulp.src('app/scripts/*js')
        .pipe(uglify())
        .pipe(gulp.dest("build/scripts"));
});

var createTemplate = function(module) {
    var moduleName = module.charAt(0).toUpperCase() + module.slice(1);
    var header = "'use strict';\ndefine(['app', 'handlebars'], function(App,Handlebars) {\nreturn App.module('" + moduleName + ".Templates', function(Templates){\n";
    var footer = "\n});});\n";

    var dest = "app/scripts/modules/" + module;
    var templateFile = "Templates.js";
    gutil.log("createTemplate: " + dest + "/" + templateFile);
    var stream = gulp.src("app/scripts/modules/" + module + "/templates/*.hbs")
        //.pipe(sourcemaps.init())
        .pipe(handlebars())
        //.pipe(defineModule('plain'))
        .pipe(defineModule('plain',{
            wrapper: 'Templates["<%= name %>"] = <%= handlebars %>'
        }))
        //.pipe(declare({
        //  namespace: "App.templates." + module,
        //  noRedeclare: true
        //}))
        .pipe(concat(templateFile))
        .pipe(uglify())
        //.pipe(sourcemaps.write())
        .pipe(wrapper({
            header: header,
            footer: footer
        }))
        .pipe(gulp.dest(dest));
    return stream;
};

gulp.task("handlebars", function() {
    var modules = ["analysis", "common", "order", "position", "admin"];
    for(var i=0;i < modules.length; i++){
        createTemplate(modules[i]);
    }
});
/*
gulp.task("watch", function() {
    return gulp.watch('app/scripts/*js', ['uglify']);
});

gulp.task('default', ['uglify', 'watch']);
*/
gulp.task("watch-templates", function() {
    //return gulp.watch('app/scripts/modules/**/templates/*hbs', ['handlebars']);
    return gulp.watch('app/scripts/modules/**/templates/*hbs', function(event){
//        var file = event.path.split('/');
        var file = event.path.split(/[\/\\]/);
        var module = file[file.length-3];
        createTemplate(module);
    });
});

gulp.task("watch-jshint", function() {
    return gulp.watch([
        'app/scripts/*.js',
        'app/scripts/entities/**/*.js',
        'app/scripts/modules/**/*.js',
        'app/scripts/modules/**/**/*.js'
    ], function(event){
        if (event.path.match(/Templates.js/) !== null) {
            return;
        }
        gulp.src(event.path)
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
    
    });
});

gulp.task('removeAppDir', function(){
    "use strict";
    return gulp.src('deploy/app').pipe(rimraf());
});
gulp.task("build",['removeAppDir'], function(){
    "use strict";
    var now = String(new Date().getTime());
    gulp.src('app/*html')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("deploy/app"));
    gulp.src('app/config.js')
        .pipe(replace(/%timestamp%/ , now))
        .pipe(gulp.dest("deploy/app"));
    gulp.src('app/imgs/**').pipe(gulp.dest("deploy/app/imgs"));
    gulp.src('app/css/**').pipe(gulp.dest("deploy/app/css"));
    gulp.src('app/help/**').pipe(gulp.dest("deploy/app/help"));
    gulp.src('app/header/ITS/**')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("deploy/app/header/ITS"));
    gulp.src('app/header/static/**').pipe(gulp.dest("deploy/app/header/static"));
    gulp.src('app/bower_components/requirejs/require.js').pipe(uglify()).pipe(gulp.dest("deploy/app/bower_components/requirejs"));
    gulp.src('app/bower_components/jquery/dist/jquery.min.map').pipe(gulp.dest("deploy/app/scripts"));
    gulp.src('app/scripts/lib/jquery-ui/**').pipe(gulp.dest("deploy/app/scripts/lib/jquery-ui"));
    gulp.src('app/scripts/lib/ga.js')
        .pipe(gulp.dest("deploy/app/scripts/lib"));

});

gulp.task('removeAdminDir', function(){
    "use strict";
    return gulp.src('deploy/admin').pipe(rimraf());
});
gulp.task("buildAdmin",['removeAdminDir'], function(){
    "use strict";
    var now = String(new Date().getTime());
    gulp.src('admin/*html')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(replace(/\.\.\/app/g , '.'))
        .pipe(gulp.dest("deploy/admin"));
    gulp.src('admin/scripts/config.js')
        .pipe(replace(/%timestamp%/ , now))
        .pipe(replace(/\/app/g , '.'))
        .pipe(gulp.dest("deploy/admin/scripts"));
    gulp.src('admin/imgs/**').pipe(gulp.dest("deploy/admin/imgs"));
    gulp.src('admin/css/**').pipe(gulp.dest("deploy/admin/css"));
    gulp.src('app/bower_components/requirejs/require.js').pipe(uglify()).pipe(gulp.dest("deploy/admin/bower_components/requirejs"));
    gulp.src('app/bower_components/bootstrap/dist/css/**').pipe(gulp.dest("deploy/admin/bower_components/bootstrap/dist/css"));
    gulp.src('app/bower_components/bootstrap/dist/fonts/**').pipe(gulp.dest("deploy/admin/bower_components/bootstrap/dist/fonts"));
    gulp.src('app/bower_components/eonasdan-bootstrap-datetimepicker/build/css/**').pipe(gulp.dest("deploy/admin/bower_components/eonasdan-bootstrap-datetimepicker/build/css"));
    gulp.src('app/bower_components/jquery/dist/jquery.min.map').pipe(gulp.dest("deploy/admin/scripts"));
});

gulp.task('removeAnalysisDir', function(){
    "use strict";
    return gulp.src('deploy/analysis').pipe(rimraf());
});
gulp.task("buildAnalysis",['removeAnalysisDir'], function(){
    "use strict";
    var now = String(new Date().getTime());
    gulp.src('app/individual_analysis.html')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("deploy/analysis"));
    gulp.src('app/past_analysis.html')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("deploy/analysis"));
    gulp.src('app/Top.html')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("deploy/analysis"));
    gulp.src('app/config.js')
        .pipe(replace(/%timestamp%/ , String(new Date().getTime())))
        .pipe(gulp.dest("deploy/analysis"));
    gulp.src('app/imgs/**').pipe(gulp.dest("deploy/analysis/imgs"));
    gulp.src('app/css/**').pipe(gulp.dest("deploy/analysis/css"));
    gulp.src('app/help/**').pipe(gulp.dest("deploy/analysis/help"));
    gulp.src('app/header/ITS/**')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("deploy/analysis/header/ITS"));
    gulp.src('app/header/static/**').pipe(gulp.dest("deploy/analysis/header/static"));
    gulp.src('app/bower_components/requirejs/require.js').pipe(uglify()).pipe(gulp.dest("deploy/analysis/bower_components/requirejs"));
    gulp.src('app/bower_components/jquery/dist/jquery.min.map').pipe(gulp.dest("deploy/analysis/scripts"));
    gulp.src('app/scripts/lib/jquery-ui/**').pipe(gulp.dest("deploy/analysis/scripts/lib/jquery-ui"));
    gulp.src('app/scripts/lib/ga.js')
        .pipe(gulp.dest("deploy/analysis/scripts/lib"));

});


gulp.task('removeErrDir', function(){
    "use strict";
    return gulp.src('err').pipe(rimraf());
});
gulp.task('removeDeployErrDir', function(){
    "use strict";
    return gulp.src('deploy/err').pipe(rimraf());
});
gulp.task("err",['removeErrDir'], function(){
    "use strict";
    var now = String(new Date().getTime());
    gulp.src('app/400.html').pipe(gulp.dest("err"));
    gulp.src('app/imgs/common/logo_ialgo.png').pipe(gulp.dest("err/imgs/common"));
    gulp.src('app/imgs/common/bg_header*').pipe(gulp.dest("err/imgs/common"));
    gulp.src('app/imgs/common/bg_bunseki_grad.png').pipe(gulp.dest("err/imgs/common"));
    gulp.src('app/imgs/apple-touch*').pipe(gulp.dest("err/imgs"));
    gulp.src('app/imgs/favicon.ico').pipe(gulp.dest("err/imgs"));
    gulp.src('app/css/**').pipe(gulp.dest("err/css"));
    gulp.src('app/header/ITS/**')
        .pipe(replace(/%timestamp%/g , now))
        .pipe(gulp.dest("err/header/ITS"));
    gulp.src('app/header/static/**').pipe(gulp.dest("err/header/static"));
    gulp.src('app/scripts/lib/ga.js')
        .pipe(gulp.dest("err/scripts/lib"));

});
gulp.task("errDeploy",['removeDeployErrDir'], function(){
    "use strict";
    gulp.src('err/**').pipe(gulp.dest("deploy/err"));
});
gulp.task('test',function(){
    "use strict";
    console.log(String(new Date().getTime()));
});


gulp.task('default', ['watch-templates','watch-jshint']);
