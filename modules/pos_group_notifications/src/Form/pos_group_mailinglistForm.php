<?php

namespace Drupal\pos_group_notifications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\user\Entity\Role;
use \Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Link;
use \Drupal\node\Entity\Node;

class pos_group_mailinglistForm extends ConfigFormBase {

	public function getFormId() {
		return 'pos_group_mailing_list_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		$config = $this->config('pos_group_notifications.settings');  // store data in pos_group_notifications.settings
		$form = parent::buildForm($form, $form_state);

		$pos_group_notifications_testing_mode = $config->get('pos_group_notifications_testing_mode'); 
		$pos_group_notifications_primary_email = $config->get('pos_group_notifications_primary_email');
		
		$extraText = t("It's important to be aware of this:");
		$extraText .= '<ul><li>'.t('With this form you can send an e-mail to the site members.').'</li>';
		$extraText .= '<li>'.t("Only users that has checked the field 'field_accept_email_notifications' in their profiles will receive the e-mail.").'</li>';
		$extraText .= '<li>'.t("The content of the e-mail will be published into the site.").'</li>';
		if ($pos_group_notifications_testing_mode==1) {
			$warningMessage = t("As the module is configurated in demo mode all the e-mails will be sent to this e-mail address: ").$pos_group_notifications_primary_email."."; 
			//drupal_set_message($warningMessage, 'warning');
			
			$extraText .= '<li>'.$warningMessage.'</li>';
				
		}
		$extraText .= '</ul>';
		
		$form['advicemessage'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => $extraText,
			'#suffix' => '</br>',
			//'#tree' => true,
		);

		$form['pos_selection_type_of_members'] = array(
		  '#type' => 'radios',
		  '#title' => t('Please select the recipients of the e-mail'),
		  '#required'      => TRUE,
		  '#options' => array(
		    1 => $this
		      ->t('All the users of the site'),
		    2 => $this
		      ->t('Users of the selected roles'),
		    3 => $this
		      ->t('Members of the selected groups'),
		    4 => $this
		      ->t('Only to me'),
		  ),
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

  
		$form['pos_role_selection'] = array(		
		//'#type' => 'radios',
		//'#type' => 'checkboxes',
		'#type' => 'select',
		'#multiple' => True,		
		'#title' => t('Role selection'),
		'#description' => t('Users that belongs to any of the roles selected will be notified.'),
		'#options' => $rolesToAddInTheList,
		//'#required'      => TRUE,
		'#states' => array(
            'visible' => array(
                ':input[name=pos_selection_type_of_members]' => array('value' => 2),
            ),
            'required' => array(
                ':input[name="pos_selection_type_of_members"]' => array('value' => 2),
            ),
        ),	
		);
				

	$query = \Drupal::entityQuery('group');
  
	$groups_ids = $query->execute();
	
	$groups = \Drupal\group\Entity\Group::loadMultiple($groups_ids);
	
	$groupsToAddInTheList = array();
	$groupsTrialList = array();
	$groupsSolutionList = array();
		
	foreach ($groups as $group) {

		$gid = $group->id[0]->value;
		$glabel = $group->label[0]->value;
		
		$gtype = $group->type[0]->target_id;
		
		//drupal_set_message('------------$glabel:'.$glabel."--".$group->field_qa_approved[0]->value, 'error');
		$extraText = "Not approved";
		
		if ($group->field_qa_approved[0]->value==1) {
			$extraText = "Approved";
		}
		
		//if ($group->field_qa_approved[0]->value==1) {
			if (($gtype=='solution') or ($gtype=='trial')) {			
				//drupal_set_message('------------gid:'.$gid.'-----glabel:'.$glabel.'----gtype:'.$gtype.'---', 'error');
				$groupsToAddInTheList[$gid] = $glabel. " (".ucfirst($gtype)." - ".$extraText.")";
			}
			
			if ($gtype=='solution') {			
				//drupal_set_message('------------gid:'.$gid.'-----glabel:'.$glabel.'----gtype:'.$gtype.'---', 'error');
				$groupsSolutionList[$gid] = $glabel. " (".$extraText.")";
			}
			elseif ($gtype=='trial') {			
				//drupal_set_message('------------gid:'.$gid.'-----glabel:'.$glabel.'----gtype:'.$gtype.'---', 'error');
				$groupsTrialList[$gid] = $glabel. " (".$extraText.")";
			}
		//}			
	}
	
	asort($groupsToAddInTheList);
	asort($groupsSolutionList);
	asort($groupsTrialList);


/*************************/
		$rolesTypes = array(
		    'owner' => $this
		      ->t('Owners'),
		    'team' => $this
		      ->t('Teams'),
		    'contact' => $this
		      ->t('Contact'),		      
		  );

		$form['pos_group_roles_type_selection'] = array(		
		//'#type' => 'radios',
		'#type' => 'checkboxes',
		//'#type' => 'select',
		//'#default_value' => 3,
		'#multiple' => True,
		'#title' => t('Group roles selection'),
		'#description' => t('Users that belongs to any of the selected group roles will be notified.'),
		'#options' => $rolesTypes,
		//'#required'      => TRUE,
		'#states' => array(
            'visible' => array(
                ':input[name=pos_selection_type_of_members]' => array('value' => 3),
            ),
            'required' => array(
                ':input[name=pos_selection_type_of_members]' => array('value' => 3),
            ),
        ),	
		);	  
		  
/*************************/

/******************/
		$groupsTypes = array(
		    1 => $this
		      ->t('Trials'),
		    2 => $this
		      ->t('Solutions'),
			3 => $this
		      ->t('Both')
		  );

		$form['pos_group_type_selection'] = array(		
		//'#type' => 'checkboxes',
		'#type' => 'radios',
		//'#type' => 'select',
		'#default_value' => 3,
		//'#multiple' => True,
		'#title' => t('Type of group'),
		'#description' => t('Select the type of group.'),
		'#options' => $groupsTypes,
		'#states' => array(
            'visible' => array(
                ':input[name=pos_selection_type_of_members]' => array('value' => 3),
            ),
            'required' => array(
                ':input[name=pos_selection_type_of_members]' => array('value' => 3),
            ),
        ),	
		);
		
		$form['pos_trial_group_selection'] = array(		
		//'#type' => 'radios',
		//'#type' => 'checkboxes',
		'#type' => 'select',
		'#multiple' => True,
		'#title' => t('Trials groups selection'),
		'#description' => t('Users that belongs to any of the selected groups will be notified.'),
		'#options' => $groupsTrialList,
		//'#required'      => TRUE,
		'#states' => array(
            'visible' => array(
            	':input[name=pos_selection_type_of_members]' => array('value' => 3),
                ':input[name=pos_group_type_selection]' => array('value' => 1),
            ),
            'required' => array(
            	':input[name=pos_selection_type_of_members]' => array('value' => 3),
                ':input[name="pos_group_type_selection"]' => array('value' => 1),
            ),
        ),	
		);
				

		$form['pos_solutions_group_selection'] = array(		
		//'#type' => 'radios',
		//'#type' => 'checkboxes',
		'#type' => 'select',
		'#multiple' => True,
		'#title' => t('Solutions groups selection'),
		'#description' => t('Users that belongs to any of the selected groups will be notified.'),
		'#options' => $groupsSolutionList,
		//'#required'      => TRUE,
		'#states' => array(
            'visible' => array(
            	':input[name=pos_selection_type_of_members]' => array('value' => 3),
                ':input[name=pos_group_type_selection]' => array('value' => 2),
            ),
            'required' => array(
            	':input[name=pos_selection_type_of_members]' => array('value' => 3),
                ':input[name="pos_group_type_selection"]' => array('value' => 2),
            ),
        ),	
		);
				
/*****************/

		$form['pos_group_selection'] = array(		
		//'#type' => 'radios',
		//'#type' => 'checkboxes',
		'#type' => 'select',
		'#multiple' => True,
		'#title' => t('Groups selection'),
		'#description' => t('Users that belongs to any of the selected groups will be notified.'),
		'#options' => $groupsToAddInTheList,
		//'#required'      => TRUE,
		'#states' => array(
            'visible' => array(
                ':input[name=pos_selection_type_of_members]' => array('value' => 3),
                ':input[name=pos_group_type_selection]' => array('value' => 3),
            ),
            'required' => array(
            	':input[name=pos_selection_type_of_members]' => array('value' => 3),
                ':input[name="pos_group_type_selection"]' => array('value' => 3),
            ),
        ),	
		);

		$form['pos_group_mailinglist_subject_message'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('Subject of the e-mail.'),
			'#required'      => TRUE,
		);
		  
		$form['pos_group_mailinglist_body_message'] = array(
			//'#type' => 'textarea',
			'#type' => 'text_format',
			'#format' => 'basic_html',
			//'#allowed_formats' => ['basic_html', 'full_html'],
			'#allowed_formats' => ['basic_html'],
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('Write here the body of the e-mail.').t('If you use [user:display-name] and/or [site:name] they will  be replaced by the appropriate values.'),
			'#required'      => TRUE,
		);
				
		$default_pos_group_mailinglist_footer_body_message = $config->get('pos_group_mailinglist_footer_body_message');
		
		$form['pos_group_mailinglist_footer_body_message'] = array(
			//'#type' => 'textarea',
			'#type' => 'text_format',
			'#format' => 'basic_html',
			//'#allowed_formats' => ['basic_html', 'full_html'],
			'#allowed_formats' => ['basic_html'],
			'#rows'=> 7,
			'#title' => t('Footer text'),
			'#description' => t('Write here the footer of the e-mail. It will be added in the body.').' '.t('If you use [site:name] and/or [user:edit-url] they will  be replaced by the appropriate value.').' '.('This text will be stored to reuse it in the next e-mail.'),
			'#required'      => TRUE,
			'#default_value' => ($default_pos_group_mailinglist_footer_body_message),
		);

		$form['actions']['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Send e-mail'),
		//'#submit' => array('submitForm'),
		);		
					
		return $form;
	}

	public function validateForm(array &$form, FormStateInterface $form_state) {
			
		//$form_state->setErrorByName('title', t('Your title should be number'));

  		//if pos_selection_type_of_members = 3 -> pos_group_roles_type_selection is mandatory
		
		$values = $form_state->getValues(); 
		//$form_state->setErrorByName('[pos_group_roles_type_selection]', "-->".$values['pos_group_roles_type_selection']."<--");
		
  		if ($form_state->hasValue('pos_selection_type_of_members')) {
     		$pos_selection_type_of_members = $form_state->getValue('pos_selection_type_of_members');
			if ($pos_selection_type_of_members==3) {
				//$form_state->setErrorByName('pos_selection_type_of_members', $pos_selection_type_of_members);
				
				//$pos_group_roles_type_selection = $form_state->getValue('pos_selection_type_of_members');
					
				if ($form_state->hasValue('pos_group_roles_type_selection')) {
					$values_pos_group_roles_type_selection = $values['pos_group_roles_type_selection'];
					
					//$values_pos_group_roles_type_selection = array_filter($form_state->getValue('pos_group_roles_type_selection'));
					
					//$form_state->setErrorByName('pos_group_roles_type_selection',"-->".sizeof($values_pos_group_roles_type_selection)."<---");
					
					$validlistofroles = False;
					
					
					foreach ($values_pos_group_roles_type_selection as $key => $value)
					{
						//$form_state->setErrorByName('pos_group_roles_type_selection_'.$key, "i-->".$value."<--->".$values_pos_group_roles_type_selection[$key]."<----");						
						if ($value) {							
							$validlistofroles = True;
						}
					}
					
					if ($validlistofroles==False) {
						$form_state->setErrorByName('pos_group_roles_type_selection',t('Select a group role.'));
					}
					
				}
				
			}
  		}		

  		//if Type of group (pos_group_type_selection) is:
  		// - 1 = Trials: field pos_trial_group_selection is mandatory
  		// - 2 = Solutions: field pos_solutions_group_selection is mandatory
  		// - 3 = Both: field pos_group_selection is mandatory
  		
		$pos_selection_type_of_members = $form_state->getValue('pos_selection_type_of_members');
		if ($pos_selection_type_of_members==3) {
			if ($form_state->hasValue('pos_group_type_selection')) {
				
				$value_pos_group_type_selection = $values['pos_group_type_selection'];
				
				//$form_state->setErrorByName('pos_group_type_selection',"value_pos_group_type_selection-->".$value_pos_group_type_selection);
				$fieldToCheck = '';
				if ($value_pos_group_type_selection == 1) {
					
					$fieldToCheck = 'pos_trial_group_selection';
					
				}
				elseif ($value_pos_group_type_selection == 2) {
	
					$fieldToCheck = 'pos_solutions_group_selection';				
					
				}
				elseif ($value_pos_group_type_selection == 3) {
	
					$fieldToCheck = 'pos_group_selection';				
					
				}
				
				$values_to_check = $values[$fieldToCheck];
				
				
				$cntGroups = 0;
				foreach ($values_to_check as $key => $value) {
					$cntGroups = $cntGroups +1;
					
				}
				
				if ($cntGroups==0) {
					$form_state->setErrorByName($fieldToCheck, t('Select a group.'));
				}
				
			}
		}
  		
	}
	
	public function submitForm(array &$form, FormStateInterface $form_state) {
				
		//drupal_set_message('------------TESTING-------------', 'error');
		$config = $this->config('pos_group_notifications.settings');
		
		$footerText = $form_state->getValue('pos_group_mailinglist_footer_body_message');
		$footerText = $footerText['value'];	
		$config->set('pos_group_mailinglist_footer_body_message', $footerText);
		$config->save();
		
		$pos_selection_type_of_members = $form_state->getValue('pos_selection_type_of_members');
		$finalUsersArray = array();
		
		if ($pos_selection_type_of_members==1) {
			//we must recover all active users
		    //recover the list of users enabled
			$ids = \Drupal::entityQuery('user')
				->condition('status', 1)  
				->execute();
		
			$users = User::loadMultiple($ids);	
			$finalUsersArray = $users;

			foreach ($users as $user) {
				$uid = $user->get('uid')->value;
				$finalUsersArray[$uid]=$user;					
			}			
			
		}
		else if ($pos_selection_type_of_members==2) {
			//we must recover users form the selected roles
			//pos_role_selection
			$pos_role_selection = $form_state->getValue('pos_role_selection');
			foreach ($pos_role_selection as $key=>$role_name) {
				//drupal_set_message('------------pos_role_selection--key->'.$role_name.'-----value->'.$role_name.'--------', 'error');

				$ids = \Drupal::entityQuery('user')
				->condition('status', 1)
				->condition('roles', $role_name)
				->execute();
				
				$users = User::loadMultiple($ids);
				
				foreach ($users as $user) {
					
					$uid = $user->get('uid')->value;
						
					//drupal_set_message('------------users--uid->'.$uid.'-------------', 'error');
					
					if (!in_array($uid, $finalUsersArray)) {
    					$finalUsersArray[$uid]=$user;
					}
					
					
				}

			}
						
		}
		else if ($pos_selection_type_of_members==3) {
			//we must recover users form the selected groups
			//pos_group_mailinglist_body_message
			
			
			$pos_group_type_selection = $form_state->getValue('pos_group_type_selection');
						
			$pos_trial_group_selection = $form_state->getValue('pos_trial_group_selection');
			$pos_solutions_group_selection = $form_state->getValue('pos_solutions_group_selection');			
			$pos_group_selection = $form_state->getValue('pos_group_selection');
			
			$pos_group_roles_type_selection = $form_state->getValue('pos_group_roles_type_selection');
			
			if ($pos_group_type_selection==1) {
				$listRolesToUse = $pos_trial_group_selection;
			}
			elseif ($pos_group_type_selection==2) {
				$listRolesToUse = $pos_solutions_group_selection;
			}
			elseif ($pos_group_type_selection==3) {
				$listRolesToUse = $pos_group_selection;
			}
			
			//foreach ($pos_group_selection as $key=>$group_id) {
			foreach ($listRolesToUse as $key=>$group_id) {
				//drupal_set_message('------------pos_group_selection--key->'.$key.'-----group_id->'.$group_id.'--------', 'error');
				
				//$group = Drupal\group\Entity\Group::load($key);	
				$group = \Drupal\group\Entity\Group::load($key);
				
				$gid = $group->id[0]->value;
				$glabel = $group->label[0]->value;
				
				
				//drupal_set_message('------------pos_group_selection--key->'.$key.'-----glabel->'.$glabel.'--------', 'error');
				$members = $group->getMembers();
				foreach ($members as $member) {
					$user = $member->getUser();
					$uid = $user->get('uid')->value;
					
					$uroles = $member->getRoles();
					//drupal_set_message('------------uid->'.$uid.'-----uroles->'.$uroles.'<--------pos_group_roles_type_selection-->'.$pos_group_roles_type_selection.'<--', 'error');
					
					$validUser = False;
					foreach ($uroles as $roleK => $roleV) {
						
						//$roleK = role of the user
						//we check if this role is in the list of $pos_group_type_selection
						foreach ($pos_group_roles_type_selection as $selection) {
							
							if (strpos($roleK, $selection) !== false) {
								$validUser = True;
							}
							
							
						}
						 
						//drupal_set_message('------------uid->'.$uid.'-----role->'.$roleK.'<--------', 'error');
					}
					
					if ($validUser) {
						//drupal_set_message('----->'.$uid.'<-------VALID USER--------', 'error');
						if (!in_array($uid, $finalUsersArray)) {
    						$finalUsersArray[$uid]=$user;
						}
					}
					else {
						//drupal_set_message('------>'.$uid.'<------NOT A VALID USER--------', 'error');
					}
					
				}
				
			}
			
		}
		else if ($pos_selection_type_of_members==4) {
			//email only to me
			//$uid = $current_user->id();
			$uid = \Drupal::currentUser()->id();
			$current_user = \Drupal::currentUser();
			$user = \Drupal\user\Entity\User::load($uid);
			
			$finalUsersArray[$uid] = $user;
			
		}
		
		$keyEmail = 'pos_group_notifications_mailing_list'; 	
		$subject_email = $form_state->getValue('pos_group_mailinglist_subject_message');
		$original_body_email = $form_state->getValue('pos_group_mailinglist_body_message');
		//if we us a wysiwyw editor we need to read position 0
		
				
		//if type = text_format we need to use this
		$original_body_email = $original_body_email['value'];
		//drupal_set_message("original_body_email:".$original_body_email);
		
		
		//we add the footer text
		$original_body_email_to_create_node = $original_body_email; 
		$original_body_email .=	'<p> </p>'.$footerText;
		
		// access to the main site data
		$system_site_config = \Drupal::config('system.site');
		$site_name = $system_site_config->get('name');
			
		//drupal_set_message('------------pos_selection_type_of_members->'.$pos_selection_type_of_members.'-------------', 'error');
		//drupal_set_message('------------pos_role_selection->'.$pos_role_selection.'-------------', 'error');
		//drupal_set_message('------------pos_group_selection->'.$pos_group_selection.'-------------', 'error');
		//drupal_set_message('------------subject_email->'.$subject_email.'-------------', 'error');
		//drupal_set_message('------------body_email->'.$body_email.'-------------', 'error');
		
		$cntEmailsSended = 0;
		$finalListOfUsersToInsert = [];
		
		
		foreach ($finalUsersArray as $key=>$value) {
						
			//$finalListOfUsersToInsert[] = ['value' => $key];
			$finalListOfUsersToInsert[] = $key;
			
			$realname = $value->get('field_real_name')->value;
			$mail = $value->get('mail')->value;
			//$realname = $user->get('field_real_name')->value;
			
			//drupal_set_message('------------finalUsersArray--key->'.$key.'-------------realname:'.$realname.'--------mail:'.$mail.'<----', 'error');
			
			$body_email = $original_body_email;
			$body_email = str_replace("[user:display-name]", $realname, $body_email);
			$body_email = str_replace("[site:name]", $site_name, $body_email);
			
			global $base_url;
			$host = $base_url;
			
			//$editUserUrl = '<a href="'.$host.'/user/'.$key.'/edit'.'">your user profile</a>';
			
			$editUserUrl = $host.'/user/'.$key.'/edit';
			
			//drupal_set_message('editUserUrl='.$editUserUrl);
			
			$body_email = str_replace("[user:edit-url]", $editUserUrl, $body_email);
			
			$body_email  = html_entity_decode( $body_email);
			$body_email = render($body_email);
		
			pos_send_custom_email($mail, $subject_email, $body_email, $keyEmail);
			$cntEmailsSended = $cntEmailsSended + 1;
		}


		//creating a node


		// Create node object
		//drupal_set_message('------------finalUsersArray--key->'.$finalListOfUsersToInsert.'<-----------------', 'error');
		
		$node = Node::create([
			'type'        => 'pos_email_notification',
  			'title'       => $subject_email,
  			//'body'       => $body_email,
  			'langcode' => 'en',
  			'body' => ['format' => 'basic_html', 'value' => $original_body_email_to_create_node],
  			'field_notification_recipients' => $finalListOfUsersToInsert
		]);
		$node->save();				
		
		$nid = $node->id();
		
		global $base_url;
		$host = $base_url;
		
		$link = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();

		if ($cntEmailsSended>0) {
			drupal_set_message(t('The e-mails have been sent.'));
		}
		else {
			drupal_set_message(t('There are not users with this filter conditions.'));
		}

		drupal_set_message(t('The e-mail has been published <a href="@link">here</a>.', array('@link' => $link)));
		
	    //$messenger = \Drupal::messenger();
	    //$messenger->addMessage('Title: ');
	    //$messenger->addMessage('Accept: ');
	
	    // Redirect to home
	    //$form_state->setRedirect('<front>');
				
		
		//return parent::submitForm($form, $form_state);
	}

	public function getEditableConfigNames() {
		return ['pos_group_notifications.settings'];
	}

}