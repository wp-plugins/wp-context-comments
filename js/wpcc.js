jQuery(function() {

    jQuery('body').append('<div id="view-comment" class="h"><p></p><button id="close-comment">Close</button></div>')

    // init
    data = jQuery.parseJSON(wpccparams.comments)

    for(c in data){
        var searchResultApplier = rangy.createClassApplier('comment', {
            'elementAttributes': {
                'data-id': data[c]['comment_ID']
            }
        });
        var range = rangy.createRange();
        var term = data[c]['context'][0];

        range.selectNodeContents(document.body);

        if(term !== "") {
            while (range.findText(term)) {
                searchResultApplier.applyToRange(range);
                range.collapse(false);
            }
        }
    }

    jQuery('span.comment').on('hover', function(){
        jQuery('body').toggleClass('cinema')
        jQuery(this).toggleClass('shine')
    }, function(){
        jQuery('body').toggleClass('cinema')
        jQuery(this).toggleClass('shine')
    }).on('click', function(){

        for(c in data){
            if(jQuery(this).data('id').toString() === data[c]['comment_ID'].toString()){
                jQuery('#view-comment p').text(data[c]['comment_content'])
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

    if(wpccparams.logged_in){

        jQuery('body').append('<div id="add-comment" class="h"><textarea id="comment"></textarea><button>Comment</button></div>')

        rangy.init();

        highlighter = rangy.createHighlighter();
        highlighter.addClassApplier(rangy.createClassApplier("highlight", {
            ignoreWhiteSpace: true,
            tagNames: ["span", "a"]
        }));

        jQuery(wpccparams.selectors).on('mouseup', function(){
            if(rangy.getSelection().toString().length > 0){
                highlighter.removeAllHighlights()
                highlighter.highlightSelection("highlight")
                rangy.getSelection().removeAllRanges()

                jQuery('#add-comment').css({
                    top: jQuery('.highlight').offset().top + jQuery('.highlight').height() + 20,
                    left: jQuery('.highlight').offset().left
                }).removeClass('h')

                jQuery('#add-comment textarea').val('').focus()
            }
        })

        jQuery('body').on('keyup', function(e){
            if(e.keyCode === 27){
                highlighter.removeAllHighlights()
                jQuery('#add-comment').addClass('h')
                jQuery('#view-comment').addClass('h')
            }
        })

        jQuery('#add-comment button').on('click', function(){
             jQuery.post( wpccparams.admin_url+'admin-ajax.php', {
                'id': wpccparams.postid,
                'action': 'wpcc_etherify',
                'content': jQuery('#add-comment textarea').val(),
                'context': jQuery('.highlight').text()
             }, function( data ) {
                jQuery('.highlight').removeClass('highlight').addClass('underline')
                jQuery('#add-comment').addClass('h')
             });
        })
    }

});
