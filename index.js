/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const path = require('path');
const Encore = require('@symfony/webpack-encore');

class SyliusAdmin {
    static getWebpackConfig(rootDir) {
        this._prepareWebpackConfig(rootDir);
        Encore
            .addEntry('admin-entry', path.resolve(__dirname, 'Resources/assets/entrypoint.js'))

        const adminConfig = Encore.getWebpackConfig();

        adminConfig.externals = { ...adminConfig.externals, window: 'window', document: 'document' };
        adminConfig.name = 'admin';

        Encore.reset();

        return adminConfig;
    }

    static _getInternalWebpackConfig(rootDir) {
        this._prepareWebpackConfig(rootDir);
        // For a ready-to-use Stimulus bridge. Should be used only for sylius/sylius tests
        Encore
            .addEntry('admin-entry', path.resolve(__dirname, 'Resources/assets/app.js'))
            .enableStimulusBridge(path.resolve(__dirname, 'Resources/assets/controllers.json'));
        const adminConfig = Encore.getWebpackConfig();

        adminConfig.externals = { ...adminConfig.externals, window: 'window', document: 'document' };
        adminConfig.name = 'admin';

        Encore.reset();

        return adminConfig;
    }

    static _prepareWebpackConfig(rootDir) {
        Encore
            .setOutputPath('public/build/admin/')
            .setPublicPath('/build/admin')
            .addEntry('admin-product-entry', path.resolve(__dirname, 'Resources/assets/product-entrypoint.js'))
            .disableSingleRuntimeChunk()
            .cleanupOutputBeforeBuild()
            .enableSourceMaps(!Encore.isProduction())
            .enableVersioning(Encore.isProduction())
            .enableSassLoader((options) => {
                // eslint-disable-next-line no-param-reassign
                options.additionalData = `$rootDir: '${rootDir}';`;
            })
    }
}

module.exports = SyliusAdmin;
