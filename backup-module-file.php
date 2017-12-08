<?php

function mri_webform_ano_help($path, $arg)
{

}

function mri_webform_ano_perm()
{

}

function mri_webform_ano_menu()
{
  $items['abc/%/def'] = array(
    'page callback'  => 'mri_webform_ano_check',
    'access callback'=> 'user_is_logged_in',
  );
  $items['abc/%/test'] = array(
    'page callback'  => 'mri_webform_ano_check_g',
    'access callback'=> 'user_is_logged_in',
  );
  $items['zed'] = array(
    'page callback'  => 'mri_webform_ano_check_zed',
    'access callback'=> 'user_is_logged_in',
  );
  $items['tor/zed'] = array(
    'page callback'  => 'mri_webform_ano_check_tor',
    'access callback'=> 'user_is_logged_in',
  );
  $items['zed/form'] = array(//this creates a URL that will call this form at "examples / form - example"
    'title'=> 'Example Form',//page title
    'description'=> 'A form to mess around with.',
    'page callback'  => 'drupal_get_form',//this is the function that will be called when the page is accessed.  for a form, use drupal_get_form
    'page arguments' => array('mri_webform_ano_form_conf'),// name of the form 
    'access callback'=> TRUE
  );
  /*
  $items['admin/structure/webform-ip-anonymizer'] = array(
  'access callback' => 'user_is_logged_in',  //or 'user_is_logged_in' to check if logged in
  'type' => MENU_NORMAL_ITEM,
  'menu_name' => 'management',
  'title' => t('Webform IP Anonymizer'),


  'link_path' => 'my_path',
  'link_title' => 'title',
  'weight' => 0,
  'plid' => 0, // Parent menu item, 0 if menu item is on top level
  'module' => 'menu',
  );
  */
  return $items;
}
function mri_webform_ano_check()
{
  echo('test xxxx');
  drupal_set_message('Eintrag vorhanden', 'status');
  echo('test xxxx');
  return drupal_set_message('Eintrag vorhanden', 'status');
}
function mri_webform_ano_check_g()
{
  echo('test yyyy');
  drupal_set_message('yyyyEintrag vorhanden', 'status');
  echo('test yyyy');
  return drupal_set_message('yyyyEintrag vorhanden', 'status');
}
function mri_webform_ano_check_zed()
{
  echo('test mri_webform_ano_check_zed');
  drupal_set_message('Eintrag mri_webform_ano_check_zed vorhanden', 'status');
  echo('testmri_webform_ano_check_zed');
  return drupal_set_message('Eintrag mri_webform_ano_check_zed vorhanden', 'status');
}
function mri_webform_ano_check_tor()
{
  echo('test mri_webform_ano_check_tor');
  drupal_set_message('Eintrag mri_webform_ano_check_tor vorhanden', 'status');
  echo('test mri_webform_ano_check_tor');
  return drupal_set_message('Eintrag mri_webform_ano_check_tor vorhanden', 'status');
}
/*
function mri_webform_ano_webform_submission_insert($node, $submission) {
// Insert a record into a 3rd-party module table when a submission is added.
// echo ("test ".$node." ". $submission);
echo ("<br>ende");
// md5, mehrere mögliche Ausgaben
echo md5('test');
echo($submission->remote_addr);
$test = md5($submission->remote_addr);
echo ($test);
$submission->remote_addr = md5($submission->remote_addr);
echo($submission->remote_addr);
echo ("<br>ende");
}
*/

/*
	
*/
function mri_webform_ano_submission_presave($node, &$submission)
{
  // Insert a record into module table when a submission is added.

  drupal_set_message(t('The Remote Submission Sender IP.'.$submission->remote_addr), 'status');

  $encoded = md5($submission->remote_addr);

  $ipArr    = explode(".", $submission->remote_addr);
  $submission->remote_addr = $ipArr[0].'.'.$ipArr[1].'.'.$ipArr[2].'.'. "1111";

  //$submission->remote_addr = md5($submission->remote_addr);
  
   db_insert('webform_mri_conf')
    ->fields(array(
        'sid' => $form_state['values']['sid'],
        'fic_octet'=> $form_state['values']['fic_octet'],
        'hash'=> $form_state['values']['fic_octet'],
      ))->execute(); 
  
  
  drupal_set_message(t('The encoded Submission Sender IP after modify.'.$encoded), 'status');
  drupal_set_message(t('The Remote Submission Sender IP after modify.'.$submission->remote_addr), 'status');
  

  echo ("<br>ende");
}

/**
* Implements mri_webform_ano_settings_form().
*/
function mri_webform_ano_form_conf($form, &$form_state)
{
	$query = db_select('webform_mri_conf', 'wmriconf')
  ->fields('wmriconf', array('enc_type', 'repl_type'))
  ->condition('wmriconf.wmaid', 1)
  ->range(0, 1)
  ->execute();
  $num_of_results = 0;
  if($query->rowCount() != NULL){
		$num_of_results = $query->rowCount();
	}
  
 	$default_enc_type = 'md5';
	$default_oct_type = 'hide';
  if ($num_of_results > 0) {
		 	$result = $query->fetchObject();
		 	$default_enc_type = $result->enc_type;
		 	$default_oct_type =$result->repl_type;
	}
	else {
			echo('test');
	}
		 
	  

  $form['encode_settings'] = array(
    '#type'     => 'radios',
    '#options'   =>  array('md5' => 'MD5','hs1' => 'HS1','none'=> 'No encrypting'),
    '#validated'=> TRUE,
    '#default_value' => $default_enc_type,
  );
  $form['octett_settings'] = array(
    '#type'     => 'radios',
    '#options'   =>  array(
    	'cross_sum_last_two_octets'=> 'Cross Summ of the last two Octets', 
    	'cross_sum_last_octet'=> 'Cross Summ of the last Octet', 
    	'override_random_last_octet'=> 'Hide last IP octet',
    	'show'=> 'show last IP octet'),
    '#validated'=> TRUE,
    '#default_value' => $default_oct_type,
  );

  $form['save'] = array(
    '#type'    => 'submit',
    '#value'   =>  'Save',
    '#submit'   =>  array('mri_webform_ano_form_submit'),
    '#validate' =>  array('mri_webform_ano_form_validate'),
  );
  return $form;
}

/**
* Implements security_settings_form_validate().
*/
function mri_webform_ano_form_validate($form, $form_state)
{
  /*
  if ($form_state['values']['privacy_settings'] == 'private') {
  if ((strlen($form_state['values']['passwd']) < 6)) {
  form_set_error('passwd', 'Please enter minimum six charecters of password');
  }
  }
  */
}

/**
* Implements security_settings_form_submit().
*/
function mri_webform_ano_form_submit($form, $form_state)
{
  global $user;

  $result = db_select('webform_mri_conf', 'wmriconf')
  ->fields('wmriconf')
  ->execute();
  $num_of_results = $result->rowCount();

  if ($num_of_results > 0) {
  	db_update('webform_mri_conf')
    ->fields(array(
        'enc_type' => $form_state['values']['encode_settings'],
        'repl_type'=> $form_state['values']['octett_settings'],
      ))
    //->condition('created', REQUEST_TIME - 3600, ' >= ')
    ->execute();  	   
    drupal_set_message("successfully UPDATED Settings");
  }
  else {
    // Here can be insert Your custom form values.
     db_insert('webform_mri_conf')
    ->fields(array(
        'enc_type' => $form_state['values']['encode_settings'],
        'repl_type'=> $form_state['values']['octett_settings'],
      ))->execute();      
    drupal_set_message("successfully FIRST SAVE SETTINGS Settings");
  }
  
}



