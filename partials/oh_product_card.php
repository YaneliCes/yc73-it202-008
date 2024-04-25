<?php
/* yc73 4/25/23 */
/* product card for order history page */
if (!isset($product)) {
    error_log("Using Product partial without data");
    flash("Dev Alert: Product called without data", "danger");
}
?>
<?php if (isset($product)) : ?>
    <!-- https://i.kym-cdn.com/entries/icons/original/000/029/959/Screen_Shot_2019-06-05_at_1.26.32_PM.jpg -->
    <div class="card mx-auto" style="width: 16rem;">
        <a href="<?php echo get_url('view_product_customer.php?id=' . $product["id"]); ?>"> <img src="<?php se($product, "image", "Unknown"); ?>" class="w-100" alt="<?php se($product, "imageAlt", "Unknown") ?>"> </a>
        <!--<img src="<?php //se($product, "image", "Unknown"); ?>" class="w-100" alt="<?php //se($product, "imageAlt", "Unknown") ?>">-->
        <div class="card-body">
            <h5 class="card-title"><?php se($product, "name", "Unknown"); ?> </h5>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">Name: <?php se($product, "name", "Unknown"); ?></li>
                    <li class="list-group-item">Price: <?php se($product, "price", "Unknown"); ?></li>
                    <li class="list-group-item">Type: <?php se($product, "typeName", "Unknown"); ?></li>
                </ul>
            </div>
            <div class="card-body">
            
                <?php if (!isset($product["user_id"]) || $product["user_id"] === "N/A") : ?>
                        <a href="<?php echo get_url('view_product_customer.php?id=' . $product["id"]); ?>" class="btn btn-secondary">View</a>
                        <a href="<?php echo get_url('api/purchase_product.php?product_id=' . $product["id"]); ?>" class="btn btn-primary">Purchase</a>

                <?php else : ?>
                        <a href="<?php echo get_url('view_product_customer.php?id=' . $product["id"]); ?>" class="btn btn-secondary">View</a>
                        <a href="<?php echo get_url('api/delete_product.php?product_id=' . $product["id"]); ?>" onclick="confirm('Are you sure')?'':event.preventDefault()" class="btn btn-danger">Return</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
<?php endif; ?>