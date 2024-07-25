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

/**
 * Utility class for learning path block
 *
 * @package    block_pi_reviews
 * @copyright  2021 Sudhanshu Gupta (sudhanshug5@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_pi_reviews;

defined('MOODLE_INTERNAL') || die();

class pi_reviews {

    protected $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function get_assignment_reviews($cids, $page = 0, $perpage = 0, $where = null, $sort = '') {
        global $DB, $USER;
        $params = array();
        $totalrecord = 0;
        $context = \context_system::instance();
        if(is_siteadmin()){
            $select = "SELECT rand(),asu.id, cm.id as moduleid, u.id as userid,a.name as activityname, 
                     asu.status, Concat (u.firstname,' ', u.lastname) as username, c.id as cid, c.fullname as coursename,
                     gps.name as groupname, u.email as useremail , a.duedate , asu.timemodified as assignsubmitted, u.idnumber ";
            $countfields = 'SELECT COUNT(1)';
            $sql =  " FROM {assign_submission} asu 
                     left join {assign} a on a.id = asu.assignment  
                     left join {course} c on c.id = a.course 
                     left join {course_modules} as cm on cm.course = c.id 
                     left join {user} u on u.id = asu.userid 
                     left join {groups} gps on gps.courseid = c.id  
                     left join {groups_members} gm on gps.id = gm.groupid AND u.id = gm.userid 
                     where asu.status = 'submitted' and (asu.id,asu.userid)  
                     NOT IN (SELECT assignment,userid  FROM {assign_grades})   
                     AND u.deleted =0 and u.suspended = 0 and  cm.visible =1 AND  cm.module= 1
                      $where ";

            $sortsql = !empty($sort )? " ORDER by $sort" : '';
            $assignments = @$DB->get_records_sql($select.$sql.$sortsql, $params, ($page * $perpage), $perpage);
            $totalrecord = @$DB->count_records_sql($countfields.$sql, $params);
        } else {
            if (!empty($cids)) {
                $querycid = '';
                if (!empty($cids)) {
                    $querycid = ' AND c.id IN (' . $cids . ')';
                }
                $select = "SELECT rand(),asu.id, cm.id as moduleid, u.id as userid,a.name as activityname, 
                     asu.status, Concat (u.firstname,' ', u.lastname) as username, c.id as cid, c.fullname as coursename,
                     gps.name as groupname, u.email as useremail , a.duedate , asu.timemodified as assignsubmitted, u.idnumber ";
                $countfields = 'SELECT COUNT(1)';
                $sql = " FROM {assign_submission} asu 
                     left join {assign} a on a.id = asu.assignment  
                     left join {course} c on c.id = a.course 
                     left join {course_modules} as cm on cm.course = c.id 
                     left join {user} u on u.id = asu.userid 
                     left join {groups} gps on gps.courseid = c.id  
                     left join {groups_members} gm on gps.id = gm.groupid AND u.id = gm.userid 
                     where asu.status = 'submitted' and (asu.id,asu.userid)  
                     NOT IN (SELECT assignment,userid  FROM {assign_grades})   
                     AND u.deleted =0 and u.suspended = 0 and  cm.visible =1 AND  cm.module= 1
                      $querycid  $where ";

                $sortsql = !empty($sort) ? " ORDER by $sort" : '';
                $assignments = @$DB->get_records_sql($select . $sql . $sortsql, $params, ($page * $perpage), $perpage);
                $totalrecord = @$DB->count_records_sql($countfields . $sql, $params);
            }
        }
        return array('assignments' => $assignments, 'totalrecord' => $totalrecord);
    }
    
/*
     * User enrolled courseids
     */
    public function get_user_enrolled_courseids() {
        global $DB, $USER;
        $params = [];
        $params[] = $USER->id;
        $params[] = round(time(), -2);
        $params[] = round(time(), -2);
        $params[] = CONTEXT_COURSE;
        $params[] = 1;

        $sql = 'SELECT GROUP_CONCAT(c.id) as cids FROM {course} c    
             JOIN ( SELECT DISTINCT e.courseid FROM {enrol} e 
             JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ? ) 
             WHERE ue.status = 0 AND e.status = 0 AND ue.timestart < ? 
             AND (ue.timeend = 0 OR ue.timeend > ? )) en ON (en.courseid = c.id) LEFT JOIN {context} ctx ON (
             ctx.instanceid = c.id AND ctx.contextlevel = ?) WHERE c.id <> ? ORDER BY c.visible DESC, c.sortorder ASC';
        $courseids = $DB->get_record_sql($sql, $params);
        if ($courseids) {
            return $courseids;
        }
        return array();
    }
    
    /*
     * get enrolled user by course
     */

    public function get_course_users($cid) {
        global $DB;
        $params = [];
        $params['ej1_courseid'] = $cid;
        $sql = "SELECT distinct ( eu1_u.id)
                 FROM {user} eu1_u 
                 JOIN {user_enrolments} ej1_ue ON ej1_ue.userid = eu1_u.id
                 JOIN {enrol} ej1_e ON (ej1_e.id = ej1_ue.enrolid AND ej1_e.courseid = :ej1_courseid )
                 WHERE 1 = 1 AND eu1_u.deleted = 0 ";
        $users = $DB->get_records_sql($sql, $params);
        if ($users) {
            return array_keys($users);
        }
        return array();
    }

}
