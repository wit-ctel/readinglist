<?php  


// utility functions for readinglist
// Some variables:
// We need to determine whether this is on a testing or a live server.


function get_section_id($course){
	/**
	 * Returns the absolute ID of the section within the moodle module, where the module actually resides within the course.
	 * 
	 **/
	$section_id = 0;
	//print_r($_GET);
	if(isset($_GET['add'])){
		$ob = unserialize($course->sectioncache);
		$section_id = $ob[0]->id;
	}
	if(isset($_GET['update'])){
		$ob = unserialize($course->modinfo);
		$section_id = $ob[$_GET['update']]->sectionid;
	}
	return $section_id;
}

function moodle_list_createOrUpdate($course, $USER, $categoryrecord, $DB){

	$ancestry = array();
	$ancestors = explode('/', trim($categoryrecord->path, '/'));
	$currentrecord = '';
	foreach($ancestors as $ancestor){
		$currentrecord = $DB->get_record('course_categories', array('id' => $ancestor));
		array_push($ancestry, array('id'=>$ancestor, 'name'=>$currentrecord->name));
	}
	$returnarray = array('MOODLE_USER'=> array('username'=>$USER->username, 'firstname'=>$USER->firstname, 'lastname'=>$USER->lastname, 'email'=>$USER->email, 'role' => $isteacher), 'MOODLE_INTERNAL_ID' => $course->id, 'MOODLE_MODULE_NAME' => $course->fullname, 'MOODLE_MODULE_ANCESTOR_CATEGORIES' => $ancestry, 'MOODLE_ANCESTOR_PATH' => $categoryrecord->path, 'MOODLE_SECTION_ID' => get_section_id($course));
	
	#print_r($returnarray);
	return $returnarray;
}

function renderList($course, $isteacher, $USER, $categorypath, $moduleid, $readinglist, $cm = NULL){
	/**
	 * This function constructs a URL that retrieves an HTML-formatted readinglist for $moduleid
	 * @author David Kane, WIT Libraries <dkane@wit.ie>
	 * There is no return value.  It simply prints out the readinglist into the rendered page in Moodle.
	 *
	 * Importantly, the list is requested using the username as a parameter.  If the username is not 'STUDENT'
	 * then the code in the readinglists_server will associate the teacher with the readinglist, if they are
	 * not already associated.  
	 * 
	 * @param string $course
	 * @param string $isteacher
	 * @param object $USER
	 * @param string $categorypath
	 * @param string $moduleid
	 */
	 #print "test 123";
	$readinglistserver = 'https://library.wit.ie/';
	
	if(!$isteacher){
		$USER->username = "STUDENT"; 
	}
	$serialize = array(
		'MOODLE_INTERNAL_ID' => $course->id,
		'MOODLE_USERNAME' => $USER->username,
		'MOODLE_USER_EMAIL' => $USER->email,
		'MOODLE_USER_AUTH' => $USER->auth,
		'MOODLE_SECTION_ID' => $readinglist->section
	);
	$url = $readinglistserver . "readinglists/index.php/lists/list_books/moodle/" . base64_encode(serialize($serialize));
	//$url = $readinglistserver . "readinglists/index.php/lists/list_books/moodle/" . $course->id . "/" . $USER->username;
	$HTML = file_get_contents($url);
	$list = $HTML;
	if($isteacher){
		$list .= "<p style=\"margin-left: 20px; font-style: italic;\">&bull; Please contact a member of the library staff, ";
		$list .= "if you need help with this reading list.  We are available at: <a mailto=\"readinglists@wit.ie\" ";
		$list .= "style=\"color: blue; text-decoration: underline; cursor: pointer\">readinglists@wit.ie</a></p>";
	}
	else{
		$list .= "<p style=\"margin-left: 20px; font-style: italic;\">&bull; ";
		$list .= "Please contact your Lecturer if there is anything wrong with this reading list</p>";
	}
  // $da = print_r($USER, TRUE);
  // $list .= "<pre>$da</pre>";
	print $list;
}

function emptyList($data){
	/**
	 * A list might exist, but may have no items in it.  This function checks whether the list is empty
	 * @param string $data
	 * @return boolean 
	 */
	$pattern = '/border: solid 1px black; margin: 20px; padding: 20px; margin-top: 0px;"> </div>/';
	if(!isset($data) || preg_match($pattern, $data)){
		return false; // no data
	}else{
		return true; // proper readinglist
	}
}

function notifyLibrary($moodle_array){
	#print("<pre>");
	#print_r($moodle_array);
	#print("</pre>");
	
	/**
	 * Sends off an email to the lirbary reading lists address when the course is created from scratch.  
	 * I.E. there was no record for it in the readinglists database.
	 * It calls a special URL, through do_post_request, which actually creates a new list, where none existed before.
	 * 
	 * @param array $data - multidimensional array
	 * @return string - this return value is not used in the program. 
	 */	
	 
	$data = "datastruct=".base64_encode(serialize($moodle_array));
	
	#$data = "email=".$moodle_array['MOODLE_USER']['email'];
	#$data .= "&fname=".$moodle_array['MOODLE_USER']['firstname'];
	#$data .= "&lname=".$moodle_array['MOODLE_USER']['lastname'];
	#$data .= "&username=".$moodle_array['MOODLE_USER']['username'];
	#$data .= "&categoryrecord_path=".$moodle_array['MOODLE_ANCESTOR_PATH'];
	#$data .= "&fullname=".$moodle_array['MOODLE_MODULE_NAME'];
	#$data .= "&moodle_id=".$moodle_array['MOODLE_INTERNAL_ID'];
	#$data .= "&shortname=".$moodle_array['MOODLE_MODULE_NAME'];
	#$data .= "&datastruct=".base64_encode(serialize($moodle_array));
	
	//$params = array('http' => array('method' => 'POST', 'content' => $data ));
	//$data = "email=AGRAHAM@wit.ie&fname=Anne&lname=Graham&username=agraham&categoryrecord_path=/1037/1530/1582/1613/1614&fullname=Educational Psychology for Further Education-84428-2013&moodle_id=12641&shortname=Educational Psychology for Further Education-84428-2013";
	$url = "http://library.wit.ie/readinglists/index.php/lists/newlistrequest?" . $data;
	
	//print "<a href=\"$url\" target=\"_blank\">$url</a><br/>\n";
	$params = array(
		'http' => array(
			'method' => 'POST', 
			'content' => $data ,
			'header' => "User-Agent:MyAgent/1.0\r\nContent-Length: " . strlen($data) . "\r\nContent-Type:application/x-www-form-urlencoded"
		),
		'ssl' => array(
			'verify_peer'   => true,
			'cafile'        => __DIR__ . '/cacert.pem',
			'verify_depth'  => 5,
			'CN_match'      => 'library.wit.ie'
		)
	);
	
  
	#if ($optional_headers !== null) {	$params['http']['header'] = $optional_headers;}
	$ctx = stream_context_create($params);
	//print_r(get_object_vars($ctx));
	//$url = 'http://ireland.com/';
	#$fp = fopen($url, 'rb', false, $ctx);
	$fp = fopen($url, 'r', false, $ctx); 
	#if (!$fp) {
		#throw new Exception("Problem with $url, $php_errormsg");
	#	print("<br>Problem with $url - , <br>$php_errormsg<br/>");
	#}
	$response = stream_get_contents($fp);
	if ($response === false) {
		//throw new Exception("Problem reading data from $url, $php_errormsg");
		print("\n<br/>Problem reading data from $url, $php_errormsg");
	}
	return $response;
}

?>
