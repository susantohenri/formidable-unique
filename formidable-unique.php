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
define('FORMIDABLE_UNIQUE_ATTR_SEPARATOR', ' ');
define('FORMIDABLE_UNIQUE_FIELD_IDENTIFIER_PREFIX', 'field');
define('FORMIDABLE_UNIQUE_INCREMENT_LENGTH', 3);

add_shortcode(FORMIDABLE_UNIQUE_SHORTCODE, function () {
    wp_register_script('formidable-unique', plugin_dir_url(__FILE__) . 'formidable-unique.js', array('jquery'));
    wp_enqueue_script('formidable-unique');
    wp_localize_script(
        'formidable-unique',
        'formidable_unique',
        array(
            'generator_url' => site_url('wp-json/formidable-unique/v1/generate?cache-breaker=' . time()),
            'attribute' => FORMIDABLE_UNIQUE_ATTR,
            'separator' => FORMIDABLE_UNIQUE_ATTR_SEPARATOR,
            'field_identifier' => FORMIDABLE_UNIQUE_FIELD_IDENTIFIER_PREFIX
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
                $target = $_POST['target'];
                $combination = $_POST['combination'];
                $sources = $_POST['sources'];
                $values = $_POST['values'];

                $combination = explode(FORMIDABLE_UNIQUE_ATTR_SEPARATOR, $combination);
                $combination = array_map(function ($word) use ($values) {
                    if (-1 < strpos($word, FORMIDABLE_UNIQUE_FIELD_IDENTIFIER_PREFIX)) {
                        $field = str_replace(FORMIDABLE_UNIQUE_FIELD_IDENTIFIER_PREFIX, '', $word);
                        $word = $values[$field];
                    }
                    return $word;
                }, $combination);
                $combination = implode(FORMIDABLE_UNIQUE_ATTR_SEPARATOR, $combination);
                while ('-' === substr($combination, -1)) $combination = substr($combination, 0, -1);
                while ('-' === substr($combination, 1)) $combination = substr($combination, 0, 1);

                global $wpdb;
                $answers = $wpdb->get_results($wpdb->prepare("
                    SELECT
                        {$wpdb->prefix}frm_item_metas.meta_value answer
                    FROM {$wpdb->prefix}frm_item_metas
                    WHERE {$wpdb->prefix}frm_item_metas.field_id = %d
                    AND {$wpdb->prefix}frm_item_metas.meta_value LIKE '{$combination}%'
                    ORDER BY {$wpdb->prefix}frm_item_metas.meta_value DESC
                ", $target));

                $increment = 1;

                if ($answers) {
                    $latest = $answers[0]->answer;
                    $latest = explode(FORMIDABLE_UNIQUE_ATTR_SEPARATOR, $latest);
                    $increment = end($latest);
                    $increment = (int) $increment;
                    $increment++;
                }

                $max_length_digit = FORMIDABLE_UNIQUE_INCREMENT_LENGTH;
                $combination .= FORMIDABLE_UNIQUE_ATTR_SEPARATOR . sprintf("%0{$max_length_digit}d", $increment);
                return $combination;
            }
        )
    );
});
