var config = {
    map: {
        '*': {
            mcolorpicker: "Magetop_Themes/js/mcolorpicker",
            themes: "Magetop_Themes/js/themes",
        },
    },

	paths: {
		'mcolorpicker'	: 'Magetop_Themes/js/mcolorpicker',
		'themes'		: 'Magetop_Themes/js/themes',
	},

	shim: {
		'mcolorpicker': {
			deps: ['jquery']
		},
		'themes': {
			deps: ['jquery', 'mcolorpicker']
		},

	}

};