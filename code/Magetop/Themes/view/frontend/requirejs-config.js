var config = {

	map: {
		'*': {
			'themes': 'Magetop_Themes/js/themes',
		},
	},

	paths: {
		'Magetop/easing'		: 'Magetop_Themes/js/plugins/jquery.easing.min',
		'Magetop/bootstrap'		: 'Magetop_Themes/js/plugins/bootstrap.min',
		'Magetop/fancybox'		: 'Magetop_Themes/js/plugins/jquery.fancybox.pack',
		'Magetop/owlcarousel'	: 'Magetop_Themes/owlcarousel/owl.carousel.min',
		'Magetop/easytabs'	    : 'Magetop_Themes/js/jquery.easytabs.min',
		'Magetop/zoom'			: 'Magetop_Themes/js/plugins/jquery.zoom.min',
	},

	shim: {
		'Magetop/easing': {
			deps: ['jquery']
		},
		'Magetop/fancybox': {
			deps: ['jquery']
		},
		'Magetop/owlcarousel': {
			deps: ['jquery']
		},
		'Magetop/easytabs': {
			deps: ['jquery']
		},
        'themes': {
            deps: ['jquery', 'Magetop/easing', 'Magetop/fancybox', 'Magetop/owlcarousel', 'Magetop/easytabs']
        },

	}

};
