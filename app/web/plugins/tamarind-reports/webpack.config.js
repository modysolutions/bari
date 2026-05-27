const path = require( 'path' );
const defaults = require( '@wordpress/scripts/config/webpack.config.js' );

module.exports = () => ( {
    ...defaults,
    entry: {
        tamarind_reports: path.resolve( process.cwd(), 'src/js', 'main.js' ),
    },
    output: {
        filename: '[name].js',
        path: path.resolve( process.cwd(), 'dist' ),
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
} );
