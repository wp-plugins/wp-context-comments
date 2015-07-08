$ = jQuery;

$(function() {

    $('body').append('<div id="view-comment" class="h"><p></p><button id="close-comment">Close</button></div>')

    // init
    data = $.parseJSON(wpccparams.comments)

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

    $('span.comment').on('hover', function(){
        $('body').toggleClass('cinema')
        $(this).toggleClass('shine')
    }, function(){
        $('body').toggleClass('cinema')
        $(this).toggleClass('shine')
    }).on('click', function(){

        for(c in data){
            if($(this).data('id').toString() === data[c]['comment_ID'].toString()){
                $('#view-comment p').text(data[c]['comment_content'])
            }
        }

        $('#view-comment').css({
            top: $(this).offset().top + $(this).height() + 20,
            left: $(this).offset().left
        }).removeClass('h')
    })

    $('#view-comment button').on('click', function(){
        $('#view-comment').addClass('h')
    })


    if(wpccparams.logged_in){
        $('body').append('<div id="add-comment" class="h"><textarea id="comment"></textarea><button>Comment</button></div>')

        rangy.init();

        highlighter = rangy.createHighlighter();
        highlighter.addClassApplier(rangy.createClassApplier("highlight", {
            ignoreWhiteSpace: true,
            tagNames: ["span", "a"]
        }));

        $('body').on('mouseup', function(){
            if(rangy.getSelection().toString().length > 0){
                highlighter.removeAllHighlights()
                highlighter.highlightSelection("highlight")
                rangy.getSelection().removeAllRanges()

                $('#add-comment').css({
                    top: $('.highlight').offset().top + $('.highlight').height() + 20,
                    left: $('.highlight').offset().left
                }).removeClass('h')

                $('#add-comment textarea').val('').focus()
            }
        })

        $('body').on('keyup', function(e){
            if(e.keyCode === 27){
                highlighter.removeAllHighlights()
                $('#add-comment').addClass('h')
                $('#view-comment').addClass('h')
            }
        })

        $('#add-comment button').on('click', function(){
            console.log({
               'id': wpccparams.postid,
               'action': 'wpcc_etherify',
               'content': $('#add-comment textarea').val(),
               'context': $('.highlight').text()
            })
             $.post( wpccparams.admin_url+'admin-ajax.php', {
                'id': wpccparams.postid,
                'action': 'wpcc_etherify',
                'content': $('#add-comment textarea').val(),
                'context': $('.highlight').text()
             }, function( data ) {
                 console.log(data)
                $('.highlight').removeClass('highlight').addClass('underline')
                $('#add-comment').addClass('h')
             });
        })
    }

});
