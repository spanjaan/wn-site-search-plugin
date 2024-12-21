document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input'); 
    const autocompleteResults = document.getElementById('autocomplete-results');

    searchInput.addEventListener('input', function () {
        if (searchInput.value.trim() !== '') {
            autocompleteResults.classList.remove('d-none');
        } else {
            autocompleteResults.classList.add('d-none'); 
        }
    });
});