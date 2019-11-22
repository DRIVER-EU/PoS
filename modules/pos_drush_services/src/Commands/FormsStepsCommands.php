<?php

namespace Drupal\pos_drush_services\Commands;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drush\Commands\DrushCommands;

class FormsStepsCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Existing or migrated content to be added to a workflow.
   *
   * @param string $workflow
   *   The forms_steps name machine.
   *
   * @param array $options
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @command forms_steps_attach_pos_workflow
   * @aliases fs-attach-pos-workflow
   * @options arr An option that takes multiple values.
   * @options nid : The node's nid.
   *          gid : The group's gid.
   *          content_type: The machine name of the content type.
   *          group_type: The machine name of the group.
   * @usage forms_steps_attach_pos_workflow example_1 --nid='12345678'
   *   Attach the specific node to workflow.
   * @usage forms_steps_attach_pos_workflow example_1 --gid='23'
   *   Attach the specific group to workflow.
   * @usage forms_steps_attach_pos_workflow example_1 --content_type='article'
   *   Attach the specific content type to workflow.
   * @usage forms_steps_attach_pos_workflow example_1 --group_type='solution'
   *   Attach the specific group type to workflow.
   */
  public function coucou($workflow, $options = [
    'nid' => NULL,
    'gid' => NULL,
    'content_type' => NULL,
    'group_type' => NULL,
  ]) {
    if($options['nid']){
      /** @var \Drupal\node\NodeInterface $node */
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($options['nid']);
      if (is_null($node)) {
        $message = $this->t('The node doesn\'t exists');
        $this->outputError($message);
        return;
      }
      try {
        \Drupal::service('pos_drush_services.workflow.manager')
          ->entityInsertWithoutSteps($node, $workflow);
        $message = $this->t('The node "@title" has been updated in the workflow.', ['@title' => $node->getTitle()]);
        $this->output()->writeln($message->render());
      } catch (\Exception $e) {
        $this->outputError($e->getMessage());
      }
      return;
    }

    if($options['gid']){
      /** @var \Drupal\node\NodeInterface $node */
      $group = \Drupal::entityTypeManager()->getStorage('group')->load($options['gid']);
      if (is_null($group)) {
        $message = $this->t('The group doesn\'t exists');
        $this->outputError($message);
        return;
      }
      try {
      	
        \Drupal::service('pos_drush_services.workflow.manager')
          ->entityInsertWithoutSteps($group, $workflow);
		$gid_rec = $group->id[0]->value;
		$glabel = $group->label[0]->value;
        //$message = $this->t('The group "@title" has been updated in the workflow.', ['@title' => $group->getTitle()]);
        $message = $this->t('The group "@id" - "@title" has been updated in the workflow.', ['@id' => $gid_rec, '@title' => $glabel]);
        $this->output()->writeln($message->render());
      } catch (\Exception $e) {
        $this->outputError($e->getMessage());
      }
      return;
    }
	
    if ($options['content_type']) {
      $content_type = $options['content_type'];
      $entity_storage = \Drupal::entityTypeManager()->getStorage('node');
      $query_result = $entity_storage->getQuery()
        ->condition('type', $content_type)
        ->condition('status', NodeInterface::PUBLISHED)
        ->execute();
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadMultiple($query_result);

      foreach ($nodes as $node) {

	$this->yell($node->getTitle(), 40, 'red');

        try {
          \Drupal::service('pos_drush_services.workflow.manager')
            ->entityInsertWithoutSteps($node, $workflow);
          $message = $this->t('The node "@title" has been updated in the workflow.', ['@title' => $node->getTitle()]);
          $this->output()->writeln($message->render());
        } catch (\Exception $e) {
          $this->outputError($e->getMessage());
        }
      }
      return;
    }

    if ($options['group_type']) {
      $group_type = $options['group_type'];
      $entity_storage = \Drupal::entityTypeManager()->getStorage('group');
      $query_result = $entity_storage->getQuery()
        ->condition('type', $group_type)
        //->condition('status', NodeInterface::PUBLISHED)
        ->execute();
      $groups = \Drupal::entityTypeManager()
        ->getStorage('group')
        ->loadMultiple($query_result);

      foreach ($groups as $group) {
		
		$gid_rec = $group->id[0]->value;
		$glabel = $group->label[0]->value;
		
		$this->yell($glabel, 40, 'red');

        try {

          \Drupal::service('pos_drush_services.workflow.manager')
            ->entityInsertWithoutSteps($group, $workflow);

          $message = $this->t('The group "@id" - "@title" has been updated in the workflow.', ['@id' => $gid_rec, '@title' => $glabel]);
          $this->output()->writeln($message->render());
        } catch (\Exception $e) {
          $this->outputError($e->getMessage());
        }
      }
      return;
    }

  }

  private function outputError($message) {
    $this->yell($message, 40, 'red');
  }

}
