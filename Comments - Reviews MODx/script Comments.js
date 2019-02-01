/* отзывы */

$(document).on('click', '.make .star', function () {
    $('.make .star').removeClass("fixed");
    var star = $(this).data('num');
    $('.numstar').val(star);
    $(this).addClass("fixed");

});

$(document).on('mouseover', '.make .star', function () {
    var star = $(this).data('num');
    $('.make .star[data-num=' + star + ']').addClass("red");
    for (var i = 1; i < 6; i++) {
        if (i <= star) {
            $('.make .star[data-num=' + i + ']').addClass("red");
        } else {
            $('.make .star[data-num=' + i + ']').removeClass("red");
        }
    }
});

$(document).on('mouseleave', '.make .star', function () {
    if ($(".make .star").hasClass("fixed")) {
        var star = $('.make .fixed').data('num');
        for (var i = 1; i < 6; i++) {
            if (i <= star) {
                $('.make .star[data-num=' + i + ']').addClass("red");
            } else {
                $('.make .star[data-num=' + i + ']').removeClass("red");
            }
        }
    } else {
        $('.make .star').removeClass("red");
    }
});

$(document).on('click','.addComment',function(){ 
    $("#form_comment").toggle("slow");
});


function send() {
    var msg = $('#form_comment').serialize();
    $.ajax({
        type: 'POST',
        url: 'ajax.html?act=sendComment',
        data: msg,
        success: function (data) {
            $('#form_comment').css({
                "display": "none"
            });
            $('#results').html("Ваш отзыв успешно добавлен, вскоре он будет опубликован.");
        },
        error: function (xhr, str) {
            alert('Произошла ошибка. Попробуйте добавить отзыв ещё раз.');
        }
    });
}