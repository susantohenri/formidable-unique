<?php

/**
 * Formidable Unique
 *
 * @package     FormidableUnique
 * @author      Henri Susanto
 * @copyright   2022 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Formidable Unique
 * Plugin URI:  https://github.com/susantohenri
 * Description: Formidable form add-on for generate unique increment value. shortcode:[formidable-unique]. example: [input data-unique="combine-field1551-and-field1550-plus-field1553"]
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri
 * Text Domain: Formidable-Unique
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('FORMIDABLE_UNIQUE_SHORTCODE', 'formidable-unique');
define('FORMIDABLE_UNIQUE_ATTR', 'data-unique');
define('FORMIDABLE_ATTR_SEPARATOR', '-');
define('FORMIDABLE_FIELD_IDENTIFIER_PREFIX', 'field');

add_shortcode(FORMIDABLE_UNIQUE_SHORTCODE, function () {
    wp_register_script('formidable-unique', plugin_dir_url(__FILE__) . 'formidable-unique.js', array('jquery'));
    wp_enqueue_script('formidable-unique');
    wp_localize_script(
        'formidable-unique',
        'formidable_unique',
        array(
            'generator_url' => site_url('wp-json/formidable-unique/v1/generate?cache-breaker=' . time()),
            'attribute' => FORMIDABLE_UNIQUE_ATTR,
            'separator' => FORMIDABLE_ATTR_SEPARATOR,
            'field_identifier' => FORMIDABLE_FIELD_IDENTIFIER_PREFIX
        )
    );
});

add_action('rest_api_init', function () {
    register_rest_route(
        'formidable-unique/v1',
        '/generate',
        array(
            'methods' => 'POST',
            'permission_callback' => '__return_true',
            'callback' => function () {
                return 'helloworld';
            }
        )
    );
});
