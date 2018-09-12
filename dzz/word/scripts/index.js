jQuery(function () {
    jQuery("[data-toggle='tooltip']").tooltip();
//            左侧菜单显示控制 点击其他位置隐藏
    jQuery('.leftTopmenu').on('click',function(event) {
        event.stopPropagation();
        jQuery('.bs-left-container').addClass('showLeft');
    })
    jQuery(document).click(function (event) {
        if(jQuery('.bs-left-container').hasClass('showLeft')) {
            jQuery('.bs-left-container').removeClass('showLeft');
        }
    })
    jQuery('.bs-left-container').click(function(event) {
        event.stopPropagation();
    })
    jQuery(document).on('click','#fwin_rename-window',function (event) {
        event.stopPropagation();
    })


        jQuery('#publish_stick').on('mouseenter',function(){
            jQuery(this).addClass('hover');
        });
        jQuery('#publish_stick').on('mouseleave',function(){
            jQuery(this).removeClass('hover');
        });

        jQuery(document).on('mouseover','.list-item,.cmt-item,.image-item,.attach-item',function(){
            jQuery(this).addClass('hover');
        });
        jQuery(document).on('mouseout','.list-item,.cmt-item,.image-item,.attach-item',function(){
            jQuery(this).removeClass('hover');
        });

})


function opendoc(url, rid, blank){
    if (!url) {
        showmessage(__lang.no_app_open, 'error', 1000, 1);
    } else {
        if(rid) jQuery.post(mod_url+'&op=ajax&do=addopenrecord',{'rid':rid},function(){},'json');
        if (blank) {
            window.open(url);
        } else {
            window.location.href=url;
        }
    }
}

function attach_down(t){
    var path = jQuery(t).parents('.item').data('dpath');
    var url=DZZSCRIPT+'?mod=io&op=download&path='+path;
    if(BROWSER.ie){
        window.open(url);
    }else{
        if(!window.frames['hidefram']) jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
        window.frames['hideframe'].location=url;
    }
}