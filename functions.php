<?php
add_theme_support( 'post-thumbnails'); 

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_script('api-posts', get_template_directory_uri() . '/js/api-posts.js', array(), null, true);
});

add_action('rest_api_init', function () {
  register_rest_route('intern/v1', '/hello/', array(
    'methods' => 'GET',
    'callback' => function () {
      return array('message' => 'Part 3 done');
    },
  ));
});

add_action('rest_api_init', function () {
  register_rest_route('intern/v1', '/latest-posts/', array(
    'methods' => 'GET',
    'callback' => function () {
      $recent_posts = wp_get_recent_posts(array(
        'numberposts' => 5,
        'post_status' => 'publish'
      ));

      $data = array();
      foreach ($recent_posts as $post) {
        $data[] = array(
          'title' => $post['post_title'],
          'link' => get_permalink($post['ID'])
        );
      }

      return $data;
    },
  ));
});

?>
