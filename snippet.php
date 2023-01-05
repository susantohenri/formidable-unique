<?php

add_filter('frm_pre_create_entry', 'formidable_unique_generate');
add_filter('frm_pre_update_entry', 'formidable_unique_generate', 10, 2 );
function formidable_unique_generate($values) {
	$uniques = [];
	$uniques[] = ['target' => 1552, 'sources' => [1560, 1551]];

    $increment_digit = 3;
    $increment_separator = ' ';

    foreach ($uniques as $unique) {
		if (!isset($values['item_meta'][$unique['target']])) continue;
		$result = '';
		foreach ($unique['sources'] as $source) {
			$result = '' !== $result ? "{$result} " : $result;
			$answer = $values['item_meta'][$source];
			if (isset($answer['first'])) {
				if ('' !== $answer['first'] && '' !== $answer['last']) $result .= trim($answer['first']) . ' ' . trim($answer['last']);
				else $result .= trim($answer['first']) . trim($answer['last']);
			}
			else {
				while (-1 < strpos($answer, '  ')) $answer = str_replace('  ', ' ', $answer);
				$result .= $answer;
			}
		}

        global $wpdb;
        $answers = $wpdb->get_results($wpdb->prepare("
            SELECT
                {$wpdb->prefix}frm_item_metas.meta_value answer
            FROM {$wpdb->prefix}frm_item_metas
            WHERE {$wpdb->prefix}frm_item_metas.field_id = %d
            AND {$wpdb->prefix}frm_item_metas.meta_value LIKE '{$result}%'
            ORDER BY {$wpdb->prefix}frm_item_metas.meta_value DESC
        ", $unique['target']));

        $increment = 1;
        if ($answers) {
            $latest = $answers[0]->answer;
            $latest = explode($increment_separator, $latest);
            $increment = end($latest);
            $increment = (int) $increment;
            $increment++;
        }

        $result .= $increment_separator . sprintf("%0{$increment_digit}d", $increment);
        $values['item_meta'][$unique['target']] = $result;
	}

	return $values;
}