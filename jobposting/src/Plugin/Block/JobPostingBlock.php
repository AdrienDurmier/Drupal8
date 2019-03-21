<?php
/**
 * JobPostingBlock
 * @author Adrien Durmier
 */

namespace Drupal\jobposting\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\jobposting\Form\JobPostingForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Search engine for jobs posting block.
 *
 * @Block(
 *   id = "jobposting_block",
 *   admin_label = @Translation("Job Posting block")
 * )
 */
class JobPostingBlock extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * Form builder service.
     *
     * @var \Drupal\Core\Form\FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->formBuilder = $form_builder;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('form_builder')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
        // Get JobPosting form
        $form = $this->formBuilder->getForm(JobPostingForm::class);

        // Get user search params
        $title = \Drupal::request()->request->get('title');
        $description = \Drupal::request()->request->get('description');
        $skills = \Drupal::request()->request->get('skills');
        $exprequirements = \Drupal::request()->request->get('exprequirements');
        $employmenttype = \Drupal::request()->request->get('employmenttype');

        // Get all jobposting node entities with user params
        $db = \Drupal\Core\Database\Database::getConnection();
        $query = $db->select('node_field_data', 'n');
        $query->fields('n', ['nid']);
        $query->join('node__body', 'b', 'b.entity_id = n.nid');
        $query->join('node__field_jobposting_skills', 'skills', 'skills.entity_id = n.nid');
        $query->join('node__field_jobposting_employmenttype', 'type', 'type.entity_id = n.nid');
        $query->join('node__field_jobposting_exprequirements', 'exp', 'exp.entity_id = n.nid');
        $query->condition('n.type', 'jobposting');
        // - Filter title
        if(isset($title)){
            $query->condition('n.title', "%" . $db->escapeLike($title) . "%", 'LIKE');
        }
        // - Filter description
        if(isset($description)){
            $query->condition('b.body_value', "%" . $db->escapeLike($description) . "%", 'LIKE');
        }
        // - Filter skills
        if(isset($skills)){
            $query->condition('skills.field_jobposting_skills_value', "%" . $db->escapeLike($skills) . "%", 'LIKE');
        }
        // - Filter exprequirements
        if(isset($exprequirements)){
            $query->condition('exp.field_jobposting_exprequirements_value', $exprequirements, '>=');
        }
        // - Filter employmenttype
        if(isset($employmenttype)){
            $group = $query->orConditionGroup();
            foreach($employmenttype as $type){
                $group->condition('type.field_jobposting_employmenttype_value', $type);
            }
            $query->condition($group);
        }
        $results = $query->execute()->fetchAll();

        // Get results with full node datas
        $items = array();
        foreach($results as $item){
            $node = \Drupal\node\Entity\Node::load($item->nid);
            $items[] = $node;
        }

        return array(
            '#theme' => 'jobposting_block',
            '#form' => $form,
            '#items' => $items,
            '#attached' => array(
                'library' => array(
                    'jobposting/jobposting_assets',
                ),
            ),
        );
    }

}
