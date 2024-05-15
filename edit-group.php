<?php if ( ! defined( 'ABSPATH' ) ) exit;

// Check group ID
if ( !isset($_GET['group']) || !is_numeric($_GET['group']) ) {
    exit();
} else {
    $group_id = (int) wp_unslash( $_GET['group'] );
} ?>

<div class="wrap">
    
    <?php // Group
    global $wpdb;
    $name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}contentlock_groups WHERE id = %d", $group_id));
    if ( $name === null ) {
        echo "<div class='error'><p>Sorry, there's no group with ID #" . esc_attr($group_id) . ".</p></div>";
        exit;
    }

    // Datatable with enabled emails
    $table = $wpdb->prefix . "contentlock_emails";

    // Delete existing group
    if ( isset($_GET['action']) && $_GET['action'] === 'delete_row' ) {
        $row_id = isset($_GET['row_id']) ? intval($_GET['row_id']) : 0;
        if ($row_id > 0) {
            $wpdb->delete($table, array('id' => $row_id));
            echo '<div class="updated"><p>Email deleted successfully!</p></div>';
        }
    }
    
    // POST method form
    if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {

        // Rename Group
        if ( isset($_POST['post_title']) && !empty($_POST['post_title']) && $name !== $_POST['post_title'] ) {
            $new_name = sanitize_text_field($_POST['post_title']);
            $rename_group = $wpdb->update( $wpdb->prefix . "contentlock_groups", array('name' => $new_name), array('id' => $group_id) );
            if ( $rename_group !== false ) {
                $name = $new_name;
                echo '<div class="updated"><p>Group renamed.</p></div>';
            } else {
                echo '<div class="error"><p>New name is not valid!</p></div>';
            }
        }

        // Add new email address
        if ( isset($_POST['add_email']) && isset($_POST['new_email']) ) {
            $new_email = sanitize_text_field($_POST['new_email']);
            if ( is_email($new_email) ) {
                // Check email with "group_id"
                $existing_email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}contentlock_emails WHERE email = %s AND group_id = %s", $new_email, $group_id));
                if ( $existing_email === null ) {
                    $wpdb->insert($table, array('email' => $new_email, 'group_id' => $group_id));
                    echo '<div class="updated"><p>New email added successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-warning"><p>Email already exists in this group.</p></div>';
                }
            } else {
                echo '<div class="error"><p>Email is not valid!</p></div>';
            }
        }

    }
    
    // Get all emails in the group
    $table = $wpdb->prefix . 'contentlock_emails';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_id = %d", $group_id), ARRAY_A);
    
    // Number of items
    $num_results = count($results);

    if ($name !== null) { ?>
        <h1 class="wp-heading-inline">Edit Email Group: <strong><?php echo esc_html($name); ?></strong></h1>
        <form method="post">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div id="titlediv">
                            <div id="titlewrap">
                                <label class="screen-reader-text" id="title-prompt-text" for="title">Enter title here</label>
                                <input type="text" name="post_title" size="30" value="<?php echo esc_html($name); ?>" id="title" spellcheck="true" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="postbox">
                            <h3>Rename Group</h3>
                            <div class="inside">
                                <input type="submit" name="add_email" value="Rename" class="button button-secondary action">
                            </div>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </form>
    <?php } ?>

    <form method="post">

        <h2>Add new Email for Group</h2>

        <input type="email" name="new_email" required>
        <input type="submit" name="add_email" value="Add Email" class="button button-primary action">

        <?php if ( $num_results > 0 ) { ?>
            <div class="tablenav top">
                <div class="tablenav-pages one-page">
                    <span class="displaying-num"><?php echo esc_attr($num_results); ?> <?php echo ($num_results > 1) ? 'emails' : 'email'; ?></span>
                </div>
                <br class="clear">
            </div>
        <?php } ?>

        <h2>Emails in Group</h2>
        
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Action</th>
                    <th>ID</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($results) {
                foreach ($results as $row) { ?>
                    <tr>
                        <td><strong><span class="row-title"><?php echo esc_attr($row['email']); ?></span></strong></td>
                        <td><a href="<?php echo esc_url(add_query_arg(array('action' => 'delete_row', 'row_id' => $row['id']))); ?>" onclick="return confirm('Are you sure you want to delete this email?')">Delete</a></td>
                        <td><?php echo esc_attr($row['id']); ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr class="no-items">
                    <td colspan="4">No Emails found.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php if ( $num_results > 0 ) { ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages one-page">
                    <span class="displaying-num"><?php echo esc_attr($num_results); ?> <?php echo ($num_results > 1) ? 'emails' : 'email'; ?></span>
                </div>
                <br class="clear">
            </div>
        <?php } ?>
        
    </form>    

</div>