require([
    "jquery",
    "Magento_Cart/js/action/add-to-cart",
    "Magento_Customer/js/model/customer",
    "Magento_Checkout/js/model/checkout-data",
], function ($, addToCartAction, customer, checkoutData) {
    $("#buy-now-button").on("click", function () {
        var productId = $('input[name="product"]').val(); // Get product ID
        addToCartAction({
            product: productId,
            qty: 1,
        }).done(function () {
            window.location.href = "/checkout"; // Redirect to checkout
        });
    });
});
