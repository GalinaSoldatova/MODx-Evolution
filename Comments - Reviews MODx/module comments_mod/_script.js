(function($){

    $(document).on('click', '.del_unpublished', function () {
        $.ajax({
            url: "/assets/modules/comments_mod/_action_ajax.php?act=del_unpublished",
            statusCode: {
                200: function () { // выполнить функцию если код ответа HTTP 200
                     $(".success").css({"display": "inline-block"});
                    $(".success").text("Обновление страницы");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            }
        })

    });

    $(document).on('click', '.publish_all', function () {
        $.ajax({
            url: "/assets/modules/comments_mod/_action_ajax.php?act=publish_all",
            statusCode: {
                200: function () { // выполнить функцию если код ответа HTTP 200
                     $(".success").css({"display": "inline-block"});
                    $(".success").text("Обновление страницы");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            }
        })

    });
    
    $(document).on('click', '.unpublish_all', function () {
        $.ajax({
            url: "/assets/modules/comments_mod/_action_ajax.php?act=unpublish_all",
            statusCode: {
                200: function () { // выполнить функцию если код ответа HTTP 200
                     $(".success").css({"display": "inline-block"});
                    $(".success").text("Обновление страницы");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            }
        })

    });
    
    $(document).on('click', 'td .change', function () {

    //    var val = $(this).val();
        var published = $(this).data('publ');
        var rowid = $(this).data('id');
        
        var new_published;
        
        if (published == 0) {
            new_published=1;
        } else {
            new_published=0;
        }
        
        $.ajax({
            url: "/assets/modules/comments_mod/_action_ajax.php?act=change_status&publ="+new_published+"&id="+rowid,
            statusCode: {
                200: function () { // выполнить функцию если код ответа HTTP 200
                    $(".success").css({"display": "inline-block"});
                    $(".success").text("Обновление страницы");
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            }
        })

    });
    
})(jQuery);