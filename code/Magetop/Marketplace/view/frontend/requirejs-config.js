var config = {
	map: {
		'*': {
			productGallery: 'Magetop_Marketplace/js/product-gallery',
		    baseImage:          'Magetop_Marketplace/catalog/base-image-uploader',
			newVideoDialog:  'Magetop_Marketplace/js/new-video-dialog',
			openVideoModal:  'Magetop_Marketplace/js/video-modal',
			productAttributes:  'Magetop_Marketplace/catalog/product-attributes',
			groupedProduct: 'Magetop_Marketplace/js/grouped-product',
			separateSellerProductList: 'Magetop_Marketplace/js/product/separate-seller-product-list',
			mapChart: 'Magetop_Marketplace/js/chart',
            momentjs: 'Magetop_Marketplace/assets/global/plugins/moment',
            daterangepicker: 'Magetop_Marketplace/assets/global/plugins/bootstrap-daterangepicker/daterangepicker',
            "Magento_Customer/js/view/authentication-popup": "Magetop_Marketplace/js/view/authentication-popup",
            bootstrapjs: "Magetop_Marketplace/assets/global/plugins/bootstrap/js/bootstrap",
		}
	},
    bundles: {
        "Magetop_Marketplace/js/theme": [
            "modalPopup",
            "useDefault",
            "collapsable"
        ]
    }	
}