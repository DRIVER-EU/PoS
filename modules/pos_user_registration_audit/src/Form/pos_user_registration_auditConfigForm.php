<?php

namespace Drupal\pos_user_registration_audit\Form;

use Drupal\user\Entity\User;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Datetime\DrupalDateTime;
//use Drupal\user\Entity\Role;

//use Drupal\Core\Url;
//use Drupal\Core\Link;


function recoverUsers_witoutaccount_enabled_pos_user_registration_audit() {
	
	$query = \Drupal::entityQuery('user')
		->condition('status', 1);
		
		//->condition('field_account_enabled_by','value', 'NULL')
		//->execute();
	$query->notExists('field_account_enabled_by');
    $ids=$query->execute();
		
	$users = User::loadMultiple($ids);
	
	return 	$users;	
}

class pos_user_registration_auditConfigForm extends ConfigFormBase {

	public function getFormId() {
		return 'pos_user_registration_audit_config_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		
		
		$usersList = recoverUsers_witoutaccount_enabled_pos_user_registration_audit();
		
		if (sizeof($usersList)>0) {
			
			$form = parent::buildForm($form, $form_state);
			$message = t('There are @numberOfUsers users without the field field_account_enabled_by fulfilled.', ['@numberOfUsers' => sizeof($usersList)]);
			$message .= "</br>";
			$message .= t('To update them select a user and click over the button.');


			$form['uid'] = [
			    '#type' => 'entity_autocomplete',
			    '#target_type' => 'user',   		    
				'#required'      => TRUE,
			    // A comment can be made anonymous by leaving this field empty therefore
			    // there is no need to list them in the autocomplete.
			    '#selection_settings' => ['include_anonymous' => FALSE],
			    '#title' => $this->t('Select an user'),
			    '#description' => t('The selected user will be used to fulfill the field field_account_enabled_by of the users that need it.'),
			    '#weight' => 99
			  ];
			
			$form['actions']['submit'] = array(
				'#type' => 'submit',
				'#value' => t('Update pending users'),
				'#weight' => 100
			);	
			
			
		}
		else {
			$message = t('All user has the field field_account_enabled_by fulfilled.');
		}
		
		//drupal_set_message("!!!user array length--->".sizeof($usersList)."<------", 'error');

		$form['message'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => $message,
			'#suffix' => '</br>',
			'#weight' => 50
			//'#tree' => true,
		);
							
		return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {

		
		$users = recoverUsers_witoutaccount_enabled_pos_user_registration_audit();
		
		foreach ($users as $user) {
			$uid = $user->get('uid')->value;
			//$field_account_enabled_by = $user->get('field_account_enabled_by')->value;
			$field_account_enabled_by = $user->get('field_account_enabled_by')->getValue();
			
			
			if ($field_account_enabled_by[0]['target_id']) {
				
			}
			else {
				
				$uidToUseAsDefaultValue = $form_state->getValue('uid');
				
				//drupal_set_message("!!!user to update uid->".$uid."<------uidToUseAsDefaultValue-->".$uidToUseAsDefaultValue."<-----", 'error');
				
				//user to be updated
				// Set the field value new value.
				$user->set('field_account_enabled_by', $uidToUseAsDefaultValue);
				// Save the $user object, else changes won't persist.
				$user->save();
			}
			
			/*
			drupal_set_message("uid->".$uid."<------field_account_enabled_by->".$field_account_enabled_by[0]['target_id']."<----", 'error');
			
			foreach ($field_account_enabled_by as $k=>$v) {
				drupal_set_message("uid->".$uid."<--k--->".$k."<-----v->".$v."<---",'error');
				
				foreach ($v as $k2=>$v2) {
					drupal_set_message("uid->".$uid."<--k2--->".$k2."<-----v2->".$v2."<---",'error');
				}
			}
			*/
								
		}			
		
		//pos_group_notifications_send_emails();
		
		return parent::submitForm($form, $form_state);
	}

	public function getEditableConfigNames() {
		return ['pos_group_notifications.settings'];
	}

}