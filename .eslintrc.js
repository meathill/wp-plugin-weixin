module.exports = {
  "extends": "google",
  "plugins": ["html"],
  "settings": {
    "html/html-extensions": [".html", ".vue"],
  },
  "parserOptions": {
    "ecmaVersion": 6,
    "sourceType": "module",
    "ecmaFeatures": {
      "jsx": false,
      "modules": true,
      "experimentalObjectRestSpread": true
    },
  },
};