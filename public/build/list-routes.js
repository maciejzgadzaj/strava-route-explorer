"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["list-routes"],{

/***/ "./assets/js/list-routes.js":
/*!**********************************!*\
  !*** ./assets/js/list-routes.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var core_js_modules_es_array_find_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.find.js */ "./node_modules/core-js/modules/es.array.find.js");
/* harmony import */ var core_js_modules_es_array_find_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_find_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_function_name_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.function.name.js */ "./node_modules/core-js/modules/es.function.name.js");
/* harmony import */ var core_js_modules_es_function_name_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_function_name_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es.array.concat.js */ "./node_modules/core-js/modules/es.array.concat.js");
/* harmony import */ var core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_concat_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _scss_list_routes_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../scss/list-routes.scss */ "./assets/scss/list-routes.scss");
/* harmony import */ var _scss_list_routes_mobile_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../scss/list-routes-mobile.scss */ "./assets/scss/list-routes-mobile.scss");






function setSelectClass(element) {
  if (element.val() == 0) {
    element.removeClass("not_empty");
  } else {
    element.addClass("not_empty");
  }
}
function setAdvancedFiltersToggleLabel(element) {
  if (element.is(":visible")) {
    $(".advanced-filters-toggle a").addClass('active');
  } else {
    $(".advanced-filters-toggle a").removeClass('active');
  }
}
$(document).ready(function () {
  $("#filter_type, #filter_start_dist, #filter_end_dist").each(function () {
    setSelectClass($(this));
  }).on("change", function () {
    setSelectClass($(this));
  });
  setAdvancedFiltersToggleLabel($(".advanced_filters"));
  $(".filters_toggle").on("click", function () {
    $(".section_filters").slideToggle("fast", function () {
      if ($(this).is(":visible")) {
        $(".filters_toggle .filter_values").hide();
        $(".filters_toggle .label").text('hide filters');
        $(".filters_toggle").addClass("open");
      } else {
        $(".filters_toggle .label").text('filter results');
        $(".filters_toggle .filter_values").show();
        $(".filters_toggle").removeClass("open");
      }
    });
  });
  if ($("#filter_athlete").val()) {
    $(".starred, .private").show();
  }
  $("#filter_athlete").keyup(function () {
    if ($(this).val()) {
      $(".starred, .private").show();
    } else {
      $("#filter_starred, #filter_private").prop('checked', false);
      $(".starred, .private").hide();
    }
  });
  $("#filter_starred").change(function () {
    if (this.checked) {
      $("#filter_private").prop('checked', false);
    }
  });
  $("#filter_private").change(function () {
    if (this.checked) {
      $("#filter_starred").prop('checked', false);
    }
  });
  $(".advanced-filters-toggle").on("click", function () {
    $(".advanced_filters").slideToggle("fast", function () {
      setAdvancedFiltersToggleLabel($(this));
    });
  });
  // http://api.jqueryui.com/autocomplete/
  // https://jqueryui.com/autocomplete/#custom-data
  $('#filter_start').autocomplete({
    source: "/routes/autocomplete/location",
    minLength: 3,
    delay: 250,
    appendTo: $(this).attr("id"),
    change: function change(event, ui) {
      var name = $(this).attr("id");
      if (ui.item === null) {
        $("#" + name + "_latlon").val(null);
      }
    },
    search: function search(event, ui) {
      var name = $(this).attr("id");
      $("#" + name).parent().find('.geolocate').attr('src', "images/spinner.gif").addClass("clicked");
    },
    response: function response(event, ui) {
      $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
    },
    select: function select(event, ui) {
      var name = $(this).attr("id");
      $("#" + name).val(ui.item.name + ", " + ui.item.city + ", " + ui.item.country);
      $("#" + name + "_latlon").val(ui.item.latitude + "," + ui.item.longitude);
      return false;
    }
  }).autocomplete("instance")._renderItem = function (ul, item) {
    var address = item.country;
    if (item.city && item.city != item.country) {
      address = "".concat(item.city, ", ").concat(address);
    }
    return $("<li>").append("<div class=\"name\">".concat(item.name, "</div>")).append("<div class=\"details\"><span class=\"class\">".concat(item["class"], "</span>, <span class=\"address\">").concat(address, "</span></div>")).appendTo(ul);
  };
  $('#filter_end').autocomplete({
    source: "/routes/autocomplete/location",
    minLength: 3,
    delay: 250,
    appendTo: $(this).attr("id"),
    change: function change(event, ui) {
      var name = $(this).attr("id");
      if (ui.item === null) {
        $("#" + name + "_latlon").val(null);
      }
    },
    search: function search(event, ui) {
      var name = $(this).attr("id");
      $("#" + name).parent().find('.geolocate').attr('src', "images/spinner.gif").addClass("clicked");
    },
    response: function response(event, ui) {
      $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
    },
    select: function select(event, ui) {
      var name = $(this).attr("id");
      $("#" + name).val(ui.item.name + ", " + ui.item.city + ", " + ui.item.country);
      $("#" + name + "_latlon").val(ui.item.latitude + "," + ui.item.longitude);
      return false;
    }
  }).autocomplete("instance")._renderItem = function (ul, item) {
    var address = item.country;
    if (item.city && item.city != item.country) {
      address = "".concat(item.city, ", ").concat(address);
    }
    return $("<li>").append("<div class=\"name\">".concat(item.name, "</div>")).append("<div class=\"details\"><span class=\"class\">".concat(item["class"], "</span>, <span class=\"address\">").concat(address, "</span></div>")).appendTo(ul);
  };

  // Geolocation for route "Start" and "End" filters.
  if (navigator.geolocation) {
    $('.geolocate').attr('style', '');
  }
  $('.geolocate').on("click", function () {
    $(this).attr('src', "images/spinner.gif").addClass("clicked");
    var options = {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0
    };
    navigator.geolocation.getCurrentPosition(handlePosition, geolocationError, options);
  });
  function handlePosition(position, target) {
    $.ajax({
      type: "GET",
      url: "/routes/autocomplete/reverse?lat=" + position.coords.latitude + "&lon=" + position.coords.longitude,
      timeout: 5000,
      success: function success(data) {
        var target = $("img.geolocate.clicked").attr("data-target");
        $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
        $("#filter_" + target).val(data.name + ", " + data.city + ", " + data.country);
        $("#filter_" + target + "_latlon").val(data.latitude + "," + data.longitude);
      },
      error: function error(data) {
        $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
      }
    });
  }
  function geolocationError(err) {
    $("img.geolocate.clicked").attr("src", "images/icon-geolocate.svg").removeClass("clicked");
    console.warn(err);
  }
});

/***/ }),

/***/ "./assets/scss/list-routes-mobile.scss":
/*!*********************************************!*\
  !*** ./assets/scss/list-routes-mobile.scss ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/scss/list-routes.scss":
/*!**************************************!*\
  !*** ./assets/scss/list-routes.scss ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_internals_array-iteration_js-node_modules_core-js_internals_expo-9e55a2","vendors-node_modules_core-js_modules_es_array_concat_js-node_modules_core-js_modules_es_array-bc9c02"], () => (__webpack_exec__("./assets/js/list-routes.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QixDQUFDLENBQUN0QixXQUFXLENBQUMsU0FBUyxDQUFDO01BQzlGO0lBQ0osQ0FBQyxDQUFDO0VBQ047RUFFQSxTQUFTaUQsZ0JBQWdCQSxDQUFDVSxHQUFHLEVBQUU7SUFDM0J2RCxDQUFDLENBQUMsdUJBQXVCLENBQUMsQ0FBQ2tCLElBQUksQ0FBQyxLQUFLLEVBQUUsMkJBQTJCLENBQUMsQ0FBQ3RCLFdBQVcsQ0FBQyxTQUFTLENBQUM7SUFDMUY0RCxPQUFPLENBQUNDLElBQUksQ0FBQ0YsR0FBRyxDQUFDO0VBQ3JCO0FBQ0osQ0FBQyxDQUFDOzs7Ozs7Ozs7OztBQ3BMRjs7Ozs7Ozs7Ozs7O0FDQUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvbGlzdC1yb3V0ZXMuanMiLCJ3ZWJwYWNrOi8vLy4vYXNzZXRzL3Njc3MvbGlzdC1yb3V0ZXMtbW9iaWxlLnNjc3M/NDIyZiIsIndlYnBhY2s6Ly8vLi9hc3NldHMvc2Nzcy9saXN0LXJvdXRlcy5zY3NzPzQ5ZjYiXSwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0ICcuLi9zY3NzL2xpc3Qtcm91dGVzLnNjc3MnO1xuaW1wb3J0ICcuLi9zY3NzL2xpc3Qtcm91dGVzLW1vYmlsZS5zY3NzJztcblxuZnVuY3Rpb24gc2V0U2VsZWN0Q2xhc3MoZWxlbWVudCkge1xuICAgIGlmIChlbGVtZW50LnZhbCgpID09IDApIHtcbiAgICAgICAgZWxlbWVudC5yZW1vdmVDbGFzcyhcIm5vdF9lbXB0eVwiKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICBlbGVtZW50LmFkZENsYXNzKFwibm90X2VtcHR5XCIpO1xuICAgIH1cbn1cblxuZnVuY3Rpb24gc2V0QWR2YW5jZWRGaWx0ZXJzVG9nZ2xlTGFiZWwoZWxlbWVudCkge1xuICAgIGlmIChlbGVtZW50LmlzKFwiOnZpc2libGVcIikpIHtcbiAgICAgICAgJChcIi5hZHZhbmNlZC1maWx0ZXJzLXRvZ2dsZSBhXCIpLmFkZENsYXNzKCdhY3RpdmUnKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICAkKFwiLmFkdmFuY2VkLWZpbHRlcnMtdG9nZ2xlIGFcIikucmVtb3ZlQ2xhc3MoJ2FjdGl2ZScpO1xuICAgIH1cbn1cblxuJChkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24gKCkge1xuICAgICQoXCIjZmlsdGVyX3R5cGUsICNmaWx0ZXJfc3RhcnRfZGlzdCwgI2ZpbHRlcl9lbmRfZGlzdFwiKS5lYWNoKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgc2V0U2VsZWN0Q2xhc3MoJCh0aGlzKSk7XG4gICAgfSkub24oXCJjaGFuZ2VcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgICBzZXRTZWxlY3RDbGFzcygkKHRoaXMpKTtcbiAgICB9KTtcblxuICAgIHNldEFkdmFuY2VkRmlsdGVyc1RvZ2dsZUxhYmVsKCQoXCIuYWR2YW5jZWRfZmlsdGVyc1wiKSk7XG5cbiAgICAkKFwiLmZpbHRlcnNfdG9nZ2xlXCIpLm9uKFwiY2xpY2tcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgICAkKFwiLnNlY3Rpb25fZmlsdGVyc1wiKS5zbGlkZVRvZ2dsZShcImZhc3RcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgaWYgKCQodGhpcykuaXMoXCI6dmlzaWJsZVwiKSkge1xuICAgICAgICAgICAgICAgICQoXCIuZmlsdGVyc190b2dnbGUgLmZpbHRlcl92YWx1ZXNcIikuaGlkZSgpO1xuICAgICAgICAgICAgICAgICQoXCIuZmlsdGVyc190b2dnbGUgLmxhYmVsXCIpLnRleHQoJ2hpZGUgZmlsdGVycycpO1xuICAgICAgICAgICAgICAgICQoXCIuZmlsdGVyc190b2dnbGVcIikuYWRkQ2xhc3MoXCJvcGVuXCIpO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAkKFwiLmZpbHRlcnNfdG9nZ2xlIC5sYWJlbFwiKS50ZXh0KCdmaWx0ZXIgcmVzdWx0cycpO1xuICAgICAgICAgICAgICAgICQoXCIuZmlsdGVyc190b2dnbGUgLmZpbHRlcl92YWx1ZXNcIikuc2hvdygpO1xuICAgICAgICAgICAgICAgICQoXCIuZmlsdGVyc190b2dnbGVcIikucmVtb3ZlQ2xhc3MoXCJvcGVuXCIpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9KTtcblxuICAgIGlmICgkKFwiI2ZpbHRlcl9hdGhsZXRlXCIpLnZhbCgpKSB7XG4gICAgICAgICQoXCIuc3RhcnJlZCwgLnByaXZhdGVcIikuc2hvdygpO1xuICAgIH1cbiAgICAkKFwiI2ZpbHRlcl9hdGhsZXRlXCIpLmtleXVwKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgaWYgKCQodGhpcykudmFsKCkpIHtcbiAgICAgICAgICAgICQoXCIuc3RhcnJlZCwgLnByaXZhdGVcIikuc2hvdygpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgJChcIiNmaWx0ZXJfc3RhcnJlZCwgI2ZpbHRlcl9wcml2YXRlXCIpLnByb3AoJ2NoZWNrZWQnLCBmYWxzZSk7XG4gICAgICAgICAgICAkKFwiLnN0YXJyZWQsIC5wcml2YXRlXCIpLmhpZGUoKTtcbiAgICAgICAgfVxuICAgIH0pO1xuXG4gICAgJChcIiNmaWx0ZXJfc3RhcnJlZFwiKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAgICAgICBpZiAodGhpcy5jaGVja2VkKSB7XG4gICAgICAgICAgICAkKFwiI2ZpbHRlcl9wcml2YXRlXCIpLnByb3AoJ2NoZWNrZWQnLCBmYWxzZSk7XG4gICAgICAgIH1cbiAgICB9KTtcbiAgICAkKFwiI2ZpbHRlcl9wcml2YXRlXCIpLmNoYW5nZShmdW5jdGlvbiAoKSB7XG4gICAgICAgIGlmICh0aGlzLmNoZWNrZWQpIHtcbiAgICAgICAgICAgICQoXCIjZmlsdGVyX3N0YXJyZWRcIikucHJvcCgnY2hlY2tlZCcsIGZhbHNlKTtcbiAgICAgICAgfVxuICAgIH0pO1xuXG4gICAgJChcIi5hZHZhbmNlZC1maWx0ZXJzLXRvZ2dsZVwiKS5vbihcImNsaWNrXCIsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgJChcIi5hZHZhbmNlZF9maWx0ZXJzXCIpLnNsaWRlVG9nZ2xlKFwiZmFzdFwiLCBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBzZXRBZHZhbmNlZEZpbHRlcnNUb2dnbGVMYWJlbCgkKHRoaXMpKTtcbiAgICAgICAgfSk7XG4gICAgfSk7XG4gICAgLy8gaHR0cDovL2FwaS5qcXVlcnl1aS5jb20vYXV0b2NvbXBsZXRlL1xuICAgIC8vIGh0dHBzOi8vanF1ZXJ5dWkuY29tL2F1dG9jb21wbGV0ZS8jY3VzdG9tLWRhdGFcbiAgICAkKCcjZmlsdGVyX3N0YXJ0JylcbiAgICAgICAgLmF1dG9jb21wbGV0ZSh7XG4gICAgICAgICAgICBzb3VyY2U6IFwiL3JvdXRlcy9hdXRvY29tcGxldGUvbG9jYXRpb25cIixcbiAgICAgICAgICAgIG1pbkxlbmd0aDogMyxcbiAgICAgICAgICAgIGRlbGF5OiAyNTAsXG4gICAgICAgICAgICBhcHBlbmRUbzogJCh0aGlzKS5hdHRyKFwiaWRcIiksXG4gICAgICAgICAgICBjaGFuZ2U6IGZ1bmN0aW9uIChldmVudCwgdWkpIHtcbiAgICAgICAgICAgICAgICB2YXIgbmFtZSA9ICQodGhpcykuYXR0cihcImlkXCIpO1xuICAgICAgICAgICAgICAgIGlmICh1aS5pdGVtID09PSBudWxsKSB7XG4gICAgICAgICAgICAgICAgICAgICQoXCIjXCIgKyBuYW1lICsgXCJfbGF0bG9uXCIpLnZhbChudWxsKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgc2VhcmNoOiBmdW5jdGlvbiAoZXZlbnQsIHVpKSB7XG4gICAgICAgICAgICAgICAgdmFyIG5hbWUgPSAkKHRoaXMpLmF0dHIoXCJpZFwiKTtcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgbmFtZSkucGFyZW50KCkuZmluZCgnLmdlb2xvY2F0ZScpLmF0dHIoJ3NyYycsIFwiaW1hZ2VzL3NwaW5uZXIuZ2lmXCIpLmFkZENsYXNzKFwiY2xpY2tlZFwiKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICByZXNwb25zZTogZnVuY3Rpb24gKGV2ZW50LCB1aSkge1xuICAgICAgICAgICAgICAgICQoXCJpbWcuZ2VvbG9jYXRlLmNsaWNrZWRcIikuYXR0cihcInNyY1wiLCBcImltYWdlcy9pY29uLWdlb2xvY2F0ZS5zdmdcIikucmVtb3ZlQ2xhc3MoXCJjbGlja2VkXCIpO1xuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIHNlbGVjdDogZnVuY3Rpb24gKGV2ZW50LCB1aSkge1xuICAgICAgICAgICAgICAgIHZhciBuYW1lID0gJCh0aGlzKS5hdHRyKFwiaWRcIik7XG4gICAgICAgICAgICAgICAgJChcIiNcIiArIG5hbWUpLnZhbCh1aS5pdGVtLm5hbWUgKyBcIiwgXCIgKyB1aS5pdGVtLmNpdHkgKyBcIiwgXCIgKyB1aS5pdGVtLmNvdW50cnkpO1xuICAgICAgICAgICAgICAgICQoXCIjXCIgKyBuYW1lICsgXCJfbGF0bG9uXCIpLnZhbCh1aS5pdGVtLmxhdGl0dWRlICsgXCIsXCIgKyB1aS5pdGVtLmxvbmdpdHVkZSk7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KVxuICAgICAgICAuYXV0b2NvbXBsZXRlKFwiaW5zdGFuY2VcIikuX3JlbmRlckl0ZW0gPSBmdW5jdGlvbiAodWwsIGl0ZW0pIHtcbiAgICAgICAgICAgIGxldCBhZGRyZXNzID0gaXRlbS5jb3VudHJ5O1xuICAgICAgICAgICAgaWYgKGl0ZW0uY2l0eSAmJiBpdGVtLmNpdHkgIT0gaXRlbS5jb3VudHJ5KSB7XG4gICAgICAgICAgICAgICAgYWRkcmVzcyA9IGAke2l0ZW0uY2l0eX0sICR7YWRkcmVzc31gO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgcmV0dXJuICQoXCI8bGk+XCIpXG4gICAgICAgICAgICAgICAgLmFwcGVuZChgPGRpdiBjbGFzcz1cIm5hbWVcIj4ke2l0ZW0ubmFtZX08L2Rpdj5gKVxuICAgICAgICAgICAgICAgIC5hcHBlbmQoYDxkaXYgY2xhc3M9XCJkZXRhaWxzXCI+PHNwYW4gY2xhc3M9XCJjbGFzc1wiPiR7aXRlbS5jbGFzc308L3NwYW4+LCA8c3BhbiBjbGFzcz1cImFkZHJlc3NcIj4ke2FkZHJlc3N9PC9zcGFuPjwvZGl2PmApXG4gICAgICAgICAgICAgICAgLmFwcGVuZFRvKHVsKTtcbiAgICAgICAgfTtcbiAgICAkKCcjZmlsdGVyX2VuZCcpXG4gICAgICAgIC5hdXRvY29tcGxldGUoe1xuICAgICAgICAgICAgc291cmNlOiBcIi9yb3V0ZXMvYXV0b2NvbXBsZXRlL2xvY2F0aW9uXCIsXG4gICAgICAgICAgICBtaW5MZW5ndGg6IDMsXG4gICAgICAgICAgICBkZWxheTogMjUwLFxuICAgICAgICAgICAgYXBwZW5kVG86ICQodGhpcykuYXR0cihcImlkXCIpLFxuICAgICAgICAgICAgY2hhbmdlOiBmdW5jdGlvbiAoZXZlbnQsIHVpKSB7XG4gICAgICAgICAgICAgICAgdmFyIG5hbWUgPSAkKHRoaXMpLmF0dHIoXCJpZFwiKTtcbiAgICAgICAgICAgICAgICBpZiAodWkuaXRlbSA9PT0gbnVsbCkge1xuICAgICAgICAgICAgICAgICAgICAkKFwiI1wiICsgbmFtZSArIFwiX2xhdGxvblwiKS52YWwobnVsbCk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIHNlYXJjaDogZnVuY3Rpb24gKGV2ZW50LCB1aSkge1xuICAgICAgICAgICAgICAgIHZhciBuYW1lID0gJCh0aGlzKS5hdHRyKFwiaWRcIik7XG4gICAgICAgICAgICAgICAgJChcIiNcIiArIG5hbWUpLnBhcmVudCgpLmZpbmQoJy5nZW9sb2NhdGUnKS5hdHRyKCdzcmMnLCBcImltYWdlcy9zcGlubmVyLmdpZlwiKS5hZGRDbGFzcyhcImNsaWNrZWRcIik7XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgcmVzcG9uc2U6IGZ1bmN0aW9uIChldmVudCwgdWkpIHtcbiAgICAgICAgICAgICAgICAkKFwiaW1nLmdlb2xvY2F0ZS5jbGlja2VkXCIpLmF0dHIoXCJzcmNcIiwgXCJpbWFnZXMvaWNvbi1nZW9sb2NhdGUuc3ZnXCIpLnJlbW92ZUNsYXNzKFwiY2xpY2tlZFwiKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBzZWxlY3Q6IGZ1bmN0aW9uIChldmVudCwgdWkpIHtcbiAgICAgICAgICAgICAgICB2YXIgbmFtZSA9ICQodGhpcykuYXR0cihcImlkXCIpO1xuICAgICAgICAgICAgICAgICQoXCIjXCIgKyBuYW1lKS52YWwodWkuaXRlbS5uYW1lICsgXCIsIFwiICsgdWkuaXRlbS5jaXR5ICsgXCIsIFwiICsgdWkuaXRlbS5jb3VudHJ5KTtcbiAgICAgICAgICAgICAgICAkKFwiI1wiICsgbmFtZSArIFwiX2xhdGxvblwiKS52YWwodWkuaXRlbS5sYXRpdHVkZSArIFwiLFwiICsgdWkuaXRlbS5sb25naXR1ZGUpO1xuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSlcbiAgICAgICAgLmF1dG9jb21wbGV0ZShcImluc3RhbmNlXCIpLl9yZW5kZXJJdGVtID0gZnVuY3Rpb24gKHVsLCBpdGVtKSB7XG4gICAgICAgICAgICBsZXQgYWRkcmVzcyA9IGl0ZW0uY291bnRyeTtcbiAgICAgICAgICAgIGlmIChpdGVtLmNpdHkgJiYgaXRlbS5jaXR5ICE9IGl0ZW0uY291bnRyeSkge1xuICAgICAgICAgICAgICAgIGFkZHJlc3MgPSBgJHtpdGVtLmNpdHl9LCAke2FkZHJlc3N9YDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHJldHVybiAkKFwiPGxpPlwiKVxuICAgICAgICAgICAgICAgIC5hcHBlbmQoYDxkaXYgY2xhc3M9XCJuYW1lXCI+JHtpdGVtLm5hbWV9PC9kaXY+YClcbiAgICAgICAgICAgICAgICAuYXBwZW5kKGA8ZGl2IGNsYXNzPVwiZGV0YWlsc1wiPjxzcGFuIGNsYXNzPVwiY2xhc3NcIj4ke2l0ZW0uY2xhc3N9PC9zcGFuPiwgPHNwYW4gY2xhc3M9XCJhZGRyZXNzXCI+JHthZGRyZXNzfTwvc3Bhbj48L2Rpdj5gKVxuICAgICAgICAgICAgICAgIC5hcHBlbmRUbyh1bCk7XG4gICAgICAgIH07XG5cbiAgICAvLyBHZW9sb2NhdGlvbiBmb3Igcm91dGUgXCJTdGFydFwiIGFuZCBcIkVuZFwiIGZpbHRlcnMuXG4gICAgaWYgKG5hdmlnYXRvci5nZW9sb2NhdGlvbikge1xuICAgICAgICAkKCcuZ2VvbG9jYXRlJykuYXR0cignc3R5bGUnLCAnJyk7XG4gICAgfVxuICAgICQoJy5nZW9sb2NhdGUnKS5vbihcImNsaWNrXCIsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgJCh0aGlzKS5hdHRyKCdzcmMnLCBcImltYWdlcy9zcGlubmVyLmdpZlwiKS5hZGRDbGFzcyhcImNsaWNrZWRcIik7XG4gICAgICAgIHZhciBvcHRpb25zID0ge1xuICAgICAgICAgICAgZW5hYmxlSGlnaEFjY3VyYWN5OiB0cnVlLFxuICAgICAgICAgICAgdGltZW91dDogNTAwMCxcbiAgICAgICAgICAgIG1heGltdW1BZ2U6IDBcbiAgICAgICAgfTtcbiAgICAgICAgbmF2aWdhdG9yLmdlb2xvY2F0aW9uLmdldEN1cnJlbnRQb3NpdGlvbihoYW5kbGVQb3NpdGlvbiwgZ2VvbG9jYXRpb25FcnJvciwgb3B0aW9ucyk7XG4gICAgfSk7XG5cbiAgICBmdW5jdGlvbiBoYW5kbGVQb3NpdGlvbihwb3NpdGlvbiwgdGFyZ2V0KSB7XG4gICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICB0eXBlOiBcIkdFVFwiLFxuICAgICAgICAgICAgdXJsOiBcIi9yb3V0ZXMvYXV0b2NvbXBsZXRlL3JldmVyc2U/bGF0PVwiICsgcG9zaXRpb24uY29vcmRzLmxhdGl0dWRlICsgXCImbG9uPVwiICsgcG9zaXRpb24uY29vcmRzLmxvbmdpdHVkZSxcbiAgICAgICAgICAgIHRpbWVvdXQ6IDUwMDAsXG4gICAgICAgICAgICBzdWNjZXNzOiBmdW5jdGlvbiAoZGF0YSkge1xuICAgICAgICAgICAgICAgIHZhciB0YXJnZXQgPSAkKFwiaW1nLmdlb2xvY2F0ZS5jbGlja2VkXCIpLmF0dHIoXCJkYXRhLXRhcmdldFwiKTtcbiAgICAgICAgICAgICAgICAkKFwiaW1nLmdlb2xvY2F0ZS5jbGlja2VkXCIpLmF0dHIoXCJzcmNcIiwgXCJpbWFnZXMvaWNvbi1nZW9sb2NhdGUuc3ZnXCIpLnJlbW92ZUNsYXNzKFwiY2xpY2tlZFwiKTtcbiAgICAgICAgICAgICAgICAkKFwiI2ZpbHRlcl9cIiArIHRhcmdldCkudmFsKGRhdGEubmFtZSArIFwiLCBcIiArIGRhdGEuY2l0eSArIFwiLCBcIiArIGRhdGEuY291bnRyeSk7XG4gICAgICAgICAgICAgICAgJChcIiNmaWx0ZXJfXCIgKyB0YXJnZXQgKyBcIl9sYXRsb25cIikudmFsKGRhdGEubGF0aXR1ZGUgKyBcIixcIiArIGRhdGEubG9uZ2l0dWRlKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBlcnJvcjogZnVuY3Rpb24gKGRhdGEpIHtcbiAgICAgICAgICAgICAgICAkKFwiaW1nLmdlb2xvY2F0ZS5jbGlja2VkXCIpLmF0dHIoXCJzcmNcIiwgXCJpbWFnZXMvaWNvbi1nZW9sb2NhdGUuc3ZnXCIpLnJlbW92ZUNsYXNzKFwiY2xpY2tlZFwiKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gZ2VvbG9jYXRpb25FcnJvcihlcnIpIHtcbiAgICAgICAgJChcImltZy5nZW9sb2NhdGUuY2xpY2tlZFwiKS5hdHRyKFwic3JjXCIsIFwiaW1hZ2VzL2ljb24tZ2VvbG9jYXRlLnN2Z1wiKS5yZW1vdmVDbGFzcyhcImNsaWNrZWRcIik7XG4gICAgICAgIGNvbnNvbGUud2FybihlcnIpO1xuICAgIH1cbn0pOyIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyJdLCJuYW1lcyI6WyJzZXRTZWxlY3RDbGFzcyIsImVsZW1lbnQiLCJ2YWwiLCJyZW1vdmVDbGFzcyIsImFkZENsYXNzIiwic2V0QWR2YW5jZWRGaWx0ZXJzVG9nZ2xlTGFiZWwiLCJpcyIsIiQiLCJkb2N1bWVudCIsInJlYWR5IiwiZWFjaCIsIm9uIiwic2xpZGVUb2dnbGUiLCJoaWRlIiwidGV4dCIsInNob3ciLCJrZXl1cCIsInByb3AiLCJjaGFuZ2UiLCJjaGVja2VkIiwiYXV0b2NvbXBsZXRlIiwic291cmNlIiwibWluTGVuZ3RoIiwiZGVsYXkiLCJhcHBlbmRUbyIsImF0dHIiLCJldmVudCIsInVpIiwibmFtZSIsIml0ZW0iLCJzZWFyY2giLCJwYXJlbnQiLCJmaW5kIiwicmVzcG9uc2UiLCJzZWxlY3QiLCJjaXR5IiwiY291bnRyeSIsImxhdGl0dWRlIiwibG9uZ2l0dWRlIiwiX3JlbmRlckl0ZW0iLCJ1bCIsImFkZHJlc3MiLCJjb25jYXQiLCJhcHBlbmQiLCJuYXZpZ2F0b3IiLCJnZW9sb2NhdGlvbiIsIm9wdGlvbnMiLCJlbmFibGVIaWdoQWNjdXJhY3kiLCJ0aW1lb3V0IiwibWF4aW11bUFnZSIsImdldEN1cnJlbnRQb3NpdGlvbiIsImhhbmRsZVBvc2l0aW9uIiwiZ2VvbG9jYXRpb25FcnJvciIsInBvc2l0aW9uIiwidGFyZ2V0IiwiYWpheCIsInR5cGUiLCJ1cmwiLCJjb29yZHMiLCJzdWNjZXNzIiwiZGF0YSIsImVycm9yIiwiZXJyIiwiY29uc29sZSIsIndhcm4iXSwic291cmNlUm9vdCI6IiJ9