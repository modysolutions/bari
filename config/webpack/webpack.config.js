const path = require('path');
const glob = require('glob');
const defaults = require('@wordpress/scripts/config/webpack.config.js');

// Function to get entries for plugins
const getPluginEntries = () => {
    const entries = {};
    const pluginDirs = glob.sync('./src/plugins/*/');
    pluginDirs.forEach(dir => {
        const pluginName = path.basename(dir);
        entries[pluginName] = path.resolve(process.cwd(), dir, 'app.js');
    });
    return entries;
};

module.exports = {
    ...defaults,
    entry: {
        theme: path.resolve(process.cwd(), 'src', 'theme', 'app.js'),
        ...getPluginEntries(),
    },
    module: {
        ...defaults.module,
        rules: [
            ...defaults.module.rules,
            {
                test: /\.(png|svg|jpg|jpeg|gif)$/i,
                type: 'asset/resource',
            },
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-react'],
                    },
                },
            },
        ],
    },
};