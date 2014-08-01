/**
 +----------------------------------------------------------
 * 首页幻灯
 +----------------------------------------------------------
 */
$(document).ready(function() {
    $("#slideShow .controls li a").click(function() {
        shuffle();
        var rel = $(this).attr("rel");
        if ($("#" + rel).hasClass("current")) {
            return false;
        }
        $("#" + rel).stop(true, true).show();
        $(".current").fadeOut().removeClass("current");
        $("#" + rel).addClass("current");
        $(".active").removeClass("active");
        $(this).parents("li").addClass("active");
        set_new_interval(5000);
        return false;
    });
});
function banner_switch() {
    shuffle();
    var next = $('.slide.current').next('.slide').length ? $('.slide.current').next('.slide') : $('#slideShow .slides .slide:first');
    $(next).show();
    $(".current").fadeOut().removeClass("current");
    $(next).addClass("current");
    var next_link = $(".active").next("li").length ? $('.active').next('li') : $('#slideShow .controls li:first');
    $(".active").removeClass("active");
    $(next_link).addClass('active');
}
$(function() {
    slide = setInterval("banner_switch()", 5000);
});
function set_new_interval(interval) {
    clearInterval(slide);
    slide = setInterval("banner_switch()", interval);
}
function shuffle() {
    $(".slide").css("z-index", 1).hide();
    $(".current").css("z-index", 2).show();
}