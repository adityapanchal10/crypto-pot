function key_down(e) {
    if(e.keyCode === 13) {
    search_func();
    }
}

function search_func() {
    var search_query = document.getElementById("search").value;
    window.location = "search.php?search=" + search_query;
}