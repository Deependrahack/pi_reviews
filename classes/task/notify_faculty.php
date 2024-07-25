<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

namespace block_pi_reviews\task;

/**
 * An example of a scheduled task.
 */
class notify_faculty extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('duedatenotify', 'block_pi_reviews');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $CFG, $USER;
        require_once $CFG->dirroot."/blocks/pi_reviews/lib.php";
        // Call your own api
        $assignments = get_assignment_with_duedate();
        if($assignments){
            $postsubject = get_string('postsubject' , 'block_pi_reviews');
            $posttext = get_string('posttext' , 'block_pi_reviews');
            $touser = \core_user::get_user($assignments->userid);
            
        $eventdata = new \core\message\message();
        $eventdata->courseid         = $assignments->course;
        $eventdata->modulename       = 'assign';
        $eventdata->userfrom         = $USER;
        $eventdata->userto           = $touser;
        $eventdata->subject          = $postsubject;
        $eventdata->fullmessage      = $posttext;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml  = $posthtml;
        $eventdata->smallmessage     = $postsubject;

        $eventdata->name            = $eventtype;
        $eventdata->component       = 'mod_assign';
        $eventdata->notification    = 1;
        $eventdata->contexturl      = $info->url;
        $eventdata->contexturlname  = $info->assignment;
        $customdata = [
            'cmid' => $coursemodule->id,
            'instance' => $coursemodule->instance,
            'messagetype' => $messagetype,
            'blindmarking' => $blindmarking,
            'uniqueidforuser' => $uniqueidforuser,
        ];
        // Check if the userfrom is real and visible.
        if (!empty($userfrom->id) && core_user::is_real_user($userfrom->id)) {
            $userpicture = new user_picture($userfrom);
            $userpicture->size = 1; // Use f1 size.
            $userpicture->includetoken = $userto->id; // Generate an out-of-session token for the user receiving the message.
            $customdata['notificationiconurl'] = $userpicture->get_url($PAGE)->out(false);
        }
        $eventdata->customdata = $customdata;

        message_send($eventdata);
        }
        
    }
}