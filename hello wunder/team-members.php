<?php
/**
 * Plugin Name: Team Members
 * Description: This plugin lets you add team members and display them on block theme.
 * Version: 1.0
 * Author: Eross
 * Text Domain: tmembers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TMembers_Plugin {
    const OPTION_KEY = 'tmembers_options';

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
        add_shortcode( 'team_members', array( $this, 'shortcode_team_members' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // register post meta for REST/API / block compatibility
        add_action( 'init', array( $this, 'register_post_meta_fields' ) );
    }

    public function register_post_type() {
        $labels = array(
            'name' => __( 'Team Members', 'tmembers' ),
            'singular_name' => __( 'Team Member', 'tmembers' ),
            'menu_name' => __( 'Team Members', 'tmembers' ),
            'name_admin_bar' => __( 'Team Member', 'tmembers' ),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'show_in_rest' => true,
            'rewrite' => array( 'slug' => 'team' ),
            'menu_position' => 20,
            'menu_icon' => 'dashicons-groups',
            'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        );

        register_post_type( 'team_member', $args );
    }

    public function register_post_meta_fields() {
        // job_title
        register_post_meta( 'team_member', 'tmembers_job_title', array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );

        // social url
        register_post_meta( 'team_member', 'tmembers_social_url', array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            }
        ) );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'tmembers_meta',
            __( 'Team Member Info', 'tmembers' ),
            array( $this, 'render_meta_box' ),
            'team_member',
            'normal',
            'high'
        );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'tmembers_save_meta', 'tmembers_meta_nonce' );

        $job_title = get_post_meta( $post->ID, 'tmembers_job_title', true );
        $social = get_post_meta( $post->ID, 'tmembers_social_url', true );
        ?>
        <p>
            <label for="tmembers_job_title"><strong><?php _e( 'Job Title', 'tmembers' ); ?></strong></label><br />
            <input type="text" id="tmembers_job_title" name="tmembers_job_title" value="<?php echo esc_attr( $job_title ); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="tmembers_social_url"><strong><?php _e( 'Social Link (LinkedIn or GitHub)', 'tmembers' ); ?></strong></label><br />
            <input type="url" id="tmembers_social_url" name="tmembers_social_url" value="<?php echo esc_attr( $social ); ?>" style="width:100%;" />
        </p>
        <?php
    }

    public function save_meta_boxes( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision( $post_id ) ) return;
        if ( ! isset( $_POST['tmembers_meta_nonce'] ) || ! wp_verify_nonce( $_POST['tmembers_meta_nonce'], 'tmembers_save_meta' ) ) return;
        if ( $post->post_type !== 'team_member' ) return;

        if ( isset( $_POST['tmembers_job_title'] ) ) {
            update_post_meta( $post_id, 'tmembers_job_title', sanitize_text_field( wp_unslash( $_POST['tmembers_job_title'] ) ) );
        } else {
            delete_post_meta( $post_id, 'tmembers_job_title' );
        }

        if ( isset( $_POST['tmembers_social_url'] ) ) {
            $url = esc_url_raw( wp_unslash( $_POST['tmembers_social_url'] ) );
            if ( empty( $url ) ) {
                delete_post_meta( $post_id, 'tmembers_social_url' );
            } else {
                update_post_meta( $post_id, 'tmembers_social_url', $url );
            }
        }
    }

    public function admin_assets( $hook ) {
        // only load on team member edit screens and listing
        global $post_type;
        if ( ( isset( $post_type ) && $post_type === 'team_member' ) || strpos( $hook, 'team-member' ) !== false || $hook === 'post.php' || $hook === 'post-new.php' ) {
            wp_enqueue_style( 'tmembers-admin', plugins_url( 'assets/admin.css', __FILE__ ), array(), '1.0' );
        }
    }

    public function frontend_assets() {
        wp_enqueue_style( 'tmembers-frontend', plugins_url( 'assets/style.css', __FILE__ ), array(), '1.0' );
    }

    public function shortcode_team_members( $atts ) {
        $opts = get_option( self::OPTION_KEY, array( 'per_row' => 3, 'show_social' => 1 ) );

        $atts = shortcode_atts( array(
            'per_row' => isset( $opts['per_row'] ) ? intval( $opts['per_row'] ) : 3,
            'show_social' => isset( $opts['show_social'] ) ? intval( $opts['show_social'] ) : 1,
            'count' => -1,
        ), $atts, 'team_members' );

        $per_row = max(1, min(6, intval( $atts['per_row'] )));
        $show_social = intval( $atts['show_social'] );

        $q = new WP_Query( array(
            'post_type' => 'team_member',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status' => 'publish',
        ) );

        if ( ! $q->have_posts() ) {
            return '<p class="tmembers-empty">' . esc_html__( 'No team members found.', 'tmembers' ) . '</p>';
        }

        ob_start();
        // wrapper using alignwide to match block theme styling
        echo '<div class="wp-block tmembers-block alignwide">';
        printf( '<div class="team-members-grid" style="--tmembers-columns:%d">', esc_attr( $per_row ) );

        while ( $q->have_posts() ) {
            $q->the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $content = get_the_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 30 );
            $job = get_post_meta( $id, 'tmembers_job_title', true );
            $social = get_post_meta( $id, 'tmembers_social_url', true );

            echo '<article class="team-member">';

            if ( has_post_thumbnail() ) {
                $thumb = get_the_post_thumbnail( $id, 'medium', array( 'class' => 'team-member-photo' ) );
                echo '<div class="team-member-photo-wrap">' . $thumb . '</div>';
            } else {
                // placeholder box
                echo '<div class="team-member-photo-wrap team-member-photo-placeholder" aria-hidden="true"><span>' . esc_html__( 'No photo', 'tmembers' ) . '</span></div>';
            }

            echo '<div class="team-member-body">';
            echo '<h3 class="team-member-name">' . esc_html( $title ) . '</h3>';
            if ( $job ) {
                echo '<div class="team-member-job">' . esc_html( $job ) . '</div>';
            }
            if ( $content ) {
                echo '<div class="team-member-bio">' . wp_kses_post( wpautop( $content ) ) . '</div>';
            }
            if ( $show_social && $social ) {
                $label = $this->detect_social_label( $social );
                echo '<div class="team-member-social"><a href="' . esc_url( $social ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a></div>';
            }
            echo '</div>'; // body

            echo '</article>';
        }

        echo '</div>'; // grid
        echo '</div>'; // wrapper

        wp_reset_postdata();

        return ob_get_clean();
    }

    private function detect_social_label( $url ) {
        if ( stripos( $url, 'linkedin.com' ) !== false ) return 'LinkedIn';
        if ( stripos( $url, 'github.com' ) !== false ) return 'GitHub';
        if ( stripos( $url, 'twitter.com' ) !== false ) return 'Twitter';
        return 'Profile';
    }

    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=team_member',
            __( 'Team Members Settings', 'tmembers' ),
            __( 'Settings', 'tmembers' ),
            'manage_options',
            'tmembers-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'tmembers_settings_group', self::OPTION_KEY, array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_options' ) ) );
    }

    public function sanitize_options( $input ) {
        $out = array();
        $out['per_row'] = isset( $input['per_row'] ) ? intval( $input['per_row'] ) : 3;
        $out['show_social'] = isset( $input['show_social'] ) ? intval( $input['show_social'] ) : 1;
        return $out;
    }

    public function render_settings_page() {
        $opts = get_option( self::OPTION_KEY, array( 'per_row' => 3, 'show_social' => 1 ) );
        ?>
        <div class="wrap">
            <h1><?php _e( 'Team Members Settings', 'tmembers' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'tmembers_settings_group' ); ?>
                <?php do_settings_sections( 'tmembers_settings_group' ); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="tmembers_per_row"><?php _e( 'Members per row', 'tmembers' ); ?></label></th>
                        <td>
                            <select id="tmembers_per_row" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[per_row]">
                                <?php for ( $i = 1; $i <= 6; $i++ ) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected( $opts['per_row'], $i ); ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <p class="description"><?php _e( 'Choose how many members are displayed per row by default.', 'tmembers' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( 'Show social links', 'tmembers' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[show_social]" value="1" <?php checked( 1, $opts['show_social'] ); ?> /> <?php _e( 'Display social links on the frontend', 'tmembers' ); ?></label>
                        </td>
                    </tr>

                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new TMembers_Plugin();

// Simple CSS assets bundled inline fallback (if files not present) so plugin works when dropped in:
add_action( 'init', function() {
    // create asset files if they don't exist? We will provide fallback inline styles via wp_add_inline_style on frontend enqueue
    add_action( 'wp_enqueue_scripts', function() {
        if ( ! wp_style_is( 'tmembers-frontend', 'enqueued' ) ) return;

        $css = "
.team-members-grid { display: grid; gap: 2rem; grid-template-columns: repeat(var(--tmembers-columns,3), 1fr); }
.team-member { background: var(--wp--preset--color--background, #fff); border-radius: 12px; padding: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; align-items: center; text-align: center; }
.team-member-photo-wrap { width: 100%; max-width: 220px; height: 220px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 12px; margin: 0 auto 1rem; }
.team-member-photo { width: 100%; height: 100%; object-fit: cover; }
.team-member-photo-placeholder { background: #f3f3f3; color: #666; }
.team-member-name { margin: 0 0 .25rem; font-size: 1.1rem; }
.team-member-job { font-size: 0.9rem; color: #666; margin-bottom: .5rem; }
.team-member-bio { font-size: .95rem; color: #333; }
.team-member-social { margin-top: .75rem; }
.team-member-social a { text-decoration: none; font-weight: 600; }
@media (max-width: 800px) { .team-members-grid { grid-template-columns: repeat(1,1fr); } }
";
        wp_add_inline_style( 'tmembers-frontend', $css );
    } );

    add_action( 'admin_enqueue_scripts', function() {
        if ( ! wp_style_is( 'tmembers-admin', 'enqueued' ) ) return;
        $css = ".tmembers-metabox p { margin-bottom: 1rem; }";
        wp_add_inline_style( 'tmembers-admin', $css );
    } );
});

?>