jQuery(function() {

    // init
    data = jQuery.parseJSON(wpccparams.comments)
    comments = {}

    for(c in data){

        if(comments[data[c]['context']] === undefined){
            comments[data[c]['context']] = {
                count: 0,
                ids: ''
            }
        }

        comments[data[c]['context']].count++

        if(comments[data[c]['context']]['ids'].length > 0){
            comments[data[c]['context']].ids += ','+data[c]['comment_ID']
        } else {
            comments[data[c]['context']].ids = data[c]['comment_ID']
        }
    }

    for(c in comments){

        jQuery(wpccparams.selectors+":contains('"+c+"')").html(function(_, html) {
            return html + '<span data-id="'+comments[c]['ids']+'" class="comment">'+comments[c]['count']+'</span>';
        });
    }

    jQuery('span.comment').on('click', function(){

        jQuery('#view-comment p').text('');

        for(c in data){

            var ids = []

            if(typeof jQuery(this).data('id') === 'number'){
                ids.push(jQuery(this).data('id'))
            } else {
                ids = jQuery(this).data('id').split(',')
            }

            for(id in ids){
                if(ids[id].toString() === data[c]['comment_ID'].toString()){
                    jQuery('#view-comment p').html(
                        jQuery('#view-comment p').html()+data[c]['comment_content']+'<br>'
                    )
                }
            }
        }

        jQuery('#view-comment').css({
            top: jQuery(this).offset().top + jQuery(this).height() + 20,
            left: jQuery(this).offset().left
        }).removeClass('h')

    })

    jQuery('#view-comment button').on('click', function(){
        jQuery('#view-comment').addClass('h')
    })

    if(true){

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

                jQuery('#add-comment textarea').val('').focus()

                s = window.getSelection();
                oRange = s.getRangeAt(0);
                oRect = oRange.getBoundingClientRect();

                jQuery('#add-comment').css({
                    top: jQuery(window).scrollTop() + oRect.top + oRect.height + 10,
                    left: jQuery(wpccparams.selectors).offset().left
                }).removeClass('h')
            }
        });

        jQuery('body').on('keyup', function(e){
            if(e.keyCode === 27){
                jQuery('#add-comment').addClass('h')
                jQuery('#view-comment').addClass('h')
            }
        })

        jQuery('#add-comment .close').on('click', function(){
            jQuery('#add-comment').addClass('h')
            jQuery('#view-comment').addClass('h')
        })

        jQuery('body').on('click', function (e) {
            if(jQuery(e.target).closest('#add-comment').length === 0){
                jQuery('#add-comment').addClass('h')
            }
        })
    }

});

RegExp.quote = function(str) {
    return (str+'').replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
};
