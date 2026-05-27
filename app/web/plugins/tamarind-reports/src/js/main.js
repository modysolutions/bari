import '../scss/main.scss';
import './usage-reports/triggers';
import domReady from "@wordpress/dom-ready";
import UserTypeahead from "./usage-reports/user-typeahead";

domReady(() => {
    UserTypeahead.init();
})