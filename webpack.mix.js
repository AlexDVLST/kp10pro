let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.js('resources/assets/js/app.js', 'public/js')
//    .sass('resources/assets/sass/app.scss', 'public/css');

mix.js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/pages/offers.js', 'public/js/pages/offers.min.js')
    .js('resources/assets/js/pages/products.js', 'public/js/pages/products.min.js')
    .js('resources/assets/js/pages/products-dopfields.js', 'public/js/pages/products-dopfields.min.js')
    .js('resources/assets/js/pages/products-edit.js', 'public/js/pages/products-edit.min.js')
    .js('resources/assets/js/pages/offers-deleted.js', 'public/js/pages/offers-deleted.min.js')
    .js('resources/assets/js/tour.js', 'public/js/tour.min.js')
    .js('resources/assets/js/pages/file-manager.js', 'public/js/pages/file-manager.min.js')
    .js('resources/assets/js/pages/client-create.js', 'public/js/pages/client-create.min.js')
    .js('resources/assets/js/pages/client-edit.js', 'public/js/pages/client-edit.min.js')
    .js('resources/assets/js/pages/clients.js', 'public/js/pages/clients.min.js')
    //Settings
    .js('resources/assets/js/pages/settings/currency-edit.js', 'public/js/pages/settings/currency-edit.min.js')
    .js('resources/assets/js/pages/settings/currencies.js', 'public/js/pages/settings/currencies.min.js')
    .js('resources/assets/js/pages/settings/employee-edit.js', 'public/js/pages/settings/employee-edit.min.js')
    .js('resources/assets/js/pages/settings/employee-create.js', 'public/js/pages/settings/employee-create.min.js')
    .js('resources/assets/js/pages/settings/employees.js', 'public/js/pages/settings/employees.min.js')
    .js('resources/assets/js/pages/settings/roles.js', 'public/js/pages/settings/roles.min.js')
    .js('resources/assets/js/pages/settings/role-edit.js', 'public/js/pages/settings/role-edit.min.js')
    .js('resources/assets/js/pages/settings/order.js', 'public/js/pages/settings/order.min.js')
    .js('resources/assets/js/pages/settings/order-create.js', 'public/js/pages/settings/order-create.min.js')
    .js('resources/assets/js/pages/settings/order-edit.js', 'public/js/pages/settings/order-edit.min.js')
    .js('resources/assets/js/pages/settings/integration.js', 'public/js/pages/settings/integration.min.js')
    .js('resources/assets/js/pages/settings/integration-email.js', 'public/js/pages/settings/integration-email.min.js')
    .js('resources/assets/js/pages/settings/scenario.js', 'public/js/pages/settings/scenario.min.js')
    //Admin
    .js('resources/assets/js/pages/admin/offers.js', 'public/js/pages/admin/offers.min.js')
    .js('resources/assets/js/pages/admin/helps.js', 'public/js/pages/admin/helps.min.js');

mix.sass('resources/assets/sass/app.scss', 'public/css')
    .sass('resources/assets/sass/bootstrap-tour.scss', 'public/css')
    .sass('resources/assets/sass/pages/file-manager.scss', 'public/css/pages')
    .sass('resources/assets/sass/editor.scss', 'public/css')
    .sass('resources/assets/sass/pages/offers.scss', 'public/css/pages')
    .sass('resources/assets/sass/pages/products.scss', 'public/css/pages')
    .sass('resources/assets/sass/pages/product-edit.scss', 'public/css/pages/')
    .sass('resources/assets/sass/pages/client.scss', 'public/css/pages/')
    .sass('resources/assets/sass/pages/clients.scss', 'public/css/pages/')
    .sass('resources/assets/sass/pages/settings/currencies.scss', 'public/css/pages/settings')
    .sass('resources/assets/sass/pages/settings/employee.scss', 'public/css/pages/settings')
    .sass('resources/assets/sass/pages/settings/employees.scss', 'public/css/pages/settings')
    .sass('resources/assets/sass/pages/settings/integration.scss', 'public/css/pages/settings')
    .sass('resources/assets/sass/icomoon/style.scss', 'public/css/icomoon.min.css')
    // Widgets
    .sass('resources/assets/widgets/megaplan/kp10.scss', 'public/widgets/megaplan');

//Grapesjs plugins
mix.js('resources/assets/js/grapesjs/templates/base/index.js', 'public/js/grapesjs/grapesjs-plugin-kp10-base.min.js')
    .js('resources/assets/js/grapesjs/templates/base/main.js', 'public/js/grapesjs/templates/base/main.min.js')
    .sass('resources/assets/js/grapesjs/templates/base/style.scss', 'public/js/grapesjs/css/grapesjs-plugin-kp10-base.min.css');

//Integration
mix.js('resources/assets/js/integration/bitrix24.js', 'public/js/integration/bitrix24.min.js')
    .sass('resources/assets/js/integration/bitrix24.scss', 'public/widgets/bitrix24');

//Tilda
mix.js('resources/assets/js/integration/tilda/tilda.js', 'public/js/integration/tilda.min.js');

//Widgets
mix.js('resources/assets/widgets/megaplan/kp10.js', 'public/widgets/megaplan/kp10.min.js');

//Welcome page
mix.js('resources/assets/js/welcome.js', 'public/js/welcome.min.js');

//Notification
mix.js('resources/assets/js/notifications/register-bonus.js', 'public/js/notifications/register-bonus.min.js')
    .sass('resources/assets/sass/notifications/register-bonus.scss', 'public/css/notifications/register-bonus.min.css');

if (mix.inProduction()) {
    mix.version();
}

// mix.browserSync({
//     proxy: 'kp10.local',
//     https: true
// });