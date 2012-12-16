(function($){

    $.confirm = function(params){

        if($('#confirmOverlay').length){
            // A confirm is already shown on the page:
            return false;
        }

        var buttonHTML = '';
        $.each(params.buttons,function(name,obj){

            // Generating the markup for the buttons:

            buttonHTML += '<a href="#" class="button '+obj['class']+'">'+name+'<span></span></a>';

            if(!obj.action){
                obj.action = function(){ return true };
            }
        });

        var markup = [
            '<div id="confirmOverlay"></div>',
            '<div id="confirmBox">',
            '<h1>',params.title,'</h1>',
            '<p>',params.message,'</p>',
            '<div id="confirmButtons">',
            buttonHTML,
            '</div></div>'
        ].join('');

        $(markup).hide().appendTo('body').fadeIn(function(){
            if(typeof params['onload'] == 'function') params.onload();
        });

        var buttons = $('#confirmBox .button'),
            i = 0;

        $.each(params.buttons,function(name,obj){
            buttons.eq(i++).click(function(){

                // Calling the action attribute when a
                // click occurs, and hiding the confirm.

                if(obj.action() !== false) $.confirm.hide();
                return false;
            });
        });
    }

    $.confirm.hide = function(){
        $('#confirmOverlay').fadeOut(function(){
            $(this).remove();
        });
        $('#confirmBox').fadeOut(function(){
            $(this).remove();
        });
    }

})(jQuery);
