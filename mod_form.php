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
 * The main readinglist configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage readinglist
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/functions.php');
//$courserecord = $DB->get_record('course', array('id' => $course->id));
$categoryrecord = $DB->get_record('course_categories', array('id' => $course->category));
/*
I am building something beautiful.  It runs like clockwork.  Lots of little pieces that like fit together like gears.  Perfectly.  People smile, and feel such gratitude, whenever they use it.  
*/

if(has_capability('mod/readinglist:addinstance', $context) == false){
	$isteacher = false;
}else{	
	$isteacher = true;	
}



//print_r(moodle_list_createOrUpdate($course, $USER, $categoryrecord, $DB));
/* new notifylibrary function - same as old one.  For now. */
#moodle_list_createOrUpdate($course, $USER, $categoryrecord, $DB);
notifyLibrary(moodle_list_createOrUpdate($course, $USER, $categoryrecord, $DB));
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_readinglist_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform = $this->_form;
		$intro_html = <<<HTML
		
		<div class="fitem fitem_ftext" id="fitem_id_name">
			<div class="fitemtitle">&nbsp;</div>
			<div class="felement ftext">
				<img src="/mod/readinglist/images/mod-form.png"  />
				<p>The library has a huge range of e-resources, and other materials, which are available to the <br/>
				teacher and the student.  We invest heavily in a wide range of specialised resources for the  <br/>
				benefit of our Students.</p>
				<p>These readinglists are one way that we have of making these available to the students.</p>
				<p>Talk to a member of the library staff for more details...</p>
				<p>Visit <a href="http://library.wit.ie/" target="_blank" >library.wit.ie</a>.</p>
			</div>
		</div>
		
		
		
		
		
		
		
HTML;
		//-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        // $mform->addElement('header', '', get_string('general', 'form'));
        $mform->addElement('header', '', 'About Reading Lists');
		$mform->addElement('html', $intro_html);
		#$mform->addElement('static','info','Information:',"Don't run <strong>with</strong> scissors");
        
        $mform->addElement('header', '', 'Set Name and Description');
		#$mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
       	# $mform->addElement('text', 'name', get_string('readinglistname', 'readinglist'), array('size'=>'64'));
        $mform->addElement('text', 'name', 'Name', array('size'=>'64'));
		$mform->updateElementAttr('name', array('value' => 'Library Reading List', 'style' => 'background-color: #C0C0C0'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        #$mform->addHelpButton('name', 'readinglistname', 'readinglist');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();
        //-------------------------------------------------------------------------------
        // Adding the rest of readinglist settings, spreeading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic
        #$mform->addElement('static', 'label1', 'readinglistsetting1', 'Your readinglist fields go here. Replace me!');

        #$mform->addElement('header', 'readinglistfieldset', get_string('readinglistfieldset', 'readinglist'));
        #$mform->addElement('static', 'label2', 'readinglistsetting2', 'Your readinglist fields go here. Replace me!');

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
	
    function add_intro_editor($required=false, $customlabel=null) {
		// just overriding the inherited function - to make things look simpler for staff - David Kane 14 Sep 2012.
        if (!$this->_features->introeditor) {
            // intro editor not supported in this module
            return;
        }
		
		# print_r($this->context);
        $mform = $this->_form;
		$customlabel = "Write a note to your students here about the reading list";
        $label = is_null($customlabel) ? get_string('moduleintro') : $customlabel;
		$params_array = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context);
		#print("<pre>label: " . get_string('moduleintro') . "\n\n"); print_r($params_array); print("</pre>");
		
		
        $mform->addElement('editor', 'introeditor', $label, null, $params_array);
        $mform->setType('introeditor', PARAM_RAW); // no XSS prevention here, users must be trusted
        if ($required) {
            $mform->addRule('introeditor', get_string('required'), 'required', null, 'client');
        }

		// From/until controls
	#	$mform->addElement('header', 'availabilityconditionsheader', get_string('availabilityconditions', 'condition'));
	#	$mform->addElement('date_time_selector', 'availablefrom', get_string('availablefrom', 'condition'), array('optional' => true));
	#	$mform->addHelpButton('availablefrom', 'availablefrom', 'condition');
	#	$mform->addElement('date_time_selector', 'availableuntil', get_string('availableuntil', 'condition'), array('optional' => true));

		 // Do we display availability info to students?
	#	$mform->addElement('select', 'showavailability', get_string('showavailability', 'condition'), array(CONDITION_STUDENTVIEW_SHOW=>get_string('showavailability_show', 'condition'), CONDITION_STUDENTVIEW_HIDE=>get_string('showavailability_hide', 'condition')));
	#	$mform->setDefault('showavailability', CONDITION_STUDENTVIEW_SHOW);

        // If the 'show description' feature is enabled, this checkbox appears
        // below the intro.
        if ($this->_features->showdescription) {
            $mform->addElement('hidden', 'showdescription', get_string('showdescription'));
            $mform->addHelpButton('showdescription', 'showdescription');
        }
    }
	/**
     * Adds all the standard elements to a form to edit the settings for an activity module.
     */
   /* function standard_coursemodule_elements(){
        global $COURSE, $CFG, $DB;
        $mform =& $this->_form;
        $this->_outcomesused = false;
        $mform->addElement('header', 'modstandardelshdr', get_string('modstandardels', 'form'));
        $mform->addElement('modvisible', 'visible', get_string('visible'));
        if (!empty($this->_cm)) {
            $context = get_context_instance(CONTEXT_MODULE, $this->_cm->id);
            if (!has_capability('moodle/course:activityvisibility', $context)) {
                $mform->hardFreeze('visible');
            }
        }
        $this->standard_hidden_coursemodule_elements();
    }
	*/

}
