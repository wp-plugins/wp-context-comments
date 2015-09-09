jQuery(function() {

    var comments = jQuery.parseJSON(wpccparams.comments)

    // for(var c in comments){
    //     var re = new RegExp(comments[c].re, 'g');
    //     var m;
    //
    //     jQuery(wpccparams.selectors).each(function(){
    //
    //         jQuery(this).html(function(_, html) {
    //             return html.replace(re, '$1' + '<span data-id="'+comments[c]['ids']+'" class="comment">'+comments[c]['count']+'</span>')
    //         });
    //
    //     })
    // }

    jQuery('span.comment').on('click', function(){

        jQuery('#view-comment .comment-list').html('');

        // for(c in comments){

            var ids = []

            if(typeof jQuery(this).data('id') === 'number'){
                ids.push(jQuery(this).data('id'))
            } else {
                ids = jQuery(this).data('id').split(',')
            }

            for(id in ids){

                jQuery('#view-comment .comment-list').html(
                    jQuery('#view-comment .comment-list').html()
                    + jQuery('#comment-'+ids[id]).outer()
                )

                // if(ids[id].toString() === comments[c]['comment_ID'].toString()){
                //     jQuery('#view-comment p').html(
                //         jQuery('#view-comment p').html()+comments[c]['comment_content']+'<br>'
                //     )
                // }
            }
        // }

        jQuery('#view-comment')
        .removeClass('h')
        .css({
            top: jQuery(this).offset().top - jQuery('#view-comment').height() / 2,
            left: jQuery(wpccparams.selectors).offset().left
        })

    })

    jQuery('#view-comment button').on('click', function(){
        jQuery('#view-comment').addClass('h')
    })

    window.selecting(jQuery(wpccparams.selectors), function(selector, e) {
        if(selector.length){

            var re_chars = wpccparams.re_chars.split('')

            for(var c in re_chars){
                var char = re_chars[c],
                    last = selector.slice(-1),
                    last_two = selector.slice(-2);

                if(char === last){
                    selector = selector.substring(0, selector.length - 1);
                }

                if(char + ' ' === last_two){
                    selector = selector.substring(0, selector.length - 2);
                }
            }

            var re_string = "["+wpccparams.re_chars+"]([^"+wpccparams.re_chars+"]*?"+RegExp.quote(selector)+".*?["+wpccparams.re_chars+"])"
            var re = new RegExp(re_string, "g");
            var str = '.'+jQuery(wpccparams.selectors).text();
            var m;

            while ((m = re.exec(str)) !== null) {
                if (m.index === re.lastIndex) {
                    re.lastIndex++;
                }
                jQuery('#add-comment p#contextstring').text(m[1].trim())
                jQuery('#add-comment input[name=context]').val(m[1].trim())
            }

            selection = window.getSelection();
            range = selection.getRangeAt(0);
            rectangle = range.getBoundingClientRect();

            var _left = rectangle.left,
                _right = rectangle.right,
                _center = _left + (_right - _left) / 2,
                _top = jQuery(window).scrollTop() + rectangle.top + rectangle.height,
                _height = rectangle.height;

            jQuery('#add-comment').css({
                top: jQuery(window).scrollTop() + rectangle.top + rectangle.height + 10,
                left: jQuery(wpccparams.selectors).offset().left
            })

            jQuery('#add-comment-btn').css({
                left: _right - jQuery('#add-comment-btn').width() / 2,
                top: _top - _height - jQuery('#add-comment-btn').height() - 3
            }).removeClass('h').click(function(){
                jQuery('#add-comment').removeClass('h')
                jQuery('#add-comment-btn').addClass('h')
            })

            // jQuery('#add-comment textarea').val('').focus()
        } else {
            jQuery(wpccparams.selectors).find('.comment').show()
        }
    });

    jQuery('body').on('keyup', function(e){
        if(e.keyCode === 27){
            jQuery('#add-comment').addClass('h')
            jQuery('#view-comment').addClass('h')
            jQuery('#add-comment-btn').addClass('h')
        }
    })

    jQuery('#add-comment .close').on('click', function(){
        jQuery('#add-comment').addClass('h')
        jQuery('#view-comment').addClass('h')
        jQuery('#add-comment-btn').addClass('h')
    })

    jQuery('body').on('click', function (e) {
        if(jQuery(e.target).closest('#add-comment, #add-comment-btn').length === 0){
            jQuery('#add-comment').addClass('h')
            jQuery('#add-comment-btn').addClass('h')
        }
    })
});

RegExp.quote = function(str) {
    return (str+'').replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
};

jQuery.fn.outer = function() {
  return jQuery('<div />').append(this.eq(0).clone()).html();
};

function getSelectionHtml() {
    var html = "";
    if (typeof window.getSelection != "undefined") {
        var sel = window.getSelection();
        if (sel.rangeCount) {
            var container = document.createElement("div");
            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                container.appendChild(sel.getRangeAt(i).cloneContents());
            }
            html = container.innerHTML;
        }
    } else if (typeof document.selection != "undefined") {
        if (document.selection.type == "Text") {
            html = document.selection.createRange().htmlText;
        }
    }
    return html;
}
