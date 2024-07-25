<?php

// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block pi_reviews is defined here.
 *
 * @package     pi_reviews
 * @copyright   2024 Deependra Kumar Singh <deepcs20@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../../config.php';
require_once 'lib.php';
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
global $CFG, $DB, $USER, $OUTPUT, $PAGE;
require_login();
// Page configurations.
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('allreviews', 'block_pi_reviews'));
$PAGE->set_heading(get_string('allreviews', 'block_pi_reviews'));
$url = new moodle_url('/blocks/pi_reviews/allreviews.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);

echo $OUTPUT->header();

$homeurl = new \moodle_url('/my');
echo '<div class="back_arrow_text breadcrumb-forall-pages mobile-none">
            <span class="d-flex justify-content-start align-items-center ">
                <a class="accent-blue font-13 d-block font-weight-normal" href="' . $homeurl . '" title="">Home</a>
                <i class="fa fa-angle-right mx-2 d-block font-12"></i>
                <a class="black-base font-13 d-block font-weight-normal" title="">Assignment To Review</a>
            </span>
    </div>';
echo ' <div class="back_arrow_btn breadcrumb-forall-pages desktop-none mobile-block mt-0">
                    <span class="d-flex justify-content-start align-items-center ">
                        <i class="fa fa-angle-left mr-1 black-base d-block font-18 font-w-600" style="color: #333 !important;"></i>
                        <a class="black-base font-16 font-w-600 d-block" href="' . $homeurl . '" title>
                        Back  
                        </a>
                    </span>
            </div>';
echo "<div class='all-toreview-fullview-page'>";
echo $OUTPUT->heading('Assignment To Review');

// Learning path based on tenant 
$allparams = array("sesskey" => sesskey(), "dataformat" => 'csv');
$sitecontext = context_system::instance();
$companyreviews = new \block_pi_reviews\pi_reviews($sitecontext);
$courseids = $companyreviews->get_user_enrolled_courseids();
$cids = $courseids->cids;
$reviews = $companyreviews->get_assignment_reviews($cids, 0, $perpage);
$total_to_reviews = $reviews['totalrecord'];
$reviews = $reviews['assignments'];
$data = array();
$i = 0;
$total_students_program = 0;
$url_params = array();
foreach ($reviews as $key => $activity) {
    $data['activity'][$i]['course_name'] = $activity->coursename;
    $data['activity'][$i]['courseurl'] = $CFG->wwwroot.'/course/view.php?id='.$activity->cid;
    $data['activity'][$i]['name'] = $activity->activityname;
    $data['activity'][$i]['activityurl'] = $CFG->wwwroot.'/mod/assign/view.php?id='.$activity->moduleid;
    $data['activity'][$i]['username'] = $activity->username;
    $data['activity'][$i]['userurl'] = $CFG->wwwroot.'/user/profile.php?id='.$activity->userid;
    $data['activity'][$i]['groupname'] = $activity->groupname;
    $data['activity'][$i]['useremail'] = $activity->useremail;
    $data['activity'][$i]['idnumber'] = $activity->idnumber;
    $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
    $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/pi_reviews/pix/assignment-blue.svg";
    $data['activity'][$i]['assignmentduedate'] =   ($activity->duedate > 1) ? date("d/m/Y" ,$activity->duedate): "Not Set";
    $data['activity'][$i]['assignsubmitted'] =   ($activity->assignsubmitted > 1) ? date("d/m/Y" ,$activity->assignsubmitted): "Not Set";
    $grade_params = array('id' => $activity->moduleid, 'action' => 'grading');
    $data['activity'][$i]['gradeurl'] = new \moodle_url('/mod/assign/view.php', $grade_params);
    $i++;
}

$total_reviews = $data['to_reviews'] = $total_to_reviews;

$output = "";
$courses = pi_get_filters();
$courses = array_values($courses);

$output .= $OUTPUT->render_from_template('block_pi_reviews/filters', array('courses' => $courses));
$output .= html_writer::start_div('allreviews-display');
$data['downloadurl'] = new \moodle_url('/blocks/pi_reviews/download/download_allreviews.php', $allparams);

$output .= $OUTPUT->render_from_template('block_pi_reviews/allreviews_faculty', $data);
$url = new moodle_url('/blocks/pi_reviews/allreviews.php', $url_params);
$output .= html_writer::start_div('pagination-nav-filter');
$output .= $OUTPUT->paging_bar($total_reviews, $page, $perpage, $url);
$output .= html_writer::end_div();
$output .= html_writer::end_div();
echo $output;
echo "</div>";
echo $OUTPUT->footer();

