var config = {
		map: {
		'*': {
			mcp_responsive_menu : 'Magetop_Menupro/js/responsive_menu'
			}
		},
		paths: {
			mcp_sidr : 'Magetop_Menupro/js/jquery.sidr.min',
			mcp_touchwipe : 'Magetop_Menupro/js/jquery.touchwipe.1.1.1',
			mcp_modernizr : 'Magetop_Menupro/pushnew/js/modernizr.custom',
			mcp_mlpushmenu : 'Magetop_Menupro/pushnew/js/mlpushmenu',
			mcp_push_modernizr : 'Magetop_Menupro/push/js/modernizr.custom'
		},	
		shim: {
			'mcp_touchwipe' : {
				'deps': ['jquery']
			},
			'mcp_responsive_menu' : {
				'deps': ['jquery']
			}		
		}
};