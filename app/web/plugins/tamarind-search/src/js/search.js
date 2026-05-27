// Control JS para filtro del Search sidebar
import filterSearch from "./filter-search.js";

document.addEventListener('DOMContentLoaded', function() {
    filterSearch.init();
});


// hide duplicated search results if are as featured in the top
document.addEventListener('DOMContentLoaded', function() {
    // Select all articles within .new-search-results-featured
    const featuredArticles = document.querySelectorAll('.new-search-results-featured article');

    if (featuredArticles.length > 0) {
      featuredArticles.forEach(function(featuredArticle) {
        // Get the value of the data-id attribute of the featured article
        const articleID = featuredArticle.getAttribute('data-id');

        // Select and remove corresponding articles in .common-search-results
        const commonArticles = document.querySelectorAll('.common-search-results article[data-id="' + articleID + '"]');
        commonArticles.forEach(function(commonArticle) {
          commonArticle.remove();
        });
      });
    }
});
