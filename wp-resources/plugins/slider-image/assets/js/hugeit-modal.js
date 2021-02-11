var hugeitModal = {
    show: function(elementId, args){
        var el = jQuery('#'+elementId);
        console.log(el);
        if(el.length){
            el.css('display','flex');
        }
    },

    hide: function(elementId){
        var el = jQuery('#'+elementId);
        el.css('display','none');
    }
};

jQuery(document).ready(function(){
    jQuery('body').on('click','.-hugeit-modal-close',function(){
        hugeitModal.hide(jQuery(this).closest('.-hugeit-modal').attr('id'));
    });
});