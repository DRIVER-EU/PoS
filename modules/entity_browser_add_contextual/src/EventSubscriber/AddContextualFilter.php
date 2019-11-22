<?php

/**
 * @file
 * Contains \Drupal\entity_browser_add_contextual\EventSubscriber\AddContextualFilter.
 */

namespace Drupal\entity_browser_add_contextual\EventSubscriber;

use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\AlterEntityBrowserDisplayData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Event Subscriber MyEventSubscriber.
 */
class AddContextualFilter implements EventSubscriberInterface {

  /**
   * Code that should be triggered on event specified 
   */
  public function onAlterData(AlterEntityBrowserDisplayData $event) {

    $data = $event->getData();
    $original_path = $data['query_parameters']['query']['original_path'];
    // add route parameters of the current path to the query parameters
    $params = \Drupal\Core\Url::fromUserInput($original_path)->getRouteParameters();
    $data['query_parameters']['query'] = array_merge($data['query_parameters']['query'],$params);
    // split the original path into its parts and add them to the query parameters
    $pathsegments = explode('/',$original_path);
    array_shift($pathsegments);
    $data['query_parameters']['query'] = array_merge($data['query_parameters']['query'],$pathsegments);

    $event->setData($data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    $events[Events::ALTER_BROWSER_DISPLAY_DATA][] = ['onAlterData'];
    return $events;
  }

}
