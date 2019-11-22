<?php

namespace Drupal\request_support\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "helpdesk_block",
 *   admin_label = @Translation("Helpdesk"),
 *   category = @Translation("Custom")
 * )
 */
class HelpDeskBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build(){
		// Gets current page path
		$current_path = \Drupal::service('path.current')->getPath();
		$current_path = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
		$current_path_link = \Drupal::request()->getSchemeAndHttpHost() . $current_path;

		$link_options = [
			'query' => ['origin' => $current_path_link, 'destination' => $current_path],
			'attributes' => ['class' => ['btn', 'btn-helpdesk', 'btn-add']],
		];

		$link = Link::fromTextAndUrl(t('Helpdesk'), Url::fromUri('internal:/form/helpdesk', $link_options))->toString();
		
		return array(
			'#type' => 'markup',
			'#markup' => '<p id="helpdesk-wrap">' . $link . '</p>',
		);		
	}
	
  /**
   * {@inheritdoc}
   */	
	public function getCacheMaxAge() {
		return 0;
	}	
}