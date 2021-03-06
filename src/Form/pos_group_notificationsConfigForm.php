<?php

namespace Drupal\pos_group_notifications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\user\Entity\Role;

use Drupal\Core\Url;
use Drupal\Core\Link;

use Drupal\Core\Form\FormBase;



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
		  //'#title' => t('Request publication'),
		  '#title' => t("Publications messages"),
		  //'#description' => t('When group owner/team member sets the "Request publication", the site editor(s) should receive an e-mail.'),
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

		$form['group2']['requestpublishing']  = array(
		  '#type' => 'details',
		  '#title' => t("Request publication"),
		  '#description' => t('Used when user changes the “approval requested” (field field_publish)  to true and qa approved (field field_qa_approved) is false.'),
		  '#open' => TRUE
		);
				
		$pos_group_notifications_request_publication_subject = $config->get('pos_group_notifications_request_publication_subject');
		$form['group2']['requestpublishing']['pos_group_notifications_request_publication_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			//'#default_value' => 'Subject....',
			'#default_value' => $pos_group_notifications_request_publication_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_request_publication_body'); 
		
		$form['group2']['requestpublishing']['pos_group_notifications_request_publication_body'] = array(
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

        $form['group2']['publishedgroup']  = array(
		  '#type' => 'details',
		  '#title' => t("Published"),
		  '#description' => t('Used when user changes the “approval requested” (field field_publish) to true and qa approved (field_qa_approved) is true.'),
		  '#open' => TRUE
		);
				
		$pos_group_notifications_publication_subject = $config->get('pos_group_notifications_publication_subject');
		$form['group2']['publishedgroup']['pos_group_notifications_publication_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			//'#default_value' => 'Subject....',
			'#default_value' => $pos_group_notifications_publication_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_publication_body'); 
		
		$form['group2']['publishedgroup']['pos_group_notifications_publication_body'] = array(
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
		  '#description' => t("Data used into the Cron task to send e-mails to the site editors to reminder there that the '/QA/groups' view isn't empty when some of the groups in that view has the field 'Approval requested?' (field_publish) = True and the field 'QA approved?' (field_qa_approved) = False."),
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


		/***********************/
		//Groups with validation errors
		$form['group7'] = array(
		  '#type' => 'details',		  
		  '#title' => t("Groups with validation errors"),
		  '#description' => t("Data used into the Cron task to send e-mails (one time per month) to inform the members of a group that their group has some validation error."),
		  '#open' => TRUE,
		  '#group' => 'vertical_tabs',
		);

		$form['group7']['pos_group_notifications_group_validation_error_enabled'] = array(		
			'#type'          => 'checkbox',
			'#title' => t('Enable'),
			'#description' => t('Check this checkbox to send an e-mail to the users if their group has some validation error.'),
			'#required'      => FALSE,
			'#default_value' => $config->get('pos_group_notifications_group_validation_error_enabled'),
		);


/***********/
		$groupsTypes = array(
		    'trial' => $this
		      ->t('Trials'),
		    'solution' => $this
		      ->t('Solutions'),
			'country_profile' => $this
		      ->t('Country Profile')
		  );

		$rolesTypes = array(
		    'owner' => $this
		      ->t('Owners'),
		    'team' => $this
		      ->t('Teams'),
		    'contact' => $this
		      ->t('Contact'),		      
		  );
		 
		//https://openwritings.net/pg/drupal/drupal-8-create-table-form-input-fields

		
		$form['group7']['markup7_1'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => t('Configure each group type. Only the members of the selected roles per group type will receive an e-mail.'),
			'#suffix' => '</br>',
			//'#tree' => true,
		);
						
		$form['group7']['pos_group_notifications_group_validation_configgroups'] = array(
            '#type' => 'table',
            '#title' => 'Configuration groups table',
            '#header' => array('Group Type', 'Roles', 'View Id', 'Display Id', 'Sentece to check to assume the group is buildt properly'),
		);
 
        // Add input fields in table cells.
        //for ($i=1; $i<=count($groupsTypes); $i++) {
        foreach ($groupsTypes as $k=>$v) {
        	//drupal_set_message($config->get('pos_group_notifications_group_validation_configgroups')[$k]['group']);
            $form['group7']['pos_group_notifications_group_validation_configgroups'][$k]['group'] = array(
				'#type'          => 'checkbox',
				'#title' => $v,
				'#required'      => FALSE,
				'#default_value' => $config->get('pos_group_notifications_group_validation_configgroups')[$k]['group'],
			);

 			$defaultValueCheckboxes = array();
			foreach ($config->get('pos_group_notifications_group_validation_configgroups')[$k]['rolesbygroup'] as $k_item=> $v_item) {
				if ($k_item===$v_item) {				
					array_push($defaultValueCheckboxes, $v_item);
				}
			}


            $form['group7']['pos_group_notifications_group_validation_configgroups'][$k]['rolesbygroup'] = array(
                                                '#type' => 'checkboxes',
                                                '#options' => $rolesTypes,
                                                //'#attributes' => array('checked' => 'checked'),
                                                //'#default_value' => array_keys($config->get('pos_group_notifications_group_validation_configgroups')[$k]['rolesbygroup']),
                                                '#default_value' => $defaultValueCheckboxes,
                                            );
											
			$form['group7']['pos_group_notifications_group_validation_configgroups'][$k]['viewId'] = array(
				'#type' => 'textfield',
				//'#title' => t('View Id'),
				'#maxlength' => 128,
				'#size' => 20,
				'#required'      => FALSE,
				'#default_value' => $config->get('pos_group_notifications_group_validation_configgroups')[$k]['viewId'],
				//'#states' => array(
			    //	'visible' => array(
				//		':input[name="pos_group_notifications_group_validation_configgroups['.$k.'][group]"]' => array(
				 //           array('checked' => TRUE),
				 //       ),
				 //   ),
				//),
			);

			$form['group7']['pos_group_notifications_group_validation_configgroups'][$k]['displayId'] = array(
				'#type' => 'textfield',
				//'#title' => t('Display Id'),
				'#maxlength' => 128,
				'#size' => 20,
				'#required'      => FALSE,
				'#default_value' => $config->get('pos_group_notifications_group_validation_configgroups')[$k]['displayId'],
			);			

			$form['group7']['pos_group_notifications_group_validation_configgroups'][$k]['sentence'] = array(
				'#type' => 'textfield',
				//'#title' => t('Sentece'),
				'#maxlength' => 128,
				'#size' => 30,
				'#required'      => FALSE,
				'#default_value' => $config->get('pos_group_notifications_group_validation_configgroups')[$k]['sentence'],
			);									
											                                            
        }

  

                
		$form['group7']['markup7_2'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => '',
			'#suffix' => '</br>',
			//'#tree' => true,
		); 

/***********/
		  


		$pos_group_notifications_group_validation_subject = $config->get('pos_group_notifications_group_validation_subject');
		$form['group7']['pos_group_notifications_group_validation_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_group_validation_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_group_validation_body'); 
		
		$form['group7']['pos_group_notifications_group_validation_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [dynamic_content] and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		); 

		/*******************/

		
		/********* expiring email notifications **************/

		$form['group8'] = array(
		  '#type' => 'details',
		  '#title' => t('Expiring notifications'),
		  '#open' => TRUE,
		  //'#required'      => TRUE,
		  '#group' => 'vertical_tabs',
		);
				
		$form['group8']['expiringnotificationintroduction'] = array(
			'#type' => 'markup',
			'#prefix' => '</br>',
			'#markup' => t('Here you can configure which content types and which group types will be take into account to send expiration emails. When the system finds some content where their updated date was older than the expiration date configured (latests updated date + number of months configured into expiration time < today), it will send a notification to the author of the item. This process will be executed one time per month.'),
			'#suffix' => '</br>',
			//'#tree' => true,
		);

		$optionsMonths = [];
		for ($i = 0; $i <= 24; $i++) {
			$extraLabel = 'months';
			if ($i==0) {
				$extraLabel = 'never';
			}
			else if ($i==1) {
				$extraLabel = 'month';	
			}
			
			if ($i==0) {
				$optionsCT[$i] = t($extraLabel);
			}
			else {
				$optionsCT[$i] = $i." ".t($extraLabel);	
			}
    		
		}
		
		$default_pos_group_notifications_expiration_months = $config->get('pos_group_notifications_expiration_months'); 
		
 		$form['group8']['pos_group_notifications_expiration_months'] = array(
			'#type'          => 'select',
			'#title'         => t('Expiration time'),
			'#description' => t('Select the expiration time for the content. If you select never, items will never be processed as expired.'),
			'#required'      => TRUE,	
			'#options' => $optionsCT,
			'#default_value' => $default_pos_group_notifications_expiration_months,	
		);
		
				
		$form['group8']['nodes']  = array(
		  '#type' => 'details',
		  '#title' => t("Content types"),
		  '#description' => t('Select the content types that the system will take into account.'),
		  '#open' => FALSE
		);
		
		$node_types = \Drupal\node\Entity\NodeType::loadMultiple();
		// If you need to display them in a drop down:
		$optionsCT = [];
		foreach ($node_types as $node_type) {
  			$optionsCT[$node_type->id()] = $node_type->label();
		}
		
		$default_pos_group_notifications_expiration_node_types = $config->get('pos_group_notifications_expiration_node_types');

 		$form['group8']['nodes']['pos_group_notifications_expiration_node_types'] = array(
			'#type'          => 'checkboxes',
			'#title'         => t('Content types'),
			//'#description' => t('Select the CT you wish.'),
			'#required'      => FALSE,	
			'#options' => $optionsCT,	
			'#default_value' => $default_pos_group_notifications_expiration_node_types,	
		);

		$form['group8']['groups']  = array(
		  '#type' => 'details',
		  '#title' => t("Groups"),
		  '#description' => t('Select the group types that the system will take into account.'),
		  '#open' => FALSE
		);		
						
		$group_types = \Drupal\group\Entity\GroupType::loadMultiple();
		// If you need to display them in a drop down:
		$optionsGT = [];
		foreach ($group_types as $group_type) {
  			$optionsGT[$group_type->id()] = $group_type->label();
		}
		
		$default_pos_group_notifications_expiration_group_types = $config->get('pos_group_notifications_expiration_group_types');

 		$form['group8']['groups']['pos_group_notifications_expiration_group_types'] = array(
			'#type'          => 'checkboxes',
			'#title'         => t('Groups types'),
			//'#description' => t('Select the group you wish.'),
			'#required'      => FALSE,	
			'#options' => $optionsGT,	
			'#default_value' => $default_pos_group_notifications_expiration_group_types,
		);

		$pos_group_notifications_expiration_subject = $config->get('pos_group_notifications_expiration_subject');
		$form['group8']['pos_group_notifications_expiration_subject'] = array(
			'#type' => 'textfield',
			'#title' => t('Subject e-mail'),
			'#maxlength' => 128,
			'#description' => t('[site:name] will be replaced by the appropriate value.'),
			'#required'      => TRUE,
			'#default_value' => $pos_group_notifications_expiration_subject,
		);
		
		$default_body_value = $config->get('pos_group_notifications_expiration_body'); 
		
		$form['group8']['pos_group_notifications_expiration_body'] = array(
			'#type' => 'textarea',
			'#rows'=> 7,
			'#title' => t('Body e-mail'),
			'#description' => t('[user:display-name], [item:url] (is the label + the url) and [site:name] will be replaced by the appropriate values.'),
			'#required'      => TRUE,
			'#default_value' => $default_body_value,
		); 

		/********************/
							
		return $form;
	}

	public function validateForm(array &$form, FormStateInterface $form_state) {
    	parent::validateForm($form, $form_state);


    	$configValidationGroups = $form_state->getValue('pos_group_notifications_group_validation_configgroups');
    	//$accept = $form_state->getValue('accept');
		foreach ($configValidationGroups as $gK => $gV) {
			//drupal_set_message($gK.'---'.(string) $gV.'<***','error');

			
			$roleSelectedByGroup = False;
			$errorXviewId = False;
			$errorXdisplayId = False;
			$errorXsentence = False;
				
				
			if ($configValidationGroups[$gK]['group']==1) {
				//let's check if there is a role selected
				//drupal_set_message("let's check if there is a role selected for ".$gK,'error');
				foreach ($configValidationGroups[$gK]['rolesbygroup'] as $gK2 => $gV2) {
					//drupal_set_message('----'.$gK.'---'.$gK2.'---'.(string) $gV2.'<***','error');
					if ($gK2 === $gV2) {
						$roleSelectedByGroup = True;
						//drupal_set_message("let's check if there is a role selected for ".$gK."--equal",'error');
					}
					else {
						//drupal_set_message("let's check if there is a role selected for ".$gK."--different",'error');
					}
				}
				
				
				if (strlen($configValidationGroups[$gK]['viewId']) === 0)
				{
					$errorXviewId = True;
				}
				
				if (strlen($configValidationGroups[$gK]['displayId']) === 0)
				{
					$errorXdisplayId = True;
				}
				
				if (strlen($configValidationGroups[$gK]['sentence']) === 0)
				{
					$errorXsentence = True;
				}
				
			}
			else {
				$roleSelectedByGroup = True;
			}
			
			if (!$roleSelectedByGroup) {				
				$form_state->setErrorByName('pos_group_notifications_group_validation_configgroups]['.$gK.'][rolesbygroup', $this->t('Select a role in '.$gK.'.'));
			}
			if ($errorXviewId) {
				$form_state->setErrorByName('pos_group_notifications_group_validation_configgroups]['.$gK.'][viewId', $this->t('Add the id of a view in '.$gK.'.'));
			}
			if ($errorXdisplayId) {
				$form_state->setErrorByName('pos_group_notifications_group_validation_configgroups]['.$gK.'][displayId', $this->t('Add the id of a display in '.$gK.'.'));
			}
			if ($errorXsentence) {
				$form_state->setErrorByName('pos_group_notifications_group_validation_configgroups]['.$gK.'][sentence', $this->t('Add a sentece in '.$gK.'.'));
			}			
		
			
			
			//['group']
		}
    	//if (strlen($title) < 10) {
      		// Set an error for the form element with a key of "title".
      		//$form_state->setErrorByName('title', $this->t('The title must be at least 10 characters long.'));

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
		
		$config->set('pos_group_notifications_publication_subject', $form_state->getValue('pos_group_notifications_publication_subject'));
		$config->set('pos_group_notifications_publication_body', $form_state->getValue('pos_group_notifications_publication_body'));
		
		
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


		//g7		
		$config->set('pos_group_notifications_group_validation_error_enabled', $form_state->getValue('pos_group_notifications_group_validation_error_enabled'));		
		$config->set('pos_group_notifications_group_validation_subject', $form_state->getValue('pos_group_notifications_group_validation_subject'));
		$config->set('pos_group_notifications_group_validation_body', $form_state->getValue('pos_group_notifications_group_validation_body'));
	
		$config->set('pos_group_notifications_group_validation_configgroups', $form_state->getValue('pos_group_notifications_group_validation_configgroups'));
		//$values = $form_state->getValues();
		//drupal_set_message(print_r($values['pos_group_notifications_group_validation_configgroups'],true));
		
		
		//g8		
		$config->set('pos_group_notifications_expiration_months', $form_state->getValue('pos_group_notifications_expiration_months'));
		$config->set('pos_group_notifications_expiration_node_types', $form_state->getValue('pos_group_notifications_expiration_node_types'));		
		$config->set('pos_group_notifications_expiration_group_types', $form_state->getValue('pos_group_notifications_expiration_group_types'));
		
		$config->set('pos_group_notifications_expiration_subject', $form_state->getValue('pos_group_notifications_expiration_subject'));
		$config->set('pos_group_notifications_expiration_body', $form_state->getValue('pos_group_notifications_expiration_body'));
		
		
		$config->save(); // save data in pos_group_notifications.settings
		
		//pos_group_notifications_send_emails();
		
		return parent::submitForm($form, $form_state);
	}

	public function getEditableConfigNames() {
		return ['pos_group_notifications.settings'];
	}

}