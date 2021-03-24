<?php
/**
 * Plugin Name:       WPDB Demo
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.10.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Abdullah Mahi
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpdb-demo
 * Domain Path:       /languages
 */

if ( ! defined("ABSPATH")) {
    exit;
}
define( "WPDB_DEMO_VERSION", 1.12 );

function wpdb_demo_init() {
    global $wpdb;
    $table_name = $wpdb->prefix."person";
    $sql        = "CREATE TABLE {$table_name} (
                    id INT NOT NULL AUTO_INCREMENT,
                    name VARCHAR(250),
                    email VARCHAR(250),
                    phone INTEGER (25),
                    PRIMARY KEY (id)
                );";
    require_once ( ABSPATH.'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    add_option( 'wpdb_demo_version', WPDB_DEMO_VERSION );

    if ( get_option( 'wpdb_demo_version') != WPDB_DEMO_VERSION ) {
        $sql        = "CREATE TABLE {$table_name} (
                    id INT NOT NULL AUTO_INCREMENT,
                    name VARCHAR(250),
                    email VARCHAR(250),
                    phone INT(25),
                    home VARCHAR(250),
                    PRIMARY KEY (id)
                );";
        require_once ( ABSPATH.'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        update_option( 'wpdb_demo_version', WPDB_DEMO_VERSION );
    }
}
register_activation_hook( __FILE__, 'wpdb_demo_init' );

function wpdb_demo_drop_cloumn() {
    global $wpdb;
    $table_name = $wpdb->prefix."person";
    if ( get_option( 'wpdb_demo_version') != WPDB_DEMO_VERSION ) {
        $query = "ALTER TABLE {$table_name} DROP COLUMN home";
        $wpdb->query($query);
        update_option('wpdb_demo_version', WPDB_DEMO_VERSION);
    }

}
add_action( 'plugins_loaded', 'wpdb_demo_drop_cloumn' );

function wpdb_insert_data() {
    global $wpdb;
    $table_name = $wpdb->prefix."person";
    $wpdb->insert($table_name, [
        'name'  => 'Abdullah al Mahi',
        'email' => 'mahi@abdullahmahi.com',
        'phone' => '01751989173',
    ]);
    $wpdb->insert($table_name, [
        'name'  => 'Nishpa kha',
        'email' => 'nishpa@abdullahmahi.com',
        'phone' => '01751989173',
    ]);
}
register_activation_hook( __FILE__, 'wpdb_insert_data' );
function wpdb_flash_data() {
    global $wpdb;
    $table_name = $wpdb->prefix."person";
    $query = "TRUNCATE TABLE {$table_name}";
    $wpdb->query($query);
}
register_deactivation_hook( __FILE__, 'wpdb_flash_data' );

function wpdb_demo_menu() {
    add_menu_page(
        __( "WPDB Demo", 'wpdb-demo' ),
        __( "WPDB Demo", 'wpdb-demo' ),
        'manage_options',
        'wpdb-demo',
        'wp_demo_menu_page',
        'dashicons-database',
        '35'
    );
}

function wp_demo_menu_page(){
    global $wpdb;
    $table_name = $wpdb->prefix."person";
    $id         = $_GET['pid'] ?? 0;
    $id         = sanitize_key( $id );
    if ( $id ) {
        $result = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE id='{$id}'");
        if ( $result ) {
            echo "<h2> WPDB DEMO<br/></h2>";
            echo "<h4> Name  : {$result->name} <br/></h4>";
            echo "<h4> Email : {$result->email} <br/></>";
            echo "<h4>Phone : {$result->name} <br/></>";
        }
     } else {
        $results = $wpdb->get_results( "SELECT * FROM {$table_name}");
        if ( $results) {
            echo "<h2> WPDB DEMO<br/></h2>";
            foreach ($results as $result) {
                echo "<h4> Name  : {$result->name} <br/></h4>";
                echo "<h4> Email : {$result->email} <br/></>";
                echo "<h4>Phone : {$result->phone} <br/></>";
                echo "<hr/>";
            }
        }
    }
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e( "Add New Person", 'training' );?></h1>
        <hr/>
        <div class="notice notice-success is-dismissible">
            <p> <?php echo "say HI"; ?></p>
        </div>
        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
            <?php
                if ($id) {
                    echo '<input type="hidden" name="action" value="'.$id.'">';
                }else {
                   echo '<input type="hidden" name="action" value="wpdb_demo_add_record">';
                }
            ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e( "Name", 'training' );?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" value="<?php if ($id) echo $result->name; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="phone"><?php _e( "Phone", 'training' );?></label>
                    </th>
                    <td>
                        <input type="number" name="phone" id="phone" class="regular-text" value="<?php if ($id) echo $result->phone; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="email"><?php _e( "Email", 'training' );?></label>
                    </th>
                    <td>
                        <input type="email" name="email" id="email" class="regular-text" value="<?php if ($id) echo $result->email; ?>">
                    </td>
                </tr>
                </tbody>
            </table>

            <?php if ($id) {
                wp_nonce_field( 'wpdb_demo_nonce', 'wpdemo_nonce' );
                submit_button( __("Update Person"), 'primary', 'submit_wpdb_demo', true, null );
            } else {
                wp_nonce_field( 'wpdb_demo_nonce', 'wpdemo_nonce' );
                submit_button( __("Add Person"), 'primary', 'submit_wpdb_demo', true, null );
            }
            ?>
        </form>
    </div>
<?php

//    if ( isset($_POST['submit_wpdb_demo']) ) {
//        $nonce = sanitize_text_field( $_POST['wpdemo_nonce'] ) ;
//        if ( wp_verify_nonce( $nonce, 'wpdb_demo_nonce' ) ) {
//            $name = sanitize_text_field( $_POST['name'] );
//            $email = sanitize_text_field( $_POST['email'] );
//            $phone = sanitize_text_field( $_POST['phone'] );
//
//            $wpdb->insert( $table_name, [ 'name'=>$name, 'email'=>$email, 'phone'=>$phone ] );
//        } else {
//            wp_die("Data inset failed" );
//        }
//    }
}
add_action('admin_menu', 'wpdb_demo_menu' );

function admin_post_add() {
    global $wpdb;
    $table_name = $wpdb->prefix."person";
    if ( isset($_POST['submit_wpdb_demo']) ) {
        if ( !empty($_POST['name'] ) & !empty($_POST['email']) & !empty($_POST['phone'] ) ) {
            $nonce = sanitize_text_field($_POST['wpdemo_nonce']);
            if (wp_verify_nonce($nonce, 'wpdb_demo_nonce')) {
                $name  = sanitize_text_field($_POST['name']);
                $email = sanitize_text_field($_POST['email']);
                $phone = sanitize_text_field($_POST['phone']);
                $id    = sanitize_text_field($_POST['id']);
                if ($id) {
                    var_dump("hello");
                    $wpdb->update( $table_name, ['name' => $name, 'email' => $email, 'phone' => $phone], ['id'=>$id] );
                }else {
                    $wpdb->insert( $table_name, ['name' => $name, 'email' => $email, 'phone' => $phone] );
                }
            } else {
                wp_die("Data inset failed");
            }
        }else {
            $empty = "Name/email/phone must required";
        }
    }
    wp_redirect( admin_url( 'admin.php?page=wpdb-demo' ) );
}
add_action('admin_post_wpdb_demo_add_record', 'admin_post_add');