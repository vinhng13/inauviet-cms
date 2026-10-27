<?php
/**
 * Title: Trang chủ – Mẫu đã làm
 * Slug: auviet/home-projects
 * Categories: auviet
 */
?>
<!-- wp:group {"tagName":"section","align":"wide","className":"av-section","layout":{"type":"default"}} -->
<section class="wp-block-group alignwide av-section"><!-- wp:group {"className":"av-section__head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group av-section__head"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Mẫu đã làm</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"av-intro"} -->
<p class="av-intro">Ảnh thật từ các đơn hàng đã giao.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph -->
<p><a href="/mau-da-lam/">Xem tất cả mẫu</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":22,"query":{"perPage":3,"pages":0,"offset":0,"postType":"av_project","order":"desc","orderBy":"date","inherit":false},"className":"av-cards av-cards--projects"} -->
<div class="wp-block-query av-cards av-cards--projects"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->

<!-- wp:post-title {"level":3,"isLink":true} /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Mẫu đang được cập nhật.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></section>
<!-- /wp:group -->
