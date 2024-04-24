<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //die(header("Location: $BASE_PATH" . "/home.php"));
    redirect("home.php");
}
?>

<?php
/* yc73 4/12/23 */
/* fetching id */
$id = se($_GET, "id", -1, false);


$product = [];
if ($id > -1) {
    /* yc73 4/12/23 */
    //fetch
    $db = getDB();
    //query
    $query = "SELECT id, api_id, name, price, measurement, typeName, image, contextualImageUrl, imageAlt, url, categoryPath, stock, is_api, created, modified FROM `Products` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $product = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    //die(header("Location:" . get_url("admin/list_products.php")));
    redirect("admin/list_products.php");
}
foreach ($product as $key => $value) {
    if (is_null($value)) {
        $product[$key] = "N/A";
    }
}
//TODO handle manual create stock
?>
<div class="container-fluid viewProd-whole">
    <div>
        <a href="<?php echo get_url("admin/list_products.php"); ?>" class="viewProd-back btn btn-secondary">Back</a>
    </div>
    <div class="container-fluid viewProd-content">
        <h3  class="viewProd-title">Product: <?php se($product, "name", "Unknown"); ?></h3>
        <!-- https://i.kym-cdn.com/entries/icons/original/000/029/959/Screen_Shot_2019-06-05_at_1.26.32_PM.jpg -->
        <!-- <div class="card mx-auto" style="width: 60rem;"> -->
            
        <!-- yc73 4-15-23 -->
        <div class="row mt-4">
            <div class="col-md-6">
                <section id="carousel" class="gallery-carousel pt-3 bg-light w-50 mx-auto">
                    <div class="container">
                        <div id="carouselExampleIndicators" class="carousel slide" data-bs-interval="false" data-bs-ride="false" data-pause="hover">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="<?php se($product, "image", "Unknown"); ?>" class="w-100" alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="<?php se($product, "contextualImageUrl", "Unknown"); ?>" class="w-100" alt="...">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-6" style="margin-left: -50px;">
                <div class="text">
                    <h5 class="title"><?php se($product, "name", "Unknown"); ?></h5>
                    <ul class="list-group">
                        <li class="list-group-item">Name: <?php se($product, "name", "Unknown"); ?></li>
                        <li class="list-group-item">Price: <?php se($product, "price", "Unknown"); ?></li>
                        <li class="list-group-item">Measurement: <?php se($product, "measurement", "Unknown"); ?></li>
                        <li class="list-group-item">Stock: <?php se($product, "stock", "Unknown"); ?></li>
                        <li class="list-group-item">Type: <?php se($product, "typeName", "Unknown"); ?></li>
                        <li class="list-group-item">Category: <?php se($product, "categoryPath", "Unknown"); ?></li>
                        <li class="list-group-item">Url: <a href="<?php se($product, "url", "Unknown"); ?>" target="_blank"><?php se($product, "url", "Unknown"); ?></a> </li>
                    </ul>
                    <div class="row mt-3">
                        <div class="col-md-3 viewProd-edit">
                            <a href="<?php echo get_url("admin/edit_product.php?id=" . $id); ?>" class="btn btn-secondary">Edit</a>
                        </div>
                        <div class="col-md-3 viewProd-delete">
                            <a href="<?php echo get_url("admin/delete_product.php?id=" . $id); ?>" class="btn btn-secondary">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
</script>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>