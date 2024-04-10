var config = {
		map: {
		'*': {
				mcp_mbjs : 'Magetop_Menupro/js/magetop',
				mcp_sitemap : 'Magetop_Menupro/js/sitemap',
			}		
		},
		shim: {
				'mcp_sitemap' : {
					'deps': ['jquery','jquery/ui']
				},
				'mcp_mbjs' : {
					'deps': ['jquery']
				},
		}
};