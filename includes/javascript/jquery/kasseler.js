(function($){
  $.fn.changeType = function(type) {  
    return this.each(function(i, elm) {
        var newElm = $("<input type=\""+type+"\" />");
        for(var iAttr = 0; iAttr < elm.attributes.length; iAttr++) {
            var attribute = elm.attributes[iAttr].name;
            if(attribute === "type") {
                continue;
            }
            newElm.attr(attribute, elm.attributes[iAttr].value);
        }
        newElm.value=elm.value;
console.log(newElm);
        $(elm).replaceWith(newElm);
    });
  };
})(jQuery);
