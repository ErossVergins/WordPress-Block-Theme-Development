<?php
/**
 * Title: Team Section
 * Slug: mytheme/team-section
 * Categories: team
 */
?>

<!-- Add inline CSS specific to this pattern -->
<style>
.team-section img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    display: block;
    margin-left: auto;
    margin-right: auto;
}
</style>

<!-- wp:group {"align":"wide","className":"team-section"} -->
<div class="wp-block-group alignwide team-section">
  <!-- wp:heading {"textAlign":"center","level":2} -->
  <h2 class="has-text-align-center">Meet Our Team</h2>
  <!-- /wp:heading -->

  <!-- wp:columns -->
  <div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:image {"sizeSlug":"medium"} -->
      <figure class="wp-block-image size-medium">
        <img src="https://www.sciencebuddies.org/7zLYmn_ONTUkBcZrOUKRf7u7RMc=/640x480/-/https/careerdiscovery.sciencebuddies.org/cdn/Files/19713/5/web-developer-designer-main.jpg" alt=""/>
      </figure>
      <!-- /wp:image -->
      <!-- wp:heading {"level":3,"textAlign":"center"} -->
      <h3 class="has-text-align-center">Jane Doe</h3>
      <!-- /wp:heading -->
      <!-- wp:paragraph {"align":"center"} -->
      <p class="has-text-align-center">Designer</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:image {"sizeSlug":"medium"} -->
      <figure class="wp-block-image size-medium">
        <img src="https://bairesdev.mo.cloudinary.net/blog/2022/08/portrait-of-a-man-using-a-computer-in-a-modern-office-picture-id1344688156-1.jpg?tx=w_1920,q_auto" alt=""/>
      </figure>
      <!-- /wp:image -->
      <!-- wp:heading {"level":3,"textAlign":"center"} -->
      <h3 class="has-text-align-center">John Smith</h3>
      <!-- /wp:heading -->
      <!-- wp:paragraph {"align":"center"} -->
      <p class="has-text-align-center">Developer</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:image {"sizeSlug":"medium"} -->
      <figure class="wp-block-image size-medium">
        <img src="https://www.shutterstock.com/image-photo/young-smart-busy-professional-business-600nw-2223351329.jpg" alt=""/>
      </figure>
      <!-- /wp:image -->
      <!-- wp:heading {"level":3,"textAlign":"center"} -->
      <h3 class="has-text-align-center">Anna Lee</h3>
      <!-- /wp:heading -->
      <!-- wp:paragraph {"align":"center"} -->
      <p class="has-text-align-center">Marketing</p>
      <!-- /wp:paragraph -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->
