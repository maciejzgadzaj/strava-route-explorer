"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["immersive"],{

/***/ "./assets/js/immersive.js":
/*!********************************!*\
  !*** ./assets/js/immersive.js ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");
/* harmony import */ var core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_for_each_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _scss_immersive_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../scss/immersive.scss */ "./assets/scss/immersive.scss");
/* harmony import */ var _scss_immersive_mobile_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../scss/immersive-mobile.scss */ "./assets/scss/immersive-mobile.scss");




$(window).scroll(function () {
  $(".arrow").css("opacity", 1 - $(window).scrollTop() / 250);
});

// https://css-tricks.com/snippets/javascript/loop-queryselectorall-matches/
function loadAllProgressiveImages() {
  var progressiveBgImages = document.querySelectorAll(".progressive-bg-image");
  [].forEach.call(progressiveBgImages, function (progressiveBgImage) {
    var imgLarge = new Image();
    imgLarge.src = progressiveBgImage.dataset.large;
    imgLarge.onload = function () {
      progressiveBgImage.style.backgroundImage = "url(" + progressiveBgImage.dataset.large + ")";
      progressiveBgImage.classList.remove('small');
    };
  });
}
$(document).ready(function () {
  loadAllProgressiveImages();
});

/***/ }),

/***/ "./assets/scss/immersive-mobile.scss":
/*!*******************************************!*\
  !*** ./assets/scss/immersive-mobile.scss ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/scss/immersive.scss":
/*!************************************!*\
  !*** ./assets/scss/immersive.scss ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./node_modules/core-js/internals/array-for-each.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/array-for-each.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {


var $forEach = (__webpack_require__(/*! ../internals/array-iteration */ "./node_modules/core-js/internals/array-iteration.js").forEach);
var arrayMethodIsStrict = __webpack_require__(/*! ../internals/array-method-is-strict */ "./node_modules/core-js/internals/array-method-is-strict.js");

var STRICT_METHOD = arrayMethodIsStrict('forEach');

// `Array.prototype.forEach` method implementation
// https://tc39.es/ecma262/#sec-array.prototype.foreach
module.exports = !STRICT_METHOD ? function forEach(callbackfn /* , thisArg */) {
  return $forEach(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
// eslint-disable-next-line es/no-array-prototype-foreach -- safe
} : [].forEach;


/***/ }),

/***/ "./node_modules/core-js/internals/array-method-is-strict.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/array-method-is-strict.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {


var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

module.exports = function (METHOD_NAME, argument) {
  var method = [][METHOD_NAME];
  return !!method && fails(function () {
    // eslint-disable-next-line no-useless-call -- required for testing
    method.call(null, argument || function () { return 1; }, 1);
  });
};


/***/ }),

/***/ "./node_modules/core-js/modules/es.array.for-each.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/modules/es.array.for-each.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {


var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var forEach = __webpack_require__(/*! ../internals/array-for-each */ "./node_modules/core-js/internals/array-for-each.js");

// `Array.prototype.forEach` method
// https://tc39.es/ecma262/#sec-array.prototype.foreach
// eslint-disable-next-line es/no-array-prototype-foreach -- safe
$({ target: 'Array', proto: true, forced: [].forEach !== forEach }, {
  forEach: forEach
});


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_internals_array-iteration_js-node_modules_core-js_internals_expo-9e55a2"], () => (__webpack_exec__("./assets/js/immersive.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW1tZXJzaXZlLmpzIiwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFnQztBQUNPO0FBRXZDQSxDQUFDLENBQUNDLE1BQU0sQ0FBQyxDQUFDQyxNQUFNLENBQUMsWUFBVTtFQUN2QkYsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDRyxHQUFHLENBQUMsU0FBUyxFQUFFLENBQUMsR0FBR0gsQ0FBQyxDQUFDQyxNQUFNLENBQUMsQ0FBQ0csU0FBUyxDQUFDLENBQUMsR0FBRyxHQUFHLENBQUM7QUFDL0QsQ0FBQyxDQUFDOztBQUVGO0FBQ0EsU0FBU0Msd0JBQXdCQSxDQUFBLEVBQUc7RUFDaEMsSUFBSUMsbUJBQW1CLEdBQUdDLFFBQVEsQ0FBQ0MsZ0JBQWdCLENBQUMsdUJBQXVCLENBQUM7RUFDNUUsRUFBRSxDQUFDQyxPQUFPLENBQUNDLElBQUksQ0FBQ0osbUJBQW1CLEVBQUUsVUFBU0ssa0JBQWtCLEVBQUU7SUFDOUQsSUFBSUMsUUFBUSxHQUFHLElBQUlDLEtBQUssQ0FBQyxDQUFDO0lBQzFCRCxRQUFRLENBQUNFLEdBQUcsR0FBR0gsa0JBQWtCLENBQUNJLE9BQU8sQ0FBQ0MsS0FBSztJQUMvQ0osUUFBUSxDQUFDSyxNQUFNLEdBQUcsWUFBWTtNQUMxQk4sa0JBQWtCLENBQUNPLEtBQUssQ0FBQ0MsZUFBZSxHQUFHLE1BQU0sR0FBQ1Isa0JBQWtCLENBQUNJLE9BQU8sQ0FBQ0MsS0FBSyxHQUFDLEdBQUc7TUFDdEZMLGtCQUFrQixDQUFDUyxTQUFTLENBQUNDLE1BQU0sQ0FBQyxPQUFPLENBQUM7SUFDaEQsQ0FBQztFQUNMLENBQUMsQ0FBQztBQUNOO0FBRUFyQixDQUFDLENBQUNPLFFBQVEsQ0FBQyxDQUFDZSxLQUFLLENBQUMsWUFBVTtFQUN4QmpCLHdCQUF3QixDQUFDLENBQUM7QUFDOUIsQ0FBQyxDQUFDOzs7Ozs7Ozs7OztBQ3RCRjs7Ozs7Ozs7Ozs7O0FDQUE7Ozs7Ozs7Ozs7O0FDQWE7QUFDYixlQUFlLHdIQUErQztBQUM5RCwwQkFBMEIsbUJBQU8sQ0FBQyx1R0FBcUM7O0FBRXZFOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxFQUFFOzs7Ozs7Ozs7OztBQ1hXO0FBQ2IsWUFBWSxtQkFBTyxDQUFDLHFFQUFvQjs7QUFFeEM7QUFDQTtBQUNBO0FBQ0E7QUFDQSxnREFBZ0QsV0FBVztBQUMzRCxHQUFHO0FBQ0g7Ozs7Ozs7Ozs7O0FDVGE7QUFDYixRQUFRLG1CQUFPLENBQUMsdUVBQXFCO0FBQ3JDLGNBQWMsbUJBQU8sQ0FBQyx1RkFBNkI7O0FBRW5EO0FBQ0E7QUFDQTtBQUNBLElBQUksOERBQThEO0FBQ2xFO0FBQ0EsQ0FBQyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Fzc2V0cy9qcy9pbW1lcnNpdmUuanMiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL3Njc3MvaW1tZXJzaXZlLW1vYmlsZS5zY3NzP2IwOGQiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL3Njc3MvaW1tZXJzaXZlLnNjc3M/ZTY4NyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvYXJyYXktZm9yLWVhY2guanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvaW50ZXJuYWxzL2FycmF5LW1ldGhvZC1pcy1zdHJpY3QuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvbW9kdWxlcy9lcy5hcnJheS5mb3ItZWFjaC5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgJy4uL3Njc3MvaW1tZXJzaXZlLnNjc3MnO1xuaW1wb3J0ICcuLi9zY3NzL2ltbWVyc2l2ZS1tb2JpbGUuc2Nzcyc7XG5cbiQod2luZG93KS5zY3JvbGwoZnVuY3Rpb24oKXtcbiAgICAkKFwiLmFycm93XCIpLmNzcyhcIm9wYWNpdHlcIiwgMSAtICQod2luZG93KS5zY3JvbGxUb3AoKSAvIDI1MCk7XG59KTtcblxuLy8gaHR0cHM6Ly9jc3MtdHJpY2tzLmNvbS9zbmlwcGV0cy9qYXZhc2NyaXB0L2xvb3AtcXVlcnlzZWxlY3RvcmFsbC1tYXRjaGVzL1xuZnVuY3Rpb24gbG9hZEFsbFByb2dyZXNzaXZlSW1hZ2VzKCkge1xuICAgIHZhciBwcm9ncmVzc2l2ZUJnSW1hZ2VzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIi5wcm9ncmVzc2l2ZS1iZy1pbWFnZVwiKTtcbiAgICBbXS5mb3JFYWNoLmNhbGwocHJvZ3Jlc3NpdmVCZ0ltYWdlcywgZnVuY3Rpb24ocHJvZ3Jlc3NpdmVCZ0ltYWdlKSB7XG4gICAgICAgIHZhciBpbWdMYXJnZSA9IG5ldyBJbWFnZSgpO1xuICAgICAgICBpbWdMYXJnZS5zcmMgPSBwcm9ncmVzc2l2ZUJnSW1hZ2UuZGF0YXNldC5sYXJnZTtcbiAgICAgICAgaW1nTGFyZ2Uub25sb2FkID0gZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgcHJvZ3Jlc3NpdmVCZ0ltYWdlLnN0eWxlLmJhY2tncm91bmRJbWFnZSA9IFwidXJsKFwiK3Byb2dyZXNzaXZlQmdJbWFnZS5kYXRhc2V0LmxhcmdlK1wiKVwiO1xuICAgICAgICAgICAgcHJvZ3Jlc3NpdmVCZ0ltYWdlLmNsYXNzTGlzdC5yZW1vdmUoJ3NtYWxsJyk7XG4gICAgICAgIH07XG4gICAgfSk7XG59XG5cbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XG4gICAgbG9hZEFsbFByb2dyZXNzaXZlSW1hZ2VzKCk7XG59KTtcbiIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyIsIid1c2Ugc3RyaWN0JztcbnZhciAkZm9yRWFjaCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9hcnJheS1pdGVyYXRpb24nKS5mb3JFYWNoO1xudmFyIGFycmF5TWV0aG9kSXNTdHJpY3QgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvYXJyYXktbWV0aG9kLWlzLXN0cmljdCcpO1xuXG52YXIgU1RSSUNUX01FVEhPRCA9IGFycmF5TWV0aG9kSXNTdHJpY3QoJ2ZvckVhY2gnKTtcblxuLy8gYEFycmF5LnByb3RvdHlwZS5mb3JFYWNoYCBtZXRob2QgaW1wbGVtZW50YXRpb25cbi8vIGh0dHBzOi8vdGMzOS5lcy9lY21hMjYyLyNzZWMtYXJyYXkucHJvdG90eXBlLmZvcmVhY2hcbm1vZHVsZS5leHBvcnRzID0gIVNUUklDVF9NRVRIT0QgPyBmdW5jdGlvbiBmb3JFYWNoKGNhbGxiYWNrZm4gLyogLCB0aGlzQXJnICovKSB7XG4gIHJldHVybiAkZm9yRWFjaCh0aGlzLCBjYWxsYmFja2ZuLCBhcmd1bWVudHMubGVuZ3RoID4gMSA/IGFyZ3VtZW50c1sxXSA6IHVuZGVmaW5lZCk7XG4vLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgZXMvbm8tYXJyYXktcHJvdG90eXBlLWZvcmVhY2ggLS0gc2FmZVxufSA6IFtdLmZvckVhY2g7XG4iLCIndXNlIHN0cmljdCc7XG52YXIgZmFpbHMgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZmFpbHMnKTtcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbiAoTUVUSE9EX05BTUUsIGFyZ3VtZW50KSB7XG4gIHZhciBtZXRob2QgPSBbXVtNRVRIT0RfTkFNRV07XG4gIHJldHVybiAhIW1ldGhvZCAmJiBmYWlscyhmdW5jdGlvbiAoKSB7XG4gICAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXVzZWxlc3MtY2FsbCAtLSByZXF1aXJlZCBmb3IgdGVzdGluZ1xuICAgIG1ldGhvZC5jYWxsKG51bGwsIGFyZ3VtZW50IHx8IGZ1bmN0aW9uICgpIHsgcmV0dXJuIDE7IH0sIDEpO1xuICB9KTtcbn07XG4iLCIndXNlIHN0cmljdCc7XG52YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciBmb3JFYWNoID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2FycmF5LWZvci1lYWNoJyk7XG5cbi8vIGBBcnJheS5wcm90b3R5cGUuZm9yRWFjaGAgbWV0aG9kXG4vLyBodHRwczovL3RjMzkuZXMvZWNtYTI2Mi8jc2VjLWFycmF5LnByb3RvdHlwZS5mb3JlYWNoXG4vLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgZXMvbm8tYXJyYXktcHJvdG90eXBlLWZvcmVhY2ggLS0gc2FmZVxuJCh7IHRhcmdldDogJ0FycmF5JywgcHJvdG86IHRydWUsIGZvcmNlZDogW10uZm9yRWFjaCAhPT0gZm9yRWFjaCB9LCB7XG4gIGZvckVhY2g6IGZvckVhY2hcbn0pO1xuIl0sIm5hbWVzIjpbIiQiLCJ3aW5kb3ciLCJzY3JvbGwiLCJjc3MiLCJzY3JvbGxUb3AiLCJsb2FkQWxsUHJvZ3Jlc3NpdmVJbWFnZXMiLCJwcm9ncmVzc2l2ZUJnSW1hZ2VzIiwiZG9jdW1lbnQiLCJxdWVyeVNlbGVjdG9yQWxsIiwiZm9yRWFjaCIsImNhbGwiLCJwcm9ncmVzc2l2ZUJnSW1hZ2UiLCJpbWdMYXJnZSIsIkltYWdlIiwic3JjIiwiZGF0YXNldCIsImxhcmdlIiwib25sb2FkIiwic3R5bGUiLCJiYWNrZ3JvdW5kSW1hZ2UiLCJjbGFzc0xpc3QiLCJyZW1vdmUiLCJyZWFkeSJdLCJzb3VyY2VSb290IjoiIn0=