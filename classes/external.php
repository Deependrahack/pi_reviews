<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_pi_reviews;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use moodle_url;
use context_system;
use html_writer;

class external extends external_api {

    /**
     * defines parameters to be passed in ws request
     */
    public static function get_all_reviews_parameters() {
        return new external_function_parameters(
                array(
            'cid' => new external_value(PARAM_TEXT, 'Course id name', VALUE_OPTIONAL),
            'search' => new external_value(PARAM_TEXT, 'Activity type', VALUE_OPTIONAL),
            'page' => new external_value(PARAM_INT, 'The page', VALUE_OPTIONAL),
            'column' => new external_value(PARAM_TEXT, 'The page', VALUE_OPTIONAL),
            'duesorting' => new external_value(PARAM_TEXT, 'The page', VALUE_OPTIONAL),
                )
        );
    }

    /**
     * Return the learning path info
     * @return array Learning path array
     */
    public static function get_all_reviews($cid,  $search= '', $page = 0, $column ="", $duesorting) {
        global $DB, $CFG, $OUTPUT, $PAGE;
        require_once $CFG->dirroot . '/blocks/pi_reviews/lib.php';
        $params = self::validate_parameters(self::get_all_reviews_parameters(),
                        array('cid' => $cid, 'search' => $search , 'page' => $page, 'column' =>$column ,'duesorting' => $duesorting));
        $perpage = 10;
        $page = $params['page'];
        $sitecontex = \context_system::instance();
        $PAGE->set_context($sitecontex);
        $where = '';
        $allparams = array("sesskey" => sesskey(), "dataformat" => 'csv');
        $sort = '';
        if (!empty($params['column']) && $params['column'] == "activityname") {
            $sort = " a.name $duesorting";
            $allparams['activityname'] = $params['activityname'];
        }
        if (!empty($params['column']) && $params['column'] == "coursename") {
            $sort = " c.fullname $duesorting";
            $allparams['coursename'] = $params['coursename'];
        }
        if (!empty($params['column']) && $params['column'] == "learnername") {
            $sort = " Concat (u.firstname,' ', u.lastname) $duesorting";
        }
        if (!empty($params['column']) && $params['column'] == "group") {
            $sort = " gps.name $duesorting";
        }
        if (!empty($params['column']) && $params['column'] == "email") {
            $sort = " u.email $duesorting";
        }
        if (!empty($params['column']) && $params['column'] == "assignmentdue") {
            $sort = " a.duedate $duesorting";
        }
        if (!empty($params['column']) && $params['column'] == "assignsubmited") {
            $sort = " asu.timemodified $duesorting";
        }
        
        if (!empty($params['search']) ) {
            $where = " AND  (a.name LIKE '%".$params['search']."%'
                      OR c.fullname LIKE '%".$params['search']."%' 
                      OR Concat (u.firstname,' ', u.lastname) LIKE '%".$params['search']."%')";
        }
        $sitecontext = context_system::instance();
        $companyreviews = new \block_pi_reviews\pi_reviews($sitecontext);
        
        if (!empty($params['cid'])) {
            $cids = $params['cid'];
        } else {
            $courseids = $companyreviews->get_user_enrolled_courseids();
            $cids = $courseids->cids;
        }
        $reviews = $companyreviews->get_assignment_reviews($cids, 0, $perpage, $where, $sort);
        $total_to_reviews = $reviews['totalrecord'];
        $reviews = $reviews['assignments'];
        $data = array();
        $i = 0;
        $url_params = array();
        if(!empty($reviews)){
        foreach ($reviews as $key => $activity) {
            $data['activity'][$i]['course_name'] = $activity->coursename;
            $data['activity'][$i]['courseurl'] = $CFG->wwwroot.'/course/view.php?id='.$activity->cid;
            $data['activity'][$i]['name'] = $activity->activityname;
            $data['activity'][$i]['activityurl'] = $CFG->wwwroot.'/mod/assign/view.php?id='.$activity->moduleid;
            $data['activity'][$i]['username'] = $activity->username;
            $data['activity'][$i]['userurl'] = $CFG->wwwroot.'/user/profile.php?id='.$activity->userid;
            $data['activity'][$i]['groupname'] = $activity->groupname;
            $data['activity'][$i]['useremail'] = $activity->useremail;
            $data['activity'][$i]['class'] = "assignment-btn d-flex justify-content-center align-items-center flex-row";
            $data['activity'][$i]['src'] = $CFG->wwwroot . "/blocks/pi_reviews/pix/assignment-blue.svg";
            $data['activity'][$i]['assignmentduedate'] =   ($activity->duedate > 1) ? date("d/m/Y" ,$activity->duedate): "Not Set";
            $data['activity'][$i]['assignsubmitted'] =   ($activity->assignsubmitted > 1) ? date("d/m/Y" ,$activity->assignsubmitted): "Not Set";
            $grade_params = array('id' => $activity->moduleid, 'action' => 'grading');
            $data['activity'][$i]['gradeurl'] = new \moodle_url('/mod/assign/view.php', $grade_params);
            $i++;
        }

            $total_reviews = $data['to_reviews'] = $total_to_reviews;
            $pagination = '';
                $out .= $OUTPUT->render_from_template('block_pi_reviews/allreviews_faculty_sort', $data);
                $url = new moodle_url('/blocks/pi_reviews/allreviews.php', $url_params);
                $pagination .= $OUTPUT->paging_bar($total_reviews, $page, $perpage, $url);
        } else {
            $out = html_writer::div(get_string('nothingtodisplay', 'block_pi_reviews'), 'alert alert-info mt-3');
        }
        $html = array();
        if (!empty($params['program'])) {
            $html['options'] = course_filter($params['program'], $params['cid']);
        }
        $html['displayhtml'] = $out;
        $html['pagedata'] = $pagination;
        return $html;
    }

    /**
     * returns leaders info in json format
     */
    public static function get_all_reviews_returns() {
        return $data = new external_single_structure([
            'displayhtml' => new external_value(PARAM_RAW, 'html'),
            'options' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL),
            'pagedata' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL)
        ]);
    }

    /**
     * defines parameters to be passed in ws request
     */
    public static function get_pending_submissions_parameters() {
        return new external_function_parameters(
                array(
            'instanceid' => new external_value(PARAM_INT, 'Module Instance id', VALUE_OPTIONAL),
            'courseid' => new external_value(PARAM_INT, 'courseid name', VALUE_OPTIONAL),
            'cmid' => new external_value(PARAM_INT, 'Course module id', VALUE_OPTIONAL)
                )
        );
    }

    /**
     * Return the learning path info
     * @return array Learning path array
     */
    public static function get_pending_submissions($instance, $courseid, $cmid) {
        global $DB, $CFG, $OUTPUT, $PAGE;
        require_once $CFG->dirroot . '/local/iomad/lib/iomad.php';
        require_once $CFG->dirroot . '/blocks/pi_reviews/lib.php';
        $params = self::validate_parameters(self::get_pending_submissions_parameters(),
                        array('instanceid' => $instance, 'courseid' => $courseid, 'cmid' => $cmid));

        $sitecontex = \context_system::instance();
        $PAGE->set_context($sitecontex);
        $where = '';
        $companyreviews = new \block_pi_reviews\pi_reviews($sitecontex);
        $userlist = $companyreviews->get_course_users($params['courseid']);
//        echo json_encode($userlist);die;
        $users = $companyreviews->get_pending_assignment($userlist, $params['instanceid']);
        if (!empty($users)) {
            $data = array();
            $i = 0;
            foreach ($users as $user) {
                $data[$i]['fullname'] = $user->fullname;
                $data[$i]['email'] = $user->email;
                $condition = (userdate($user->gradingduedate, '%d %b %Y') . ' ' . userdate($user->gradingduedate, '%H:%M'));
                $gradingduecondition = ($user->gradingduedate == 0 ) ? '--' : $condition;
                $data[$i]['gradingduedate'] = $gradingduecondition;
                $url = $CFG->wwwroot . "/mod/assign/view.php?id=" . $params['cmid'] . "&rownum=0&action=grader&userid=$user->userid";
                $data[$i]['gradingurl'] = $url;
                if ($user->userextensiondate) {
                    $userdate = userdate($user->extensionduedate);
                    $data[$i]['userextensiondate'] = get_string('userextensiondate', 'assign', $userdate);
                }
                $i++;
            }
            $users = array_values($users);

            $output .= $OUTPUT->render_from_template('block_pi_reviews/pending', array('learner' => $data));
        }
        $html = array();

        $html['displayhtml'] = $output;
        return $html;
    }

    /**
     * returns leaders info in json format
     */
    public static function get_pending_submissions_returns() {
        return $data = new external_single_structure([
            'displayhtml' => new external_value(PARAM_RAW, 'html'),
            'options' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL),
            'pagedata' => new external_value(PARAM_RAW, 'html', '', VALUE_OPTIONAL)
        ]);
    }

}
