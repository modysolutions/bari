/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./plugins/tamarind-search/src/js/sidebar-filters.js":
/*!***********************************************************!*\
  !*** ./plugins/tamarind-search/src/js/sidebar-filters.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initTaxonomyHierarchy)
/* harmony export */ });
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/url */ "@wordpress/url");
/* harmony import */ var _wordpress_url__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_url__WEBPACK_IMPORTED_MODULE_1__);


function initTaxonomyHierarchy() {
  const containers = document.querySelectorAll('.filter-container');
  if (!containers) {
    return;
  }
  containers.forEach(container => {
    container.addEventListener('change', e => {
      var _container$getAttribu;
      if (e.target.matches(`input[name="${(_container$getAttribu = container.getAttribute('data-target')) !== null && _container$getAttribu !== void 0 ? _container$getAttribu : 'category'}[]"]`)) {
        handleHierarchyToggle(e.target, container);
        triggerSearchUpdate(container).then(r => {});
      }
    });
    const dateRadios = document.querySelectorAll('input[name="date"]');
    if (dateRadios) {
      dateRadios.forEach(radio => {
        radio.addEventListener('change', () => {
          triggerSearchUpdate(container).then(r => {});
        });
      });
    }
  });
}

/**
 * Handles showing children and hiding siblings visually
 */
function handleHierarchyToggle(checkbox, container) {
  const listItem = checkbox.closest('.checkbox-item');
  const parentList = listItem.closest('ul');
  const siblings = Array.from(parentList.children).filter(el => el !== listItem && el.classList.contains('checkbox-item'));
  const menuOption = listItem.closest('.menu-options');
  if (checkbox.checked) {
    listItem.classList.add('is-open');
    siblings.forEach(sibling => {
      if (container.getAttribute('data-hide-siblings') === 'true') {
        sibling.classList.add('is-hidden-by-sibling');
      }
    });
    menuOption.style.maxHeight = 'initial';
  } else {
    listItem.classList.remove('is-open');
    const nestedCheckboxes = listItem.querySelectorAll('.child-list input[type="checkbox"]');
    nestedCheckboxes.forEach(cb => {
      cb.checked = false;
      cb.closest('.checkbox-item').classList.remove('is-open');
    });
    siblings.forEach(sibling => {
      sibling.classList.remove('is-hidden-by-sibling');
    });
  }
}

/**
 * Updates URL via pushState and fetches new results from the REST API
 */
async function triggerSearchUpdate(container) {
  const target = container.getAttribute('data-target');
  const checkedBoxes = document.querySelectorAll(`input[name="${target}[]"]:checked`);
  const selected = Array.from(checkedBoxes).map(cb => cb.value);
  const selectedDate = document.querySelector('input[name="date"]:checked')?.value;
  const params = new URLSearchParams(window.location.search);
  for (const key of params.keys()) {
    if (key.startsWith(`${target}[`)) {
      params.delete(key);
    }
  }
  params.set('date', selectedDate || 'all');
  params.set('paged', '1');
  selected.forEach((value, key) => {
    params.set(`${target}[${key}]`, value);
  });
  const newUrl = `${window.location.pathname}?${params.toString()}`;
  ;
  window.history.pushState({
    path: newUrl
  }, '', newUrl);
  try {
    const apiPath = (0,_wordpress_url__WEBPACK_IMPORTED_MODULE_1__.addQueryArgs)('/tamarind/v1/search', Object.fromEntries(params.entries()));
    document.querySelector('.search-results-list').style.opacity = '0.5';
    const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
      path: apiPath
    });
    renderNewResults(response);
    setTimeout(() => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }, 300);
  } catch (error) {
    console.error('Failed to fetch search results:', error);
    document.querySelector('.search-results-list').style.opacity = '1';
  }
}

/**
 * Handles DOM injection of the REST API response
 */
function renderNewResults(data) {
  const resultsContainer = document.querySelector('.search-results-list');
  const template = document.querySelector('#search-result-item-template')?.innerHTML;
  const notFoundTemplate = document.querySelector('#search-results-not-found-text')?.innerHTML;
  if (!resultsContainer || !template) {
    console.error('Results container or template not found.');
    return;
  }
  resultsContainer.innerHTML = '';
  const parser = new DOMParser();
  const pagination = document.querySelector('.tm-pagination');
  pagination.innerHTML = '';
  if (data.pagination) {
    pagination.innerHTML = data.pagination;
  }
  if (data.items.length === 0) {
    resultsContainer.innerHTML = notFoundTemplate;
  } else {
    data.items.forEach(item => {
      let renderedItem = template;
      Object.keys(item).forEach(key => {
        const regex = new RegExp(`{{\\s*${key}\\s*}}`, 'g');
        renderedItem = renderedItem.replace(regex, item[key]);
      });
      const doc = parser.parseFromString(renderedItem, 'text/html');
      const node = doc.body.firstChild;
      node.querySelectorAll('.icon-candado').forEach(item => item.style.display = 'none');
      const padlockIcon = node.querySelector(`.icon-padlock${item.is_locked === true ? '-locked' : ''}`);
      if (padlockIcon) {
        padlockIcon.style.display = 'block';
      }
      resultsContainer.appendChild(node);
    });
  }
  resultsContainer.style.opacity = '1';
}

/***/ }),

/***/ "./plugins/tamarind-search/src/scss/main.scss":
/*!****************************************************!*\
  !*** ./plugins/tamarind-search/src/scss/main.scss ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/url":
/*!*****************************!*\
  !*** external ["wp","url"] ***!
  \*****************************/
/***/ ((module) => {

module.exports = window["wp"]["url"];

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
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!********************************************!*\
  !*** ./plugins/tamarind-search/src/app.js ***!
  \********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _scss_main_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./scss/main.scss */ "./plugins/tamarind-search/src/scss/main.scss");
/* harmony import */ var _js_sidebar_filters__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./js/sidebar-filters */ "./plugins/tamarind-search/src/js/sidebar-filters.js");


document.addEventListener('DOMContentLoaded', () => {
  (0,_js_sidebar_filters__WEBPACK_IMPORTED_MODULE_1__["default"])();
});
})();

/******/ })()
;
//# sourceMappingURL=search.js.map