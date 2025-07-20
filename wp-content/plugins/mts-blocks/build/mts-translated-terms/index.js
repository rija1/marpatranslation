/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/mts-translated-terms/edit.js":
/*!******************************************!*\
  !*** ./src/mts-translated-terms/edit.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editor.scss */ "./src/mts-translated-terms/editor.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__);







function Edit({
  attributes,
  setAttributes
}) {
  const [terms, setTerms] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)([]);
  const [filteredTerms, setFilteredTerms] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)([]);
  const [searchTerm, setSearchTerm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  const blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)();
  const fetchTerms = async () => {
    try {
      const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
        path: '/pods/v1/tibetan-terms',
        method: 'GET'
      });
      setTerms(response);
      setFilteredTerms(response);
    } catch (error) {
      console.error('Error fetching terms:', error);
    }
  };
  const handleSearch = value => {
    setSearchTerm(value);
    const filtered = terms.filter(term => term.tibetan.toLowerCase().includes(value.toLowerCase()) || term.english.toLowerCase().includes(value.toLowerCase()));
    setFilteredTerms(filtered);
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    fetchTerms();
  }, []);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
    ...blockProps,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Search Tibetan Terms', 'mts-tibetan-terms'),
      value: searchTerm,
      onChange: handleSearch,
      placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Type to filter terms...', 'mts-tibetan-terms')
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
      children: filteredTerms.map(term => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("strong", {
          children: term.tibetan
        }), " - ", term.english]
      }, term.id))
    })]
  });
}

/***/ }),

/***/ "./src/mts-translated-terms/editor.scss":
/*!**********************************************!*\
  !*** ./src/mts-translated-terms/editor.scss ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/mts-translated-terms/style.scss":
/*!*********************************************!*\
  !*** ./src/mts-translated-terms/style.scss ***!
  \*********************************************/
/***/ (() => {

throw new Error("Module build failed (from ./node_modules/mini-css-extract-plugin/dist/loader.js):\nHookWebpackError: Module build failed (from ./node_modules/sass-loader/dist/cjs.js):\nCan't find stylesheet to import.\n\u001b[34m  ╷\u001b[0m\n\u001b[34m1 │\u001b[0m @import \u001b[31m'../shared/base-styles'\u001b[0m;\n\u001b[34m  │\u001b[0m \u001b[31m        ^^^^^^^^^^^^^^^^^^^^^^^\u001b[0m\n\u001b[34m  ╵\u001b[0m\n  src/mts-translated-terms/style.scss 1:9  root stylesheet\n    at tryRunOrWebpackError (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/HookWebpackError.js:86:9)\n    at __webpack_require_module__ (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5299:12)\n    at __webpack_require__ (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5256:18)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5328:20\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3485:9)\n    at done (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3527:9)\n    at Hook.eval [as callAsync] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:33:10), <anonymous>:15:1)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5234:43\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3482:9)\n    at timesSync (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:2297:7)\n    at Object.eachLimit (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3463:5)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5196:16\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3485:9)\n    at timesSync (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:2297:7)\n    at Object.eachLimit (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3463:5)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5164:15\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3485:9)\n    at done (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3527:9)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5110:8\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:3531:6\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/HookWebpackError.js:67:2\n    at Hook.eval [as callAsync] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:33:10), <anonymous>:15:1)\n    at Cache.store (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:111:20)\n    at ItemCacheFacade.store (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/CacheFacade.js:141:15)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:3530:11\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:95:34\n    at Array.<anonymous> (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/cache/MemoryCachePlugin.js:45:13)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:95:19\n    at Hook.eval [as callAsync] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:33:10), <anonymous>:19:1)\n    at Cache.get (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:79:18)\n    at ItemCacheFacade.get (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/CacheFacade.js:115:15)\n    at Compilation._codeGenerationModule (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:3498:9)\n    at codeGen (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5098:11)\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3482:9)\n    at timesSync (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:2297:7)\n    at Object.eachLimit (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3463:5)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5128:14\n    at processQueue (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/util/processAsyncTree.js:61:4)\n    at process.processTicksAndRejections (node:internal/process/task_queues:85:11)\n-- inner error --\nError: Module build failed (from ./node_modules/sass-loader/dist/cjs.js):\nCan't find stylesheet to import.\n\u001b[34m  ╷\u001b[0m\n\u001b[34m1 │\u001b[0m @import \u001b[31m'../shared/base-styles'\u001b[0m;\n\u001b[34m  │\u001b[0m \u001b[31m        ^^^^^^^^^^^^^^^^^^^^^^^\u001b[0m\n\u001b[34m  ╵\u001b[0m\n  src/mts-translated-terms/style.scss 1:9  root stylesheet\n    at Object.<anonymous> (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[4].use[1]!/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[4].use[2]!/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[4].use[3]!/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/src/mts-translated-terms/style.scss:1:7)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/javascript/JavascriptModulesPlugin.js:494:10\n    at Hook.eval [as call] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:19:10), <anonymous>:7:1)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5301:39\n    at tryRunOrWebpackError (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/HookWebpackError.js:81:7)\n    at __webpack_require_module__ (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5299:12)\n    at __webpack_require__ (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5256:18)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5328:20\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3485:9)\n    at done (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3527:9)\n    at Hook.eval [as callAsync] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:33:10), <anonymous>:15:1)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5234:43\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3482:9)\n    at timesSync (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:2297:7)\n    at Object.eachLimit (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3463:5)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5196:16\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3485:9)\n    at timesSync (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:2297:7)\n    at Object.eachLimit (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3463:5)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5164:15\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3485:9)\n    at done (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3527:9)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5110:8\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:3531:6\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/HookWebpackError.js:67:2\n    at Hook.eval [as callAsync] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:33:10), <anonymous>:15:1)\n    at Cache.store (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:111:20)\n    at ItemCacheFacade.store (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/CacheFacade.js:141:15)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:3530:11\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:95:34\n    at Array.<anonymous> (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/cache/MemoryCachePlugin.js:45:13)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:95:19\n    at Hook.eval [as callAsync] (eval at create (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/tapable/lib/HookCodeFactory.js:33:10), <anonymous>:19:1)\n    at Cache.get (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Cache.js:79:18)\n    at ItemCacheFacade.get (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/CacheFacade.js:115:15)\n    at Compilation._codeGenerationModule (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:3498:9)\n    at codeGen (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5098:11)\n    at symbolIterator (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3482:9)\n    at timesSync (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:2297:7)\n    at Object.eachLimit (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/neo-async/async.js:3463:5)\n    at /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/Compilation.js:5128:14\n    at processQueue (/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/webpack/lib/util/processAsyncTree.js:61:4)\n    at process.processTicksAndRejections (node:internal/process/task_queues:85:11)\n\nGenerated code for /Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[4].use[1]!/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[4].use[2]!/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/node_modules/sass-loader/dist/cjs.js??ruleSet[1].rules[4].use[3]!/Users/reedz/Local Sites/marpatranslation/app/public/wp-content/plugins/mts-blocks/src/mts-translated-terms/style.scss\n1 | throw new Error(\"Module build failed (from ./node_modules/sass-loader/dist/cjs.js):\\nCan't find stylesheet to import.\\n\\u001b[34m  ╷\\u001b[0m\\n\\u001b[34m1 │\\u001b[0m @import \\u001b[31m'../shared/base-styles'\\u001b[0m;\\n\\u001b[34m  │\\u001b[0m \\u001b[31m        ^^^^^^^^^^^^^^^^^^^^^^^\\u001b[0m\\n\\u001b[34m  ╵\\u001b[0m\\n  src/mts-translated-terms/style.scss 1:9  root stylesheet\");");

/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

"use strict";
module.exports = window["ReactJSXRuntime"];

/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./src/mts-translated-terms/block.json":
/*!*********************************************!*\
  !*** ./src/mts-translated-terms/block.json ***!
  \*********************************************/
/***/ ((module) => {

"use strict";
module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"create-block/mts-translated-terms","version":"0.1.0","title":"MTS Translated Terms","category":"widgets","icon":"smiley","description":"Example block scaffolded with Create Block tool.","example":{},"supports":{"html":false},"textdomain":"mts-translated-terms","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php","viewScript":"file:./view.js"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!*******************************************!*\
  !*** ./src/mts-translated-terms/index.js ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/mts-translated-terms/style.scss");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_style_scss__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/mts-translated-terms/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./src/mts-translated-terms/block.json");




(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_3__.name, {
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  attributes: {
    searchTerm: {
      type: 'string',
      default: ''
    }
  }
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map