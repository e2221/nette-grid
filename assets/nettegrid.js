(window._stack = window._stack || []).push(function (di) {
    di.getService('snippetManager').on('after-update', function () {
        $(function() {

        });
    });
});


var openLinkAjax;
openLinkAjax = function(url, method, params){
    method = method || 'POST';
    _context.invoke(function(di) {
        di.getService('page').open(url, method, params);
    });
}