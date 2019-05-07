<?php

namespace Drupal\path_pattern_as_argument\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'path_pattern_as_argument.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the relevant config.
    $config = $this->config('path_pattern_as_argument.settings');
    $saved_views = $config->get('views');
    $num_saved_views = array();

    // Check if the FormState knows about existing setting items.
    // If not let it know.

    // count_views does not only save the number of existing setting items
    // but saves indexes so we cn delete single items afterwards
    if (!$form_state->get('count_views')){
      if ( is_array($saved_views)){
        for($s = 0; $s < count($saved_views); $s++ ) {
          $num_saved_views[] = $s;
        }
      } else {
        $num_saved_views[] = 1;
      }
      $form_state->set('count_views',$num_saved_views);

    } else {
      $num_saved_views = $form_state->get('count_views');

    }


    $form['#tree'] = TRUE;

    $form['views'] = [
      '#type'       => 'container',
      '#attributes' => ['id' => 'views'],
    ];

    // build the setting items
    foreach ($num_saved_views as $i){


      $form['views'][$i] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['container'],
        ],
       '#tree' => true,
      ];

      $form['views'][$i]['view'] = [
        '#type' => 'textfield',
        '#title' => $this->t('view'),
        '#description' => $this->t('The views machine name'),
        '#maxlength' => 255,
        '#size' => 20,
        '#default_value' => ( $i < count($saved_views) )? $saved_views[$i]['view']:'',
      ];
      $form['views'][$i]['display'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Display'),
        '#description' => $this->t('The display id the argument is for'),
        '#maxlength' => 255,
        '#size' => 20,
        '#default_value' => ( $i < count($saved_views) )? $saved_views[$i]['display']:'',
      ];
      $form['views'][$i]['position'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Argument position'),
        '#description' => $this->t('For which of the arguments should the pattern be used?<br>count them from the left starting with 0'),
        '#maxlength' => 2,
        '#size' => 3,
        '#default_value' => ( $i < count($saved_views) )? $saved_views[$i]['position']:'0',
      ];
      $form['views'][$i]['replace_patterns'] = [
        '#type' => 'textarea',
        '#title' => $this->t('replace patterns'),
        '#description' => $this->t('Enter patterns which will be replaced in the current path by something else.<br>
              The format is {Regexp pattern}|{replacement pattern}.For the replacement pattern you can use the 
               matches from the regexp like in phps <a href="https://www.php.net/manual/de/function.preg-replace.php">preg_replace</a>.<br>
               One pattern replacement pair per line.'),
        '#default_value' => ( $i < count($saved_views) )? $saved_views[$i]['replace_patterns']:'',
        '#placeholder' => "e.g. /\/node\/([0-9]+)/|/node/* \nor more general:/\/([0-9a-zA-Z-_]+)\/([0-9]+)/|/\${1}/*\nmore complex: /\/([0-9a-zA-Z-_]+)\/([0-9]+)\/([0-9a-zA-Z-_]+)/|/\${1}/*/\${3}",
      ];

      $token_tree = [
        '#theme' => 'token_tree_link',
        '#token_types' => 'all',
        '#global_types' => FALSE,
       // '#dialog' => TRUE,
        '#show_nested' => TRUE,
      ];
      $rendered_token_tree = \Drupal::service('renderer')->render($token_tree);
      if (\Drupal::moduleHandler()->moduleExists('token')) {
        $description= $this->t('Should something be appended on a pattern create by the replacement rules above?<br>
                Enter a pattern which is the result from above and the string which is to be appended separated by a |.<br>
                One pair per line. The string to be appended allows token.@browse_tokens_link', [
    '@browse_tokens_link' => $rendered_token_tree]);

      } else {
        $description = $this->t('Should something be appended on a pattern created by the replacement rules above?<br>
                Enter a pattern which is the result from the replace patterns above and the string which is to be appended separated by a |.<br>
                One pair per line.');

      }
      
      $form['views'][$i]['append_patterns'] = [
        '#type' => 'textarea',
        '#title' => $this->t('append pattern'),
        '#description' => $description,
        '#default_value' => ( $i < count($saved_views) )? $saved_views[$i]['append_patterns']:'',
        '#placeholder' => "e.g. /node/*|/[node:content-type:machine-name]",
      ];
      //$form['views'][$i]['append_patterns']['#element_validate'][] = 'token_element_validate';
      //$form['views'][$i]['append_patterns']+= array('#token_types' => array());
      //$form['views'][$i]['append_patterns']+= ['#token_types' => ['node','user','group']];

      $form['views'][$i]['remove-'.$i] = [
        '#type' => 'submit',
        '#value'=> 'remove',
        '#name' =>'remove'-$i,
        '#submit' => array('::removeCallback'),
        '#attributes'=> array('name' =>'remove-'.$i),
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'views',
        ],
      ];
    }

    $form['views']['add_view'] = array(
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => array('::AddOne'),
      '#ajax' => array(
        'callback' => '::addmoreCallback',
        'wrapper' => 'views',
      ),
      '#weight' => 100,
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();

    unset($values['views']['add_view']);

    $config = $this->configFactory->getEditable('path_pattern_as_argument.settings');
    $config->set('views',$values['views']);
    $config->save();
  }


 public function removeCallback(array &$form, FormStateInterface $form_state) {

    $delta_remove = $form_state->getTriggeringElement()['#parents'][1];
    $sources_array=array();
    $sources_array = $form_state->get('count_views');
    $k = array_search($delta_remove, $sources_array);
    //\Drupal::logger('gdpr')->notice(print_r($platforms_array,true));
    unset($sources_array[$k]);
    $form_state->set('count_views', $sources_array);
    $form_state->setRebuild();
  }

  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['views'];
  }


  function AddOne(array &$form, FormStateInterface $form_state) {
    $entries = array();
    $entries = $form_state->get('count_views');
    $entries[] = max($entries)  + 1;
    $form_state->set('count_views', $entries);
    $form_state->setRebuild();
  }

}
