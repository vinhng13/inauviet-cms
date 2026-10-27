<?php
/**
 * Title: Trang chủ – Sản phẩm được đặt nhiều
 * Slug: auviet/home-products
 * Categories: auviet
 */
?>
<!-- wp:group {"tagName":"section","align":"wide","className":"av-section","layout":{"type":"default"}} -->
<section class="wp-block-group alignwide av-section"><!-- wp:group {"className":"av-section__head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group av-section__head"><!-- wp:heading -->
<h2 class="wp-block-heading">Sản phẩm được đặt nhiều</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a href="/san-pham/">Xem tất cả sản phẩm</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":21,"query":{"perPage":8,"pages":0,"offset":0,"postType":"av_product","order":"asc","orderBy":"menu_order","inherit":false},"className":"av-cards"} -->
<div class="wp-block-query av-cards"><!-- wp:post-template {"layout":{"type":"grid","columnCount":4}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1"} /-->

<!-- wp:post-title {"level":3,"isLink":true} /-->

<!-- wp:auviet/price-badge /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Sản phẩm đang được cập nhật.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></section>
<!-- /wp:group -->
