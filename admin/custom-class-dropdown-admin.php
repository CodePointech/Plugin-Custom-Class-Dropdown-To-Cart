<?php


add_action('admin_menu', 'class_dropdown_admin_menu');
function class_dropdown_admin_menu() {
    add_menu_page(
        __('CP Technologies', 'custom_class_dropdown_to_cart'),
        __('CP Technologies', 'custom_class_dropdown_to_cart'),
        'manage_options',                                      
        'cp-technologies',                                   
        '',                                                 
        'dashicons-admin-tools',                              
        20                                               
    );

    add_submenu_page(
        'cp-technologies',                                    
        __('Class Dropdown Option Cart', 'custom_class_dropdown_to_cart'),
        __('Class Dropdown Option Cart', 'custom_class_dropdown_to_cart'),
        'manage_options',                                    
        'class-dropdown-options',                        
        'class_dropdown_settings_page'                   
    );
}

add_action('admin_menu', 'class_dropdown_admin_menu');

// Remove the parent menu as a submenu
function remove_cp_technologies_submenu() {
    remove_submenu_page('cp-technologies', 'cp-technologies');
}

add_action('admin_menu', 'remove_cp_technologies_submenu', 999);


function class_dropdown_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['save_class_dropdown_options'])) {
        $dropdown_options = isset($_POST['dropdown_options']) ? array_map('sanitize_text_field', $_POST['dropdown_options']) : [];
        $default_option = isset($_POST['default_option']) ? sanitize_text_field($_POST['default_option']) : '';

        if (isset($_POST['class_selection_enabled'])) {
            $class_selection_enabled = sanitize_text_field($_POST['class_selection_enabled']);
            update_option('class_selection_enabled', $class_selection_enabled);
        }

        update_option('class_dropdown_options', $dropdown_options);
        update_option('class_dropdown_default_option', $default_option);

        echo '<div class="updated"><p>' . __('Options updated successfully.', 'custom_class_dropdown_to_cart') . '</p></div>';
    }

    $current_options = get_option('class_dropdown_options', []);
    $default_option = get_option('class_dropdown_default_option', __('Select Your Class', 'custom_class_dropdown_to_cart'));
    $class_selection_enabled = get_option('class_selection_enabled', 'no');
    ?>
    <div class="wrap">
        <h1><?php _e('Class Dropdown Option Cart', 'custom_class_dropdown_to_cart'); ?></h1>
        <form method="post">
            <table class="form-table">
                        <tr>
                <th scope="row"><?php _e('Enable Class Selection Functionality', 'custom_class_dropdown_to_cart'); ?></th>
                <td>
                    <label>
                        <input type="radio" name="class_selection_enabled" value="yes" <?php checked($class_selection_enabled, 'yes'); ?> />
                        <?php _e('Yes', 'custom_class_dropdown_to_cart'); ?>
                    </label><br>
                    <label>
                        <input type="radio" name="class_selection_enabled" value="no" <?php checked($class_selection_enabled, 'no'); ?> />
                        <?php _e('No', 'custom_class_dropdown_to_cart'); ?>
                    </label>
                </td>
            </tr>
                <tr>
                    <th scope="row"><?php _e('Default Option Text', 'custom_class_dropdown_to_cart'); ?></th>
                    <td>
                        <input type="text" name="default_option" value="<?php echo esc_attr($default_option); ?>" style="width: 300px;" />
                        <p class="description"><?php _e('Text for the default option in the dropdown (e.g., "Select Your Class").', 'custom_class_dropdown_to_cart'); ?></p>
                    </td>
                </tr>
            </table>
            <h2><?php _e('Class Options', 'custom_class_dropdown_to_cart'); ?></h2>
            <table class="form-table" id="class-dropdown-table">
                <thead>
                    <tr>
                        <th><?php _e('Class Options', 'custom_class_dropdown_to_cart'); ?></th>
                        <th><?php _e('Action', 'custom_class_dropdown_to_cart'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($current_options)) : ?>
                        <?php foreach ($current_options as $option) : ?>
                            <tr>
                                <td>
                                    <input type="text" name="dropdown_options[]" value="<?php echo esc_attr($option); ?>" />
                                </td>
                                <td>
                                    <button type="button" class="remove-row button"><?php _e('Remove', 'custom_class_dropdown_to_cart'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button" id="add-row" class="button"><?php _e('Add Option', 'custom_class_dropdown_to_cart'); ?></button>
            <br><br>
            <input type="submit" name="save_class_dropdown_options" class="button-primary" value="<?php _e('Save Options', 'custom_class_dropdown_to_cart'); ?>">
        </form>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#add-row').on('click', function() {
                $('#class-dropdown-table tbody').append('<tr><td><input type="text" name="dropdown_options[]" value="" /></td><td><button type="button" class="remove-row button"><?php _e('Remove', 'custom_class_dropdown_to_cart'); ?></button></td></tr>');
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}