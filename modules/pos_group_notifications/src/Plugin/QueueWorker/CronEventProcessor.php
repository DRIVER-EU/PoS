<?php
/**
 *
 * PHP Version 5
 */
 
namespace Drupal\pos_group_notifications\Plugin\QueueWorker;
 
/**
 *
 * @QueueWorker(
 * id = "pos_group_notifications_mailing_list",
 * title = "My Queue Worker to send newsletter notifications",
 * cron = {"time" = 120}
 * )
 */
class CronEventProcessor extends EmailEventBase {
 
}