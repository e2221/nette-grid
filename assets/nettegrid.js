(window._stack = window._stack || []).push(function (di) {
    di.getService('snippetManager').on('after-update', function () {
        $(function() {
            disableEdit();
            enableEdit();
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

disableEdit = function(){
    let editButtonsSelector = document.querySelectorAll('.nette-grid [data-edit]');
    editButtonsSelector.forEach(function(item){
        item.addEventListener("click", function(){
            let gridID = item.dataset.gridid;
            let customizedSelector = document.querySelectorAll('.nette-grid [data-edit][data-gridid="'+gridID+'"]')
            customizedSelector.forEach(function(item_2){
                item_2.classList.add("disabled");
            });
        }) ;
    });
}

enableEdit = function (){
    let editButtonsSelector = document.querySelectorAll('.nette-grid [data-cancel]');
    editButtonsSelector.forEach(function(item){
        item.addEventListener("click", function(){
            let gridID = item.dataset.gridid;
            let customizedSelector = document.querySelectorAll('.nette-grid [data-edit][data-gridid="'+gridID+'"]')
            customizedSelector.forEach(function(item_2){
                item_2.classList.remove("disabled");
            });
        }) ;
    });
}