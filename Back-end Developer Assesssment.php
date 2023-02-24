<?php
/*
Plugin Name: My Unit Plugin
Plugin URI: https://example.com/
Description: A plugin to register the unit post type and consume an API to create unit records.
Version: 1.1
Author: Oleg Dykusha
Author URI: https://example.com/
*/

// Register the custom post type
function myunit_register_post_type() {
    $labels = array(
        'name' => 'Units',
        'singular_name' => 'Unit',
        'menu_name' => 'Units',
        'name_admin_bar' => 'Unit',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Unit',
        'new_item' => 'New Unit',
        'edit_item' => 'Edit Unit',
        'view_item' => 'View Unit',
        'all_items' => 'All Units',
        'search_items' => 'Search Units',
        'parent_item_colon' => 'Parent Units:',
        'not_found' => 'No units found.',
        'not_found_in_trash' => 'No units found in Trash.'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => array( 'title', 'custom-fields' ),
        'rewrite' => array( 'slug' => 'units' )
    );

    register_post_type( 'unit', $args );
}
add_action( 'init', 'myunit_register_post_type' );

// Add an admin page for the plugin
function myunit_admin_menu() {
    add_menu_page(
        'My Unit Plugin',
        'My Unit Plugin',
        'manage_options',
        'myunit-plugin',
        'myunit_admin_page'
    );
}
add_action( 'admin_menu', 'myunit_admin_menu' );

// Display the admin page
function myunit_admin_page() {
    ?>
    <div class="wrap">
        <h1>My Unit Plugin</h1>
        <button id="myunit-api-button" class="button">Fetch Units</button>
    </div>

    <script>
        // Consume the API and create unit records on button click
        document.getElementById("myunit-api-button").addEventListener("click", function() {
            fetch('https://api.sightmap.com/v1/assets/1273/multifamily/units?per-page=250', {
                headers: {
                    'API-Key': '7d64ca3869544c469c3e7a586921ba37'
                }
            })
            .then(response => response.json())
            .then(data => {
                data.units.forEach(unit => {
                    // Create a new post for each unit
                    wp.data.dispatch('core').createPost({
                        type: 'unit',
                        title: unit.unit_number,
                        status: 'publish',
                        content: '',
                        meta: {
                            asset_id: unit.asset_id,
                            building_id: unit.building_id,
                            floor_id: unit.floor_id,
                            floor_plan_id: unit.floor_plan_id,
                            area: unit.area
                        }
                    });
                });
            });
        });
    </script>
    <?php
}

// Add custom fields for the unit post type
function myunit_register_custom_fields() {
    acf_add_local_field_group(array(
        'key' => 'group_unit',
        'title' => 'Unit Details',
        'fields' => array(
            array(
                'key' => 'field_unit_asset_id',
                'label' => 'Asset ID
