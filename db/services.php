<?php


$functions = array(
    'block_pi_reviews_get_reviews' => array(
        'classpath' => '',
        'classname' => 'block_pi_reviews\external',
        'methodname' => 'get_all_reviews',
        'description' => 'Return list of the reviews as per the filter',
        'type' => 'read',
         'ajax'        => true,
        'capabilities' => '',
    ),
    'block_pi_reviews_get_pending_submissions' => array(
        'classpath' => '',
        'classname' => 'block_pi_reviews\external',
        'methodname' => 'get_pending_submissions',
        'description' => 'Return list of the reviews as per the filter',
        'type' => 'read',
         'ajax'        => true,
        'capabilities' => '',
    ),
);