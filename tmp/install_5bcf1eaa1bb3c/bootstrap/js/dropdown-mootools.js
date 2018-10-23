if (typeof MooTools != 'undefined') {
    var mHide = Element.prototype.hide;
    Element.implement({
        hide: function() {
                if (this.hasClass("dropdown")) {
                    return this;
                }
				if (this.hasClass("hasTooltip")) {
                    return this;
                }
                mHide.apply(this, arguments);
            }
    });
}