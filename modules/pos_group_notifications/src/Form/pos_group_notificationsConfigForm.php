<?php

namespace Drupal\pos_group_notifications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\user\Entity\Role;

use Drupal\Core\Url;
use Drupal\Core\Link;

class pos_group_notificationsConfigForm extends ConfigFormBase {

	public function getFormId() {
		return 'pos_group_notifications_config_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		$config = $this->config('pos_group_notifications.settings');  // store data in pos_group_notifications.settings
		$form = parent::buildForm($form, $form_state);

		$defualtTab = 'edit-groupGeneral';
		

		/**************/		
		$form['vertical_tabs'] = array(
            '#type' => 'vertical_tabs',
            '#title' => t('PoS group notifications settings'),
             '#default_tab' => $defualtTab,
        );
		/*************/

		$form['groupGeneral'] = array(
		  '#type' => 'details',
		  '#title' => t('General Configuration'),
		  '#description' => t('Configure here the main data used in this module.'),
		  '#open' => TRUE  ,
		  '#required'      => TRUE,
		  //'#attributes' => array('class' => array('form-required')),
		  '#group' => 'vertical_tabs',
		 
		);

		$form['groupGeneral']['adviceusercheck'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => t('Only users that has the field "I wish to be informed of changes on a site and in my groups per e-mail" (machine name = field_accept_email_notifications) checked in their profiles will receive an e-mail.'),
			'#suffix' => '</br>',
			//'#tree' => true,
		);
				
 		$form['groupGeneral']['pos_group_notifications_testing_mode'] = array(
			'#type'          => 'checkbox',
			'#title'         => t('Testing mode'),
			'#description' => t('Check this checkbox to use this functionality in testing mode. If testing mode is enabled e-mails will be sended to the e-mail configured in this form.'),
			'#required'      => FALSE,
			'#default_value' => $config->get('pos_group_notifications_testing_mode'),
		);

		$form['groupGeneral']['pos_group_notifications_primary_email'] = array(
			'#type' => 'email',
			'#title' => t('E-mail address used to send e-mails in testing mode'),
			'#description' => t('It is used to avoid SPAM to the PoS members when this module is working in testing mode. The original E-mail user address will be replaced by this one.'),
			'#required'      => TRUE,
			'#default_value' => $config->get('pos_group_notifications_primary_email'),
		); 

		$form['group1'] = array(
		  '#type' => 'details',
		  '#title' => t('New content in groups'),
		  '#description' => t('Data used into the Cron task to send e-mails to the members of the PoS groups when new nodes has been published or updated in any group.'),
		  '#open' => TRUE,
		  //'#required'      => TRUE,
		  '#group' => 'vertical_tabs',
		);
		
		$form['group1']['periodicalcron']  = array(
		  '#type' => 'details',
		  '#title' => t("Periodical"),
		  '#description' => t('Data used by the cron task each time the task is executed'),
		  '#open' => FALSE
		);
				
		global $base_url;
		$host = $base_url;
		$link = Link::fromTextAndUrl(t('here'), Url::fromUri($base_url."/admin/config/system/cron/jobs",array('attributes' => array('target' => '_blank'))))->toString();
		
		$output = '';
		//$output = '<h3>' . t('Important') . '</h3>';
		$output .= '<p>' . t("It's important to be aware of this:") . '</p>';
		$output .= '<ul>';
		$output .= '<li>' . t("The cron task of this module uses the pos_notification_group_nodes view to recover data, please don't change it.") . '</li>';
      	$output .= '<li>' . t('Remember to configure the cron task according your needs into the cron administration page. To do it you can click @link.', array('@link'=>$link)) . '</li>';
		$output .= '<li>' . t('Only one e-mail per user with the latest data published or updated in their groups will be sended (Only if there are data to send).') . '</li>';
	    $output .= '</ul>';
		
		$form['group1']['periodicalcron']['advice'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => $output,
			'#suffix' => '</br>',
			//'#tree' => true,
		);


				
		$default_last_run = $config->get('pos_group_notifications_last_run');
		
		//drupal_set_message('-------------------------$default_last_run='.$default_last_run, 'error');
		
		if ($default_last_run) {
			$default_last_run = new DrupalDateTime( $config->get('pos_group_notifications_last_run'));
		}
		
		//drupal_set_message('-------------------------$default_last_run='.$default_last_run, 'error');
		
 		$form['group1']['periodicalcron']['pos_group_notifications_last_run'] = [
			'#type'          => 'datetime',
			'#title'         => t('Last run'),			
			'#description' => t('Last date that this script has been executed. This field is used to recover only the nodes that has been published or updated since this date into the "new content in groups". If you reset the content of this field all documents will be added into the e-mails the next time the pocess will be executed. Only nodes not readed by the users will be added into the e-mails.'),			
			'#default_value' => $default_last_run,
		];





		$default_subject_value = $config->get('pos_group_notifications_subject_message');

		$form['group1']['periodicalcron']['pos_group_notifications_subject_message'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail for the periodical emails'),
			'#maxlength' => 128,
			'#description' => t('[site:name] and [last_cron_run] will be replaced by the appropriate value.').' '.t('This subject will be used by the cron task each time the task is executed'),
			'#required'      => TRUE,
			'#default_value' => $default_subject_value,
		);



		$default_body_value = $config->get('pos_group_notifications_body_message');
		
				
		$form['group1']['periodicalcron']['pos_group_notifications_body_message'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [last_cron_run], [dynamic_content_per_user] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => ($default_body_value),
		);
		

		$form['group1']['reminder']  = array(
		  '#type' => 'details',
		  '#title' => t("Reminder"),
		  '#description' => t('Data used by the cron task each time the reminder task is executed'),
		  '#open' => TRUE
		);
		
		$last_reminder_value = $config->get('pos_group_notifications_last_reminder_execution');		
		//$last_reminder_value = new DrupalDateTime( $config->get('pos_group_notifications_last_reminder_execution'));
		
		if (!$last_reminder_value) {
			$last_reminder_value= t('Never');
			$last_reminder_value_field = '2019-01-31';
		}
		else{
			$last_reminder_value_field = $last_reminder_value;		
			$last_reminder_value = strtotime($last_reminder_value);
			$last_reminder_value = date('Y-M-d', $last_reminder_value);
		}
		
		$form['group1']['reminder']['adviceReminder'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => t('If the cron task is executed the next reminder e-mail notification will be sended on ').date('Y-M-t').". ".t('The previous one was sended on ').$last_reminder_value,
			'#suffix' => '</br>',
			//'#tree' => true,
		);

		$form['group1']['reminder']['pos_group_notifications_last_reminder_execution'] = array(
			'#type' => 'hidden',
			//'#type' => 'textfield',
			'#title' => t('Last reminder notification date'). t("Format YYYY-MM-DD"),
			'#maxlength' => 128,			
			'#required'      => TRUE,
			'#default_value' => $last_reminder_value_field,
		);
	    
	    
		
		$default_subject_value = $config->get('pos_group_notifications_subject_message_big_update');
		$form['group1']['reminder']['pos_group_notifications_subject_message_big_update'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail for the month report'),
			'#maxlength' => 128,
			'#description' => t('[site:name] and [last_cron_run] will be replaced by the appropriate value.').' '.t('This subject will be used one time per month just to remind users that there are content in their groups'),
			'#required'      => TRUE,
			'#default_value' => $default_subject_value,
		);


		$default_body_value = $config->get('pos_group_notifications_body_message_big_update');
		
				
		$form['group1']['reminder']['pos_group_notifications_body_message_big_update'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [last_cron_run], [dynamic_content_per_user] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => ($default_body_value),
		);
		
		
		$form['group2'] = array(
		  '#type' => 'details',
		  '#title' => t('Request publication'),
		  '#description' => t('When group owner/team member sets the "Request publication", the site editor(s) should receive an e-mail.'),
		  '#open' => TRUE,
		  '#group' => 'vertical_tabs',
		);


		
		$rolesToAddInTheList = array();
	  	$role_objects  = Role::loadMultiple();
		$system_roles = array_combine(array_keys($role_objects), array_map(function($a){ return $a->label();}, $role_objects));
		foreach ($system_roles as $key=>$value) {
			if (($key!='anonymous') && ($key!='authenticated')) {				
				$rolesToAddInTheList[$key]=$value;
				//$newData = array()
				//$newData[$key]=$value;
				//array_push($rolesToAddInTheList,$newData);
			}
	  	}

		//$pos_group_notifications_request_publication_role_to_notify ="";
		$pos_group_notifications_request_publication_role_to_notify = $config->get('pos_group_notifications_request_publication_role_to_notify');

		$form['group2']['pos_group_notifications_request_publication_role_to_notify'] = array(		
		'#type' => 'radios',
		'#title' => t('Role'),
		'#description' => t('Users that belongs to this group will be notified.'),
		'#required'      => TRUE,
		'#options' => $rolesToAddInTheList,
		'#default_value' => $pos_group_notifications_request_publication_role_to_notify,
		);  		
		
		$pos_group_notifications_request_publication_subject = $config->get('pos_group_notifications_request_publication_subject');
		$form['group2']['pos_group_notifications_request_publication_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			//'#default_value' => 'Subject....',
			'#default_value' => $pos_group_notifications_request_publication_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_request_publication_body'); 
		
		$form['group2']['pos_group_notifications_request_publication_body'] = array(
			'#type' => 'textarea',
			//'#type' => 'text_format',
			//'#format' => 'full_html',
			'#rows'=> 7,
			//'#maxlength' => 255,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [editor:name], [group:name] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		);
		
		$form['group3'] = array(
		  '#type' => 'details',
		  '#title' => t('QA not empty'),
		  '#description' => t("Data used into the Cron task to send e-mails to the site editors to reminder there that /QA/groups isn't empty."),
		  //'#description' => t("The site editors should receive a reminder if /QA/groups isn't empty."),
		  '#open' => TRUE,
		  '#group' => 'vertical_tabs',
		);

		$pos_group_notifications_qa_not_empty_role_to_notify = $config->get('pos_group_notifications_qa_not_empty_role_to_notify');
		$form['group3']['pos_group_notifications_qa_not_empty_role_to_notify'] = array(		
		'#type' => 'radios',
		'#title' => t('Role'),
		'#description' => t('Users that belongs to this group will be notified.'),
		'#required'      => TRUE,
		'#options' => $rolesToAddInTheList,
		'#default_value' => $pos_group_notifications_qa_not_empty_role_to_notify,
		); 
		
		$pos_group_notifications_qa_not_empty_subject = $config->get('pos_group_notifications_qa_not_empty_subject');
		$form['group3']['pos_group_notifications_qa_not_empty_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			//'#default_value' => 'Subject....',
			'#default_value' => $pos_group_notifications_qa_not_empty_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_qa_not_empty_body'); 
		
		$form['group3']['pos_group_notifications_qa_not_empty_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [qa:url] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		);


		/***********************/
		//reminder that their solution/trial isn't published
		$form['group4'] = array(
		  '#type' => 'details',
		  '#title' => t("Notification after editor reject or accept a solution/trial"),
		  '#description' => t("When editor rejects or accepts the request, the group owner/team must receive an e-mail"),
		  '#open' => TRUE,
		  '#group' => 'vertical_tabs',
		);

		$form['group4']['reject']  = array(
		  '#type' => 'details',
		  '#title' => t("Reject"),
		  '#description' => t("Data used to send e-mails when the editor rejects.")."<br/>".t("Reject is when an editor (user with the administration role) save a trial/solution with the field 'QA approved?' unchecked and the QA comments text changes."),
		  '#open' => TRUE
		);


		$pos_group_notifications_reject_solution_trial_subject = $config->get('pos_group_notifications_reject_solution_trial_subject');
		$form['group4']['reject']['pos_group_notifications_reject_solution_trial_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_reject_solution_trial_subject,
		);

		$default_body_value = $config->get('pos_group_notifications_reject_solution_trial_body'); 

		$form['group4']['reject']['pos_group_notifications_reject_solution_trial_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [item:url], [reason] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		); 

		$form['group4']['accept']  = array(
		  '#type' => 'details',
		  '#title' => t("Accept"),
		  '#description' => t("Data used to send e-mails when the editor accepts"),
		  '#open' => TRUE
		);

		$pos_group_notifications_accept_solution_trial_subject = $config->get('pos_group_notifications_accept_solution_trial_subject');
		$form['group4']['accept']['pos_group_notifications_accept_solution_trial_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_accept_solution_trial_subject,
		);

		$default_body_value = $config->get('pos_group_notifications_accept_solution_trial_body'); 

		$form['group4']['accept']['pos_group_notifications_accept_solution_trial_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [item:url], [reason] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		);

		/***********************/
		//reminder that their solution/trial isn't published
		$form['group5'] = array(
		  '#type' => 'details',		  
		  '#title' => t("Reminder solution/trial isn't published"),
		  '#description' => t("Data used into the Cron task to send e-mails to reminder the group owner/team that the solution/trial isn't published."),
		  //'#description' => t("The group owner/team should ocassionally receive a reminder that their solution/trial isn't published and why."),
		  '#open' => TRUE,
		  '#group' => 'vertical_tabs',
		);
		
		$pos_group_notifications_reminder_solution_trial_not_publised_subject = $config->get('pos_group_notifications_reminder_solution_trial_not_publised_subject');
		$form['group5']['pos_group_notifications_reminder_solution_trial_not_publised_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_reminder_solution_trial_not_publised_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_reminder_solution_trial_not_publised_body'); 
		
		$form['group5']['pos_group_notifications_reminder_solution_trial_not_publised_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [item:url], [reason] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		); 
		/*************************/

		/***********************/
		//
		$form['group6'] = array(
		  '#type' => 'details',		  
		  '#title' => t("Solution Feedback"),
		  '#description' => t("Data used to send a notifiction to the user who posted the feedback when an answer is given or the feedback is published."),
		  '#open' => TRUE,
		  '#group' => 'vertical_tabs',
		);
		
		$form['group6']['feedback_published']  = array(
		  '#type' => 'details',
		  '#title' => t("Feedback is publised"),
		  '#description' => t("Data used when the feedback is published"),
		  '#open' => TRUE,
		  '#default_value' => $config->get('pos_group_notifications_feedback_published_enabled'),
		);

		$form['group6']['feedback_published']['pos_group_notifications_feedback_published_enabled'] = array(		
			'#type'          => 'checkbox',
			'#title' => t('Enable'),
			'#description' => t('Check this checkbox to send an e-mail to the owner of the feedback when it is published.'),
			'#required'      => FALSE,
			'#default_value' => $config->get('pos_group_notifications_feedback_published_enabled'),
		);

		$pos_group_notifications_feedback_published_subject = $config->get('pos_group_notifications_feedback_published_subject');
		$form['group6']['feedback_published']['pos_group_notifications_feedback_published_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_feedback_published_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_feedback_published_body'); 
		
		$form['group6']['feedback_published']['pos_group_notifications_feedback_published_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [feedback:url] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		); 

		$form['group6']['answer_to_feedback']  = array(
		  '#type' => 'details',
		  '#title' => t("Answer is given"),
		  '#description' => t("Data used when an answer is given"),
		  '#open' => TRUE
		);


		$form['group6']['answer_to_feedback']['pos_group_feedback_answer_is_given_enabled'] = array(		
			'#type'          => 'checkbox',
			'#title' => t('Enable'),
			'#description' => t('Check this checkbox to send an e-mail to the owner of the feedback when an answer is given.'),
			'#required'      => FALSE,
			'#default_value' => $config->get('pos_group_feedback_answer_is_given_enabled'),
		);

		$pos_group_notifications_feedback_answered_subject = $config->get('pos_group_notifications_feedback_answered_subject');
		$form['group6']['answer_to_feedback']['pos_group_notifications_feedback_answered_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_feedback_answered_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_feedback_answered_body'); 
		
		$form['group6']['answer_to_feedback']['pos_group_notifications_feedback_answered_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [feedback:url] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		);		
				
		/*******************/
					
		return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {
		$config = $this->config('pos_group_notifications.settings');
		//$config->set('pos_group_notifications_types', $form_state->getValue('pos_group_notifications_types')); 
		
		//g0
		$config->set('pos_group_notifications_testing_mode', $form_state->getValue('pos_group_notifications_testing_mode'));
		$config->set('pos_group_notifications_primary_email', $form_state->getValue('pos_group_notifications_primary_email'));
		
		//g1
		$config->set('pos_group_notifications_subject_message', $form_state->getValue('pos_group_notifications_subject_message'));
		$config->set('pos_group_notifications_subject_message_big_update', $form_state->getValue('pos_group_notifications_subject_message_big_update'));
		$config->set('pos_group_notifications_body_message', $form_state->getValue('pos_group_notifications_body_message'));
		$config->set('pos_group_notifications_body_message_big_update', $form_state->getValue('pos_group_notifications_body_message_big_update'));
		
		
		$config->set('pos_group_notifications_last_reminder_execution', $form_state->getValue('pos_group_notifications_last_reminder_execution'));
		
		if ($form_state->getValue('pos_group_notifications_last_run')) {
			$config->set('pos_group_notifications_last_run', $form_state->getValue('pos_group_notifications_last_run')->__toString());	
		}
		else {
			$config->set('pos_group_notifications_last_run', "");
		}
		
		//g2
		$config->set('pos_group_notifications_request_publication_role_to_notify', $form_state->getValue('pos_group_notifications_request_publication_role_to_notify'));
		$config->set('pos_group_notifications_request_publication_subject', $form_state->getValue('pos_group_notifications_request_publication_subject'));
		$config->set('pos_group_notifications_request_publication_body', $form_state->getValue('pos_group_notifications_request_publication_body'));
		
		//g3		
		$config->set('pos_group_notifications_qa_not_empty_role_to_notify', $form_state->getValue('pos_group_notifications_qa_not_empty_role_to_notify'));
		$config->set('pos_group_notifications_qa_not_empty_subject', $form_state->getValue('pos_group_notifications_qa_not_empty_subject'));
		$config->set('pos_group_notifications_qa_not_empty_body', $form_state->getValue('pos_group_notifications_qa_not_empty_body'));
		
		//g4
		$config->set('pos_group_notifications_reject_solution_trial_subject', $form_state->getValue('pos_group_notifications_reject_solution_trial_subject'));
		$config->set('pos_group_notifications_reject_solution_trial_body', $form_state->getValue('pos_group_notifications_reject_solution_trial_body'));
		$config->set('pos_group_notifications_accept_solution_trial_subject', $form_state->getValue('pos_group_notifications_accept_solution_trial_subject'));
		$config->set('pos_group_notifications_accept_solution_trial_body', $form_state->getValue('pos_group_notifications_accept_solution_trial_body'));
		
		
		//g5
		$config->set('pos_group_notifications_reminder_solution_trial_not_publised_subject', $form_state->getValue('pos_group_notifications_reminder_solution_trial_not_publised_subject'));
		$config->set('pos_group_notifications_reminder_solution_trial_not_publised_body', $form_state->getValue('pos_group_notifications_reminder_solution_trial_not_publised_body'));
				
		//drupal_set_message('-------------------------last run='.$form_state->getValue('pos_group_notifications_last_run'), 'error');
		
		//g6		
		$config->set('pos_group_notifications_feedback_published_enabled', $form_state->getValue('pos_group_notifications_feedback_published_enabled'));		
		$config->set('pos_group_notifications_feedback_published_subject', $form_state->getValue('pos_group_notifications_feedback_published_subject'));
		$config->set('pos_group_notifications_feedback_published_body', $form_state->getValue('pos_group_notifications_feedback_published_body'));
		
		$config->set('pos_group_feedback_answer_is_given_enabled', $form_state->getValue('pos_group_feedback_answer_is_given_enabled'));
		$config->set('pos_group_notifications_feedback_answered_subject', $form_state->getValue('pos_group_notifications_feedback_answered_subject'));
		$config->set('pos_group_notifications_feedback_answered_body', $form_state->getValue('pos_group_notifications_feedback_answered_body'));
		
		$config->save(); // save data in pos_group_notifications.settings
		
		//pos_group_notifications_send_emails();
		
		return parent::submitForm($form, $form_state);
	}

	public function getEditableConfigNames() {
		return ['pos_group_notifications.settings'];
	}

}