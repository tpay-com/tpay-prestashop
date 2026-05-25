/**
* @author Krajowy Integrator Płatności S.A.
* @copyright Krajowy Integrator Płatności S.A.
* @license MIT
* 
* Copyright (c) 2026 Krajowy Integrator Płatności S.A.
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/
const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require("terser-webpack-plugin");

let config = {
	// devtool: "source-map",
	target: ["web", "es5"],
	entry: {
		main: [
			'./js/main.ts',
			'./scss/main.scss',
		],
	},
	output: {
		path: path.resolve(__dirname, '../views/js'),
		filename: '[name].min.js',
	},
	module: {
		rules: [
			// {
			// 	test: /\.ts?$/,
			// 	exclude: /node_modules/,
			// 	use: 'ts-loader',
			// },

			{
				test: /\.ts?$/,
				loader: 'esbuild-loader',
				options: {
					loader: 'ts',
					target: 'es2015'
				}
			},

			// {
			// 	test: /\.m?js$/,
			// 	exclude: /node_modules/,
			// 	use: {
			// 		loader: 'babel-loader',
			// 		options: {
			// 			presets: [
			// 				['@babel/preset-env', {targets: "ie 11"}]
			// 			]
			// 		}
			// 	}
			// },


			{
				test: /\.js$/,
				loader: 'esbuild-loader',
				options: {
					loader: 'js',
					target: 'es2015'
				}
			},



			// {
			// 	test: /\.ts?$/,
			// 	exclude: /node_modules/,
			// 	use: 'ts-loader',
			// },
			// {
			// 	test: /\.m?js$/,
			// 	exclude: /node_modules/,
			// 	use: {
			// 		loader: 'babel-loader',
			// 		options: {
			// 			presets: [
			// 				['@babel/preset-env', {targets: "ie 11"}]
			// 			]
			// 		}
			// 	}
			// },
			{
				test: /\.js/,
				loader: 'esbuild-loader',
			},
			{
				test: /\.scss$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader',
				],
			},
			{
				test: /.(png|woff(2)?|eot|otf|ttf|svg|gif)(\?[a-z0-9=\.]+)?$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '../css/[hash].[ext]',
						},
					},
				],
			},
			{
				test: /\.css$/,
				use: [MiniCssExtractPlugin.loader, 'style-loader', 'css-loader'],
			},
		],
	},
	resolve: {
		extensions: ['.ts', '.tsx', '.js']
	},
	plugins: [
		new MiniCssExtractPlugin({filename: path.join('..', 'css', '[name].css')}),
		// new webpack.BannerPlugin({
		//  banner: METADATA,
		//  raw: true,
		//  entryOnly: true,
		// }),
	]
};

if (process.env.NODE_ENV === 'production') {
	config.optimization = {
		minimizer: [
			new TerserPlugin(

					{
						// minify: TerserPlugin.uglifyJsMinify,
						terserOptions: {
							// sourceMap: true,
							format: {
								comments: false,
							},
						},
						extractComments: "all",



				//
				// sourceMap: false,
				// extractComments: false,
				// uglifyOptions: {
				// 	compress: {
				// 		sequences: true,
				// 		conditionals: true,
				// 		booleans: true,
				// 		if_return: true,
				// 		join_vars: true,
				// 		drop_console: true,
				// 	},
				// 	output: {
				// 		beautify: false,
				// 		comments: false,
				// 		// comments: 'some',
				// 		// preamble: METADATA,
				// 	},
				// 	mangle: { // see https://github.com/mishoo/UglifyJS2#mangle-options
				// 		keep_fnames: false,
				// 		toplevel: true,
				// 	},
				// }
			}
			)
		]
	}
} else {
	config.optimization = {
		minimizer: [
			new TerserPlugin({
				terserOptions: {
					format: {
						comments: false,
					},
				},
				extractComments: "all",

			})
		]
	}
}
//
config.mode = 'development';
// config.mode = 'production';

module.exports = config;
