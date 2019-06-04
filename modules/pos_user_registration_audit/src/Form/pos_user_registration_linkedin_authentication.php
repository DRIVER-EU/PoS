<?php

namespace Drupal\pos_user_registration_audit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class pos_user_registration_linkedin_authentication extends ConfigFormBase {

	public function getFormId() {
		return 'pos_user_registration_linkedin_authentication';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {


		//Recover how is configured the user registration in the site
		$configAdmin = \Drupal::config('user.settings');
		$typeOfRegistration = $configAdmin->get('register');
	
		if ($typeOfRegistration=='visitors_admin_approval') {
			
			$message = t("When an user is logged using the Social Auth LinkedIn module, if this user don't exists in the site it is created by the system. Is is created as Active but the autologin fails. With this module we can select how we manage this issue. To detect that a user has been created by the Social Auth LinkedIn module we check if the mandatory field real_name is empty or not, if user has been created manaully this field is filled.");
			
			$form['message'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => $message,
			'#suffix' => '</br>',
			'#weight' => -1
			);
						
			//$configAudit = $this->config('pos_user_registration_audit.settings');			
			$configAudit = \Drupal::config('pos_user_registration_audit.settings');
			
			$pos_linkedin_new_user_enabled = $configAudit->get('pos_linkedin_new_user_enabled');
			if ($pos_linkedin_new_user_enabled=="") {
				//$pos_linkedin_new_user_enabled = 0;	
			}

	 		$form['pos_linkedin_new_user_enabled'] = array(
				'#type'          => 'checkbox',
				'#title' => t('Track users created by LinkedIn'),
				'#description' => t('Enabling this functionality users that will be created automatically by LinkedIn will be tracked to set the correct status.'),
				//'#required'      => FALSE,
				'#default_value' => $pos_linkedin_new_user_enabled,
			);

			$pos_linkedin_new_user_behavior_config = $configAudit->get('pos_linkedin_new_user_behavior_config');
			if ($pos_linkedin_new_user_behavior_config=="") {
				$pos_linkedin_new_user_behavior_config = 1;	
			}	

			$groupsTypes = array(
			    1 => $this
			      ->t('New users will be blocked until an administrator validates them.'),
			    2 => $this
			      ->t('New users will be accepted automatically.'),
			  );
	
	
			$form['pos_linkedin_new_user_behavior_config'] = array(		
				'#type' => 'radios',
				'#default_value' => $pos_linkedin_new_user_behavior_config,
				'#title' => t('Choose the behavior of the platform when a new user is logged by LinkedIn'),
				'#description' => t('Select the type of behavior. This only applies if the registration of new users is configured with "Visitors, but administrator approval is required"'),
				'#options' => $groupsTypes,			
	            'required' => True	
			);
				
			$form['actions']['submit'] = array(
				'#type' => 'submit',
				'#value' => t('Save'),
				'#weight' => 100
			);	
				
		}
		else {
			
			$message = t('This configuration don\'t apply because the registration of new users is not configured with the option "Visitors, but administrator approval is required"');
			
			$form['message'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => $message,
			'#suffix' => '</br>',
			'#weight' => 50
			//'#tree' => true,
		);
		}
							
		return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {

		$config = $this->config('pos_user_registration_audit.settings');
		
		//drupal_set_message("---pos_linkedin_new_user_behavior=".$form_state->getValue('pos_linkedin_new_user_behavior'));
		
		$config->set('pos_linkedin_new_user_enabled', $form_state->getValue('pos_linkedin_new_user_enabled'));
		$config->set('pos_linkedin_new_user_behavior_config', $form_state->getValue('pos_linkedin_new_user_behavior_config'));
		
		$config->save();
		
		return parent::submitForm($form, $form_state);
	}

	public function getEditableConfigNames() {
		return ['pos_user_registration_audit.settings'];
	}

}