<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

function pi_get_filters() {
    global $DB, $USER;
    $params = [];
    $params[] = $USER->id;
    $params[] = round(time(), -2);
    $params[] = round(time(), -2);
    $params[] = CONTEXT_COURSE;
    $params[] = 1;

    $sql = 'SELECT c.id as id, c.fullname as name FROM {course} c    
             JOIN ( SELECT DISTINCT e.courseid FROM {enrol} e 
             JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ? ) 
             WHERE ue.status = 0 AND e.status = 0 AND ue.timestart < ? 
             AND (ue.timeend = 0 OR ue.timeend > ? )) en ON (en.courseid = c.id) LEFT JOIN {context} ctx ON (
             ctx.instanceid = c.id AND ctx.contextlevel = ?) WHERE c.id <> ? ORDER BY c.visible DESC, c.sortorder ASC';
    $courseids = $DB->get_records_sql($sql, $params);
    if ($courseids) {
        return $courseids;
    }
    return array();
}

/*
 * get program courses
 */
function pi_get_program_courses($programids) {
    global $DB;
//    $programslist = implode(',', $programids);
////    print_object($programslist);die;
//    $companyid = 0;
//    $where = " AND lp.id IN ($programslist) ";
//    $sql = "Select DISTINCT(c.id), c.fullname as coursename "
//            . " from {learningpaths} as lp left join {learningpath_courses} as lpc on lp.id = lpc.learningpathid "
//            . " left join {course} as c "
//            . " on c.id = lpc.courseid  where "
//            . "  lpc.course_active = 1 and c.visible = 1 "
//            . " and lp.deleted = 0  " . $where;
////    $sortsql = " ORDER by $sort";
//    $courses = $DB->get_records_sql_menu($sql);
    return array();
}

/*
 * Create dropdown based on program
 */
function pi_course_filter($programid, $cid) {
    $options = '<option selected>Course</option>';
//    $courses = get_program_courses(array($programid));
//    if (!empty($courses)) {
//        foreach ($courses as $key => $course) {
//            $selectd = ($key == $cid) ? 'selected' : '';
//            $options .= '<option value="' . $key . '" '.$selectd.'>' . $course . '</option>';
//        }
//    }

    return $options;
}
