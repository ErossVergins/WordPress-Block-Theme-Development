<?php
/**
 * Title: Hero Section
 * Slug: your-theme/hero
 * Categories: featured
 * Description: A hero with heading and button.
 */
?>

<!-- wp:cover {"overlayColor":"primary","minHeight":300,"align":"full"} -->
<div class="wp-block-cover alignfull" style="min-height:300px">
  <div class="wp-block-cover__inner-container">
    <!-- wp:heading {"textAlign":"center","level":1,"textColor":"black"} -->
    <h1 class="has-text-align-center has-black-color has-text-color">Welcome to My Awesome Traveling Blog</h1>
    <!-- /wp:heading -->

    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
    <div class="wp-block-buttons">
      <!-- wp:button {"backgroundColor":"secondary"} -->
      <div class="wp-block-button">
        <a class="wp-block-button__link has-secondary-background-color has-background">Get Started</a>
      </div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
</div>
<!-- /wp:cover -->
