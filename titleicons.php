<?php
/*
Plugin Name: Title Icons
Plugin URI: http://github.com/kevinpsiu/wp-titleicons
Description: Adding icons to your post titles to spice things up
Version: 0.1
Author: Kevin P. Siu
Author URI: http://kevinpsiu.ca
License: GPL v2
*/


/**********************************************************
    Get/Update/Delete the icon meta data for a given post
***********************************************************/
function title_icon( $post_id, $action = 'get', $iconfile = '') {

    $defaulticon = get_option('titleicons_defaulticon', 'addressbook.png');

    switch ($action) {
        case 'update' :
            if( ! $iconfile )
            //If nothing is given to update, end here
            return false;

            if( $iconfile ) {
                add_post_meta( $post_id, 'iconfile', $iconfile, true ) or
                    update_post_meta( $post_id, 'iconfile', $iconfile );
                return true;
            }
        case 'delete' :
            delete_post_meta( $post_id, 'iconfile' );
        break;
        case 'get' :
            $stored_iconfile = get_post_meta( $post_id, 'iconfile', 'true' );

            if ($stored_iconfile)
                return $stored_iconfile;
            else
                return $defaulticon;

        default :
          return false;
        break;
    } //end switch
} //end function


/*********************************
        Add icon to title
*********************************/
add_filter('the_title', 'add_title_icon', 1, 2);

function add_title_icon($title, $id){
    //Only display icon in "the loop" and not in admin pages
    if ( in_the_loop() && !is_admin() ) {
        return '<img src="'.plugins_url('icons/', __FILE__ ).title_icon($id).'" class="titleicon" style="height:'.get_option('titleicons_iconheight', '32').'px"/> ' . $title;
    }
    else
        return $title;
}


/***********************
    POSTS SIDE PANEL
************************/
add_action( 'add_meta_boxes', 'titleicons_meta_boxes' );
add_action( 'save_post', 'titleicons_save_icon' );
add_action( 'admin_enqueue_scripts', 'titleicons_enqueue_admin_script' );

//Create meta box for posts admin page
function titleicons_meta_boxes() {
    add_meta_box(
            'titleicons-metabox',
            __('Title Icon'),
            'titleicons_innerbox',
            'post',
            'side',
            'high'
    );  
}

//Create the inside box for the meta box
function titleicons_innerbox ($post){
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'titleicons_nonce' );
    
    $current_icon = title_icon($post->ID);
    echo '<div id="titleicons-currenticoncontainer">';
    echo '<input type="hidden" id="titleicons-iconfilename" name="titleicons-iconfilename" value="'
        . $current_icon . '" size="25" />';
    echo 'Current Icon:';
    echo '<img src="'.plugins_url('icons/', __FILE__ ).$current_icon.'" id="titleicons-currenticon" />';
    echo '</div>';
    
    //Get all the icons from the directory and display them
    $filenames = scandir(plugin_dir_path(__FILE__).'icons');

    echo '<div id="titleicons-iconscontainer">';
    foreach ($filenames as $icon){
        if ($icon != '.' && $icon != '..'){
            echo '<div class="titleicons-iconbox" alt="'.$icon.'">';
            echo '<img src="'.plugins_url('icons/', __FILE__ ).$icon.'" class="titleicons-iconpicker" alt="'.$icon.'"/>';
            echo '</div>';
        }
    }
    echo '</div>';
}

//Save icon metadata with page save
function titleicons_save_icon ( $post_id ){
    if ( !wp_verify_nonce( $_POST['titleicons_nonce'], plugin_basename( __FILE__ ) ) )
        return;

    // Check permissions
    if ( 'page' == $_POST['post_type'] ){
        if ( !current_user_can( 'edit_page', $post_id ) )
            return;
    }
    else {
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
    }

    title_icon($post_id, 'update', $_POST['titleicons-iconfilename']);
}

//Enqeue the JS and CSS files for the relevant posts pages
function titleicons_enqueue_admin_script($hook) {
    if ($hook != 'post.php' && $hook != 'post-new.php')
        return;

    wp_enqueue_script( 'titleicons-panel', plugins_url('titleicons-panel.js', __FILE__) );
    wp_register_style( 'titleicons-panel', plugins_url('titleicons-panel.css', __FILE__) );
    wp_enqueue_style( 'titleicons-panel' );
}

/***********************
    ADMIN OPTIONS PANEL
************************/


add_action('admin_menu', 'my_admin_menu');

function my_admin_menu() {
    add_options_page( 'Title Icons Settings', 'Title Icons', 'manage_options', 'titleicons', 'titleicons_options' );
    add_action( 'admin_init', 'register_titleicons_settings' );
 }

function register_titleicons_settings() {
    register_setting( 'titleicons-general', 'titleicons_defaulticon' );
 } 

function titleicons_options() { 

?>

<div class="wrap">
    <?php screen_icon(); ?>
    <h2>Title Icons Options</h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'titleicons-general' ); ?>
        
        <div style="">
            <h3>Select a Default Icon:</h3>

            <?php
                $filenames = scandir(plugin_dir_path(__FILE__).'icons');
                foreach ($filenames as $icon){
                    if ($icon != '.' && $icon != '..'){
                        echo '<div style="height:32px;width:50px;display:inline-block;">';
                        echo '<input type="radio" name="titleicons_defaulticon" value="'.$icon.'" ';
                        echo ((get_option('titleicons_defaulticon')==$icon)?'checked="checked"':'');
                        echo ' style="margin-right:5px;margin-top:-5px;"/>';
                        echo '<img src="'.plugins_url('icons/', __FILE__ ).$icon.'" class="titleicons-iconpicker" alt="'.$icon.'" style="max-width:28px"/>';
                        echo '</div>';
                    }
                }
            ?>

        </div>

        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>

<?php } 
?>