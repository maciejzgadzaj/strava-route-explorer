var Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // will create public/build/app.js and public/build/app.css
    .addEntry('app', './assets/js/app.js')
    .addEntry('athletes', './assets/js/athletes.js')
    .addEntry('homepage', './assets/js/homepage.js')
    .addEntry('maintenance', './assets/js/maintenance.js')
    .addEntry('pager', './assets/js/pager.js')
    .addEntry('routes', './assets/js/routes.js')
    .addEntry('select', './assets/js/select.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    // .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    // enable source maps during development
    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()

    // create hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // allow sass/css files to be processed
    .enableSassLoader()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
