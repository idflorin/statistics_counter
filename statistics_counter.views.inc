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

  $data['node_field_data']['weekcount'] = [
    'title' => t('Views this week'),
    'help' => t('The total number of times the node has been viewed this week.'),
    'field' => [
      'id' => 'numeric',
      'table' => 'node_counter',
      'field' => 'weekcount',
      'relationship' => 'node_counter',
    ],
  ];

  $data['node_field_data']['monthcount'] = [
    'title' => t('Views this month'),
    'help' => t('The total number of times the node has been viewed this month.'),
    'field' => [
      'id' => 'numeric',
      'table' => 'node_counter',
      'field' => 'monthcount',
      'relationship' => 'node_counter',
    ],
  ];

  $data['node_field_data']['yearcount'] = [
    'title' => t('Views this year'),
    'help' => t('The total number of times the node has been viewed this year.'),
    'field' => [
      'id' => 'numeric',
      'table' => 'node_counter',
      'field' => 'yearcount',
      'relationship' => 'node_counter',
    ],
  ];

  $data['node_field_data']['node_counter'] = [
    'title' => t('Node statistics counter'),
    'help' => t('Relate nodes to their view counts in the node_counter table.'),
    'relationship' => [
      'id' => 'standard',
      'base' => 'node_counter',
      'base field' => 'nid',
      'relationship field' => 'nid',
      'label' => t('Node counter'),
    ],
  ];

  return $data;
}
