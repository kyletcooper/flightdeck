const path = require('path');

module.exports = {
    entry: './admin/src/index.js',

    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'admin/dist'),
        clean: true,
    },

    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: ['babel-loader']
            },
            {
                test: /\.css$/i,
                use: ['style-loader', 'css-loader', 'postcss-loader'],
            },
            {
                test: /\.(png|svg|jpg|jpeg|gif)$/i,
                type: 'asset/resource',
            },
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/i,
                type: 'asset/resource',
                use: ['url-loader'],
            },
        ],
    },
};