const data_mapsvg = {
  rootTabs: ".responsive-tabs",
  tabsList: ".responsive-tabs__list",
  panels: ".responsive-tabs__panel",
  mapWrap: ".mapsvg-wrap-all",
  detailsContainer: ".mapsvg-details-container",
  detailsView: ".mapsvg-controller-view",
  detailsToolbar: ".mapsvg-controller-view-toolbar",
  detailsContent: ".mapsvg-controller-view-content",
  popoverClose: ".mapsvg-popover-close"
};

let elements_state = {
  root_tabs: null,
  tabs_list: null,
  panels: [],
};

let id_active_map = null;
let id_country = "no-country";

/**
 * Resolves when the DOM is ready (DOMContentLoaded fired or document already interactive/complete).
 */
function on_dom_ready() {
  return new Promise((resolve) => {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", resolve, { once: true });
    } else {
      resolve();
    }
  });
}

/**
 * Polls until a condition returns true, or rejects after the given timeout.
 * Useful for waiting for globals or third-party widgets to be available.
 */
function wait_for(cond, options) {
  const { interval = 50, timeout = 10000 } = options || {};
  return new Promise((resolve, reject) => {
    const start = Date.now();
    (function tick() {
      if (cond()) return resolve();
      if (Date.now() - start > timeout)
        return reject(new Error("Timeout waiting for condition"));
      setTimeout(tick, interval);
    })();
  });
}

/**
 * Returns true when all maps have been mounted and their count matches the number of panels.
 */
function all_maps_loaded() {
  const maps = document.querySelectorAll(data_mapsvg.mapWrap);
  const panels = document.querySelectorAll(data_mapsvg.panels);
  return maps.length > 0 && maps.length === panels.length;
}

/**
 * Resolves once all maps/panels are present on the page (observes DOM mutations if needed).
 */
function wait_maps_mounted() {
  return new Promise((resolve) => {
    if (all_maps_loaded()) return resolve();
    const mo = new MutationObserver(() => {
      if (all_maps_loaded()) {
        mo.disconnect();
        resolve();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  });
}

/**
 * Fallback: gets the first map id from the DOM when MapSVG.get(0) is not available.
 */
function get_first_map_id() {
  const wrap = document.querySelector(data_mapsvg.mapWrap);
  return wrap?.getAttribute("data-map-id") ?? null;
}

/**
 * Safely obtains a MapSVG instance by its id.
 */
function get_map_by_id(id) {
  return window.MapSVG?.getById?.(id) ?? null;
}

/**
 * Clears directory and region selections on the given map.
 */
function clear_selection(map_id) {
  const map = get_map_by_id(map_id);
  map?.controllers?.directory?.deselectItems?.();
  map?.deselectAllRegions?.();
}

/**
 * Applies selected country on the given map and triggers a redraw.
 */
function apply_selection(map_id, country_id) {
  const map = get_map_by_id(map_id);
  if (!map) return;

  // clear always first.
  map.controllers?.directory?.deselectItems?.();
  map.deselectAllRegions?.();

  // Only select if the region exists and is enabled.
  if (has_region_enabled(map_id, country_id)) {
    map.controllers?.directory?.selectItems?.(country_id);
  }
  map.redraw?.();
}

/**
 * Copies given HTML to clipboard (both text/html and text/plain).
 */
function copy_to_clipboard(html) {
  function listener(e) {
    e.clipboardData.setData("text/html", html);
    e.clipboardData.setData("text/plain", html);
    e.preventDefault();
  }
  document.addEventListener("copy", listener);
  document.execCommand("copy");
  document.removeEventListener("copy", listener);
}

/**
 * Attaches a "Copy to clipboard" button to the details view toolbar.
 */
function attach_copy_button(panel) {
  const details = panel.querySelector(data_mapsvg.detailsContainer);
  const view = details?.querySelector?.(data_mapsvg.detailsView);
  const toolbar = view?.querySelector?.(data_mapsvg.detailsToolbar);
  const content = view?.querySelector?.(data_mapsvg.detailsContent);

  if (!toolbar || !content) return;

  // No duplicates.
  if (toolbar.querySelector(".js-copy-text")) return;

  const btn = document.createElement("button");
  btn.className = "js-copy-text";
  btn.title = "Copy text";
  btn.innerHTML = '<i class="fa fa-clipboard"></i> Copy to clipboard';

  const warn = document.createElement("div");
  warn.className = "copiedwarning";
  warn.textContent = "Copied!";
  warn.style.display = "none";

  toolbar.appendChild(btn);
  toolbar.appendChild(warn);

  btn.addEventListener("click", () => {
    copy_to_clipboard(content.innerHTML);
    warn.style.display = "block";
    setTimeout(() => (warn.style.display = "none"), 1200);
  });
}


/**
 * Attaches a handler to the popover close button to reset the selected country.
 */
function attach_popover_close(panel, on_close) {
  const details = panel.querySelector(data_mapsvg.detailsContainer);
  const close_btn = details?.querySelector?.(data_mapsvg.popoverClose);
  if (close_btn) {
    close_btn.addEventListener("click", on_close, { once: true });
  }
}

/**
 * Checks if the given country/region exists and is enabled in the given map.
 */
function has_region_enabled(map_id, country_id) {
  const map = get_map_by_id(map_id);
  if (!map || !country_id || country_id === "no-country") return false;
  const r = map.getRegion?.(country_id);
  // Regions can have a `disabled` flag in v6.
  return !!(r && !r.disabled);
}

/**
 * Removes the last details view if present inside the given container.
 */
function remove_last_details_view(container) {
  const details = container.querySelector(data_mapsvg.detailsContainer);
  const view = details?.querySelector?.(data_mapsvg.detailsView);
  if (view) view.remove();
}

/**
 * Handles tab click:
 * - switches active map,
 * - reapplies the selected country if any,
 * - opens or clears popovers accordingly.
 */
function on_tab_click(e) {
  const target = e.target;
  if (!target?.id) return;

  const content_panel = elements_state.root_tabs?.querySelector(
    `${data_mapsvg.panels}[aria-labelledby="${target.id}"]`
  );
  if (!content_panel) return;

  const wrap = content_panel.querySelector(data_mapsvg.mapWrap);
  const map_id = wrap?.getAttribute("data-map-id");
  if (!map_id) return;

  // Clear selection on previous map.
  if (id_active_map) clear_selection(id_active_map);
  id_active_map = map_id;

  // Region exists and is enabled in the target map?.
  const can_reapply = has_region_enabled(map_id, id_country);

  if (!can_reapply) {
    // Modal close.
    id_country = "no-country";
    apply_selection(map_id, id_country);
    remove_last_details_view(content_panel);
    return;
  }

  // Apply selection and open details.
  apply_selection(map_id, id_country);

  const map = get_map_by_id(map_id);
  const active_region = map?.getRegion?.(id_country);
  map?.loadDetailsView?.(active_region);

  setTimeout(() => {
    attach_copy_button(content_panel);
    attach_popover_close(content_panel, () => {
      id_country = "no-country";
    });
  }, 300);
}

/**
 * Binds click handling for the tabs list.
 */
function bind_tabs_events() {
  if (!elements_state.tabs_list) return;
  elements_state.tabs_list.addEventListener("click", on_tab_click);
}

/**
 * Binds MapSVG region/directory events for every panel's map:
 * - updates `id_country` when a user clicks a directory item or a region.
 */
function bind_region_events() {
  elements_state.panels.forEach((panel) => {
    const wrap = panel.querySelector(data_mapsvg.mapWrap);
    if (!wrap) return;

    const panel_id = wrap.getAttribute("data-map-id");
    if (!panel_id) return;

    const map = get_map_by_id(panel_id);
    if (!map) return;

    // No duplicates.
    map.events?.off?.("click.directoryItem");
    map.events?.off?.("click.region");

    // v6 sign: (e, obj, mapsvg) for directoryItem.
    map.events?.on?.("click.directoryItem", function (e, obj /* , mapsvg */) {
      id_country = obj?.id ?? "no-country";

      setTimeout(() => {
        attach_copy_button(panel);
      }, 50);
    });

    // v6 sign: (e, mapsvg) and e.target.id for region.
    map.events?.on?.("click.region", function (e /* , mapsvg */) {
      const regionId = e?.target?.id;
      if (regionId) id_country = regionId;

      setTimeout(() => {
        attach_copy_button(panel);
      }, 50);
    });
  });
}

/**
 * Orchestrates initialization:
 * - waits for DOM ready, MapSVG presence, and maps mounted,
 * - caches references (tabs/panels),
 * - determines the initial active map,
 * - binds tab and region events.
 */
async function init() {
  await on_dom_ready();
  await wait_for(() => typeof window.MapSVG !== "undefined");
  await wait_maps_mounted();

  elements_state.root_tabs = document.querySelector(data_mapsvg.rootTabs);
  elements_state.tabs_list = document.querySelector(data_mapsvg.tabsList);
  elements_state.panels = Array.from(
    document.querySelectorAll(data_mapsvg.panels)
  );

  // v6 tiene MapSVG.get(0).id normalmente
  id_active_map = window.MapSVG?.get(0)?.id ?? get_first_map_id();

  bind_tabs_events();
  bind_region_events();
}

init();
