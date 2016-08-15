({
    //appDir: "app",
    baseUrl: 'app/scripts',
    mainConfigFile: 'app/config.js',
        name: "main",
        include: [
            "modules/order/New",
            "modules/order/Cancel",
            "modules/order/Repayment",
            "modules/analysis/Individual",
            "modules/analysis/Past",
            "modules/analysis/Market",
            "modules/position/Position",
            "modules/position/TradeHistory",
            "modules/position/ExecList"
        ],
    //optimize: "none", 
    out: "deploy/app/scripts/main.js"
    //dir: "test"

})
