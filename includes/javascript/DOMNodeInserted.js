setInterval(function(){
    for (var a in $.DOMlive.register) {
        o = $(a).not(".domchecked");
        if (o.length > 0) {
            o.each(function () {
                $.DOMlive.check(this);
            });
        }
    }
}, 100);