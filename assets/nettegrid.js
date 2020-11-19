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


submitForm = function(formID, submittedBy){
    _context.invoke(function(di) {
        var frm = di.getService('formLocator').getForm(formID);
        var timer = 0;
        clearTimeout(window.timer);
        timer = setTimeout(function () {
            console.log('a');
            frm.submit('filterSubmit');
        }, 500);
    });
}

autoSubmitForm = function(){
    $(document).on('keyup', 'input[data-autosubmit]', function(e){
        //console.log($(this).data('formid'));
        var $this, code;
        code = e.which || e.keyCode || 0;
        if ((code !== 13) && ((code >= 9 && code <= 40) || (code >= 112 && code <= 123))) {
            return;
        }
        clearTimeout(window.autosubmit_timer);
        $this = $(this);
        return window.autosubmit_timer = setTimeout((function(_this) {
            return function(){
                console.log('onchange');
                return submitForm($this.data("formid"), $this.data("container"));
            }
        })(this), 1000);

    });
};

autoSubmitFormTest = function(){
    $(document).on('keyup', 'input[data-autosubmit]', function(e){
        //console.log($(this).data('formid'));
        var timer = 0;
        var $this, code;
        code = e.which || e.keyCode || 0;
        if ((code !== 13) && ((code >= 9 && code <= 40) || (code >= 112 && code <= 123))) {
            return;
        }
        $this = $(this);

    });
};


delyCallCallback = function(callback, ms){
    console.log(callback);
    if(ms === '') ms = 500;
    var timer = 0;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}