import '../scss/app.scss';
import '../scss/app-mobile.scss';

$(document).ready(function(){
    $(".menu_item_burger").on("click", function(){
        if ($(".header2").is(':visible')) {
            $(".menu_item_burger").removeClass('open');
        } else {
            $(".menu_item_burger").addClass('open');
        }
        $(".header2").slideToggle("fast");
    });
});
