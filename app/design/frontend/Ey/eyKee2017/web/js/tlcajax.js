define(['uiComponent'],function(Component){
    return Component.extend({
        initialize: function(){
            this._super();
            this.sayHello = "Hello World";
        }
    });
});