({
    //appDir: "app",
    baseUrl: 'app/scripts',
    mainConfigFile: 'admin/scripts/config.js',
        name: "main",
        include: [
            "modules/admin/Login",
            "modules/admin/Home",
            "modules/admin/Order",
            "modules/admin/Issue",
            "modules/admin/Alert",
            "modules/admin/Notice",
            "modules/admin/Password"
        ],
    //optimize: "none", 
    out: "deploy/admin/scripts/main.js"
    //dir: "test"

})
