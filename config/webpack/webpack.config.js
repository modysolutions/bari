const path = require( 'path' );
const defaults = require( '@wordpress/scripts/config/webpack.config.js' );

const THEME_DIR = path.resolve( process.cwd(), 'app/web/themes/theme' );

module.exports = {
    ...defaults,

    entry: {
        app:    path.resolve( process.cwd(), 'src', 'app.js' ),
        editor: path.resolve( process.cwd(), 'src', 'editor.js' ),
    },

    output: {
        ...defaults.output,
        path: path.resolve( THEME_DIR, 'dist' ),
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
                        presets: [ '@babel/preset-react' ],
                    },
                },
            },
        ],
    },
};