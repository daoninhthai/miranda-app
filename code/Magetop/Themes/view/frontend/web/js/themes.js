/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
require([
    'jquery',
    'Magetop/owlcarousel'
    ], function($){
	$('.home-main-slide').owlCarousel({
		items:1,
		lazyLoad:true,
		autoplay: true,
		nav:true,
		loop:true,
		autoplayHoverPause:true,
		margin:0
	});
	$('.home-main-slide').on('translated.owl.carousel ', function(e) {
		$('.slide-caption').removeClass('start-a');
		$('.active .slide-caption').addClass('start-a');
	});	
});
		
require(['jquery'],function($){
	"use strict";
	$(window).scroll(function(){
		if ($(this).scrollTop() > 100) {
			$('#gotop').fadeIn();
		} else {
			$('#gotop').fadeOut();
		}
	});
	$('#gotop').click(function(){
		$('html, body').animate({scrollTop : 0},800);
		return false;
	});
	$('.heading .a-mobile').click(function () {
		$(this).toggleClass('openner')
		$(this).parent().next().slideToggle(200);
	});	
});

