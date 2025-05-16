<?php

/**
 * @file
 * Provides Views data for the Statistics Counter module.
 */

/**
 * Implements hook_views_data().
 */
function statistics_counter_views_data() {
  $data = [];

  // Define a base table (node_counter joined via nid)
  $data['node_counter']['table']['group'] = t('Node Counter');
  $data['node_counter']['table']['join']['node_field_data'] = [
    'left_field' => 'nid',
    'field' => 'nid',
  ];

  // Define fields
  foreach (['weekcount', 'monthcount', 'yearcount'] as $field) {
    $data['node_counter'][$field] = [
      'title' => ucfirst($field),
      'help' => t("The total number of times the node has been viewed this @period.", ['@period' => str_replace('count', '', $field)]),
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'relationship' => [
        'title' => t('Node'),
        'help' => t('Relate node_counter to node.'),
        'base' => 'node',
        'base field' => 'nid',
        'relationship field' => 'nid',
        'id' => 'standard',
      ],
    ];
  }

  return $data;
}
