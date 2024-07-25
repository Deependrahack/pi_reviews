<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Popup download
 */

require(__DIR__ . '../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/dataformatlib.php');
$format = optional_param('dataformat', '', PARAM_TEXT);
$programid = optional_param('pid', 0, PARAM_INT);
$activityname = optional_param('activityname', '', PARAM_TEXT);
require_login();
global $DB;
if ($format) {
    // Define the headers and columns.
    $headers = [];
    $headers[] = get_string('coursename', 'block_pi_reviews');
    $headers[] = get_string('activityname', 'block_pi_reviews');
    $headers[] = get_string('activitytype', 'block_pi_reviews');
    $headers[] = get_string('learnername', 'block_pi_reviews');
    $headers[] = get_string('idnumber', 'block_pi_reviews');
    $headers[] = get_string('group', 'block_pi_reviews');
    $headers[] = get_string('email', 'block_pi_reviews');
    $headers[] = get_string('assignmentdue', 'block_pi_reviews');
    $headers[] = get_string('submitteddate', 'block_pi_reviews');
    $sitecontext = context_system::instance();
    $companyreviews = new \block_pi_reviews\pi_reviews($sitecontext);
    $courseids = $companyreviews->get_user_enrolled_courseids();
    $cids = $courseids->cids;
    $reviews = $companyreviews->get_assignment_reviews($cids, 0, 0);
    $total_to_reviews = $reviews['totalrecord'];
    $reviews = $reviews['assignments'];
    $where = '';
//    if (!empty($activityname)) {
//        $where .= " AND a.name LIKE '%" . $activityname . "%'";
//    }
//    $reviews = $companyreviews->get_program_reviews(0, 0, $where);

    $data = array();
    $i = 0;
    $total_reviews = 0;
    $total_students_program = 0;
    $url_params = array();
    if (!empty($reviews)) {
        foreach ($reviews as $key => $activity) {
            $tmpdata = new stdClass();
            $tmpdata->coursename = htmlspecialchars_decode($activity->coursename);
            $tmpdata->activityname = htmlspecialchars_decode($activity->activityname);
            $tmpdata->activitytype = htmlspecialchars_decode('Assignment');
            $tmpdata->username = htmlspecialchars_decode($activity->username);
            $tmpdata->idnumber = htmlspecialchars_decode($activity->idnumber);
            $tmpdata->groupname = htmlspecialchars_decode($activity->groupname);
            $tmpdata->useremail = htmlspecialchars_decode($activity->useremail);
            $tmpdata->assignmentduedate =   ($activity->duedate > 1) ? date("d/m/Y" ,$activity->duedate): "Not Set";
            $tmpdata->assignsubmitted  =   ($activity->assignsubmitted > 1) ? date("d/m/Y" ,$activity->assignsubmitted): "Not Set";
            $allreviews[] = $tmpdata;
        }
    }

        $today_date = date("d-m-Y");
        $name = "Reviewlist_$today_date";
        $allreviews = (object) $allreviews;
        $filename = clean_filename($name);
        $activity = new ArrayObject($allreviews);
        $iterator = $activity->getIterator();

        $countrecord = 0;
        download_as_dataformat($filename, $format, $headers, $iterator, function ($activity) {
            global $DB;
            $data = array();
            $data[] = $activity->coursename;
            $data[] = $activity->activityname;
            $data[] = 'Assignment';
            $data[] = $activity->username;
            $data[] = $activity->idnumber;
            $data[] = $activity->groupname;
            $data[] = $activity->useremail;
            $data[] = $activity->assignmentduedate;
            $data[] = $activity->assignsubmitted;
            return $data;
        });
        exit;
    }

    