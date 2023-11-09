import '../scss/immersive.scss';
import '../scss/immersive-mobile.scss';

$(window).scroll(function(){
    $(".arrow").css("opacity", 1 - $(window).scrollTop() / 250);
});

// https://css-tricks.com/snippets/javascript/loop-queryselectorall-matches/
function loadAllProgressiveImages() {
    var progressiveBgImages = document.querySelectorAll(".progressive-bg-image");
    [].forEach.call(progressiveBgImages, function(progressiveBgImage) {
        var imgLarge = new Image();
        imgLarge.src = progressiveBgImage.dataset.large;
        imgLarge.onload = function () {
            progressiveBgImage.style.backgroundImage = "url("+progressiveBgImage.dataset.large+")";
            progressiveBgImage.classList.remove('small');
        };
    });
}

$(document).ready(function(){
    loadAllProgressiveImages();
});
