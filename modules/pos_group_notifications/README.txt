CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 

INTRODUCTION
------------

This module has been created to send the e-mails notifications for the PoS. The e-mails are:
	- New content in groups (By the cron task): If there is some new or updated content in one of the users groups, an e-mail will be sended.
	- Request publication (After submit the item): When group owner/team member sets the "Request publication", the site editor(s) should receive an e-mail.
	- QA not empty (By the cron task): To send e-mails to the site editors to reminder there that /QA/groups isn't empty.
	- Notification after editor reject or accept a solution/trial (After submit the item):
		- Reject is when an editor (user with the administration role) save a trial/solution with the field 'QA approved?' unchecked and the QA comments text changes. 
		- Accept is when 'QA approved?' is checked
	- Reminder solution/trial isn't publised (By the cron task): To send e-mails to reminder the group owner/team that the solution/trial isn't publised

		
REQUIREMENTS
------------

No special requirements. 
This module needs some other modules that are listed in the dependencies.


INSTALLATION
------------

This module must be placed into the modules/custom folder. Then you can enable it into the admin/modules page.
In the installation this module creates a custom view (pos_notification_group_nodes) that it is used to recover data.
It also needs that the users profile has a boolean field called "I wish to be informed of changes on a site and in my groups per e-mail", the machine name must be "field_accept_email_notifications". Only members with this field checked will receive e-mail notifications by this module.


CONFIGURATION
-------------

This module imports a default configuration but you can change it in admin/config/pos_group_notifications.
By default the module is configurated in demo mode to avoid to do SPAM testing it.
You also need to configure the cron task as your needs in admin/config/system/cron/jobs
