const path = require("path");

module.exports = {
  entry: {
    main: "./src/js/main.js",
    customizer: "./assets/js/customizer.js",
  },
  output: {
    path: path.resolve(__dirname, "assets/js"),
    filename: "[name].min.js",
    clean: false,
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ["@babel/preset-env"],
          },
        },
      },
    ],
  },
  optimization: {
    minimize: true,
  },
  resolve: {
    extensions: [".js"],
  },
};
