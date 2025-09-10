function addToCartAjax(
    productId,
    productName,
    productImage,
    productPrice,
    maxQuantity
) {
    var quantityInput = document.getElementById("quantity");
    var quantity = parseInt(quantityInput.value);

    // Kiểm tra số lượng trước khi gọi AJAX
    if (quantity > maxQuantity) {
        alert(
            `Số lượng tồn chỉ còn ${maxQuantity}. Không thể thêm ${quantity} sản phẩm.`
        );
        quantityInput.value = maxQuantity;
        return;
    }

    // Gọi AJAX thêm vào giỏ hàng
    $.ajax({
        url: BASE_PATH + "/Controller/CartController.php?action=add",
        type: "POST",
        data: {
            MASANPHAM: productId,
            TENSANPHAM: productName,
            HINHANH: productImage,
            GIABAN: productPrice,
            SoLuong: quantity,
        },
        success: function (response) {
            if (response.success) {
                $(".cart-count").text(response.cartCount);
            } else {
                alert(response.message);
                quantityInput.value = maxQuantity;
            }
        },
        error: function () {
            alert("Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.");
        },
    });
}

// Giới hạn số lượng input
document.addEventListener("DOMContentLoaded", function () {
    var input = document.getElementById("quantity");
    if (input) {
        input.addEventListener("input", function () {
            var maxQuantity = parseInt(this.getAttribute("max"));
            validateQuantity(this, maxQuantity);
        });
    }
});

function validateQuantity(input, maxQuantity) {
    var value = parseInt(input.value);
    if (value > maxQuantity) {
        alert(`Số lượng tồn chỉ còn ${maxQuantity}. Không thể nhập thêm.`);
        input.value = maxQuantity;
    } else if (value < 1 || isNaN(value)) {
        input.value = 1;
    }
}
