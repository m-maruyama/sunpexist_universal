#!/bin/bash

sed -i '' "s/  host =/  \/\/host =/g" app/scripts/app.js
sed -i '' "s/withCredentials = true/withCredentials = false/g" app/scripts/app.js
sed -i '' "s/ \/\/\$('body').append/ \$('body').append/g" app/scripts/modules/common/controllers/Default.js
#sed -i '' "s/\/\/this.ui.help.hide/this.ui.help.hide/g" app/scripts/modules/common/views/Header.js

gulp build
node_modules/requirejs/bin/r.js -o build.js

#分析版
#gulp buildAnalysis
#echo "var \$forAnalysis = true;" >> deploy/analysis/config.js
#node_modules/requirejs/bin/r.js -o buildAnalysis.js

gulp buildAdmin
node_modules/requirejs/bin/r.js -o buildAdmin.js

#sed -i '' "s/this.ui.help.hide/\/\/this.ui.help.hide/g" app/scripts/modules/common/views/Header.js
sed -i '' "s/ \$('body').append/ \/\/\$('body').append/g" app/scripts/modules/common/controllers/Default.js
sed -i '' "s/\/\/host =/host =/g" app/scripts/app.js
sed -i '' "s/withCredentials = false/withCredentials = true/g" app/scripts/app.js

gulp err
gulp errDeploy
