import anime from 'animejs/lib/anime.es.js';
$(document).ready(function(){
    anime({
        targets: selector,
        duration: 500,
        delay: anime.stagger(1000/30, {
            start: 1000
        }),
        translateY: [100, 0],
        opacity: [0, 1]
    });
});