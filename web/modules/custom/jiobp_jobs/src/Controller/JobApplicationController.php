<?php 

namespace Drupal\jiobp_jobs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Block\BlockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for the Job Application page.
 */
class JobApplicationController extends ControllerBase {

  /**
   * The block manager service.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs a JobApplicationController object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager service.
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Returns the page content for the Job Application page.
   *
   * @return array
   *   The page render array.
   */
  public function content() {
    // Unpublish jobs older than 15 days.
    $this->unpublishOldJobPosts();

    // Render jobs, locations, experiences, and communities data for the template.
    $locations = $this->getTermNames('location');
    $experiences = $this->getTermNames('experience');
    $communities = $this->getTermNames('function_area');
   
    $jobs = $this->getAllJobs();

    return [
      '#theme' => 'job_template', // The custom theme for rendering the page.
      '#jobs' => $jobs, // Pass the jobs data to the template.
      '#locations' => $locations,
      '#experiences' => $experiences,
      '#communities' => $communities,
      '#attached' => [
        'library' => [
          'jiobp_jobs/job_block_styles',
        ],
      ],
    ];
  }

  /**
   * Load all filter terms from taxonomy.
   *
   * @param string $field_info
   *   The field machine name for taxonomy terms.
   *
   * @return array
   *   An array of taxonomy term names.
   */
  public function getTermNames($field_info) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($field_info);
    $data = [];
    foreach ($terms as $term) {
      $data[] = $term->name;
    }
    return $data;
  }

  /**
   * Load all job application nodes.
   *
   * @return array
   *   An array of jobs data.
   */
 public function getAllJobs() {
  // Query to load job application nodes.
  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'job_applicant');
  $query->condition('status', 1); // Only published nodes.
  $query->accessCheck(TRUE); // Enforce permissions.
  $query->sort('created', 'DESC'); // Sort by creation date (newest first).
  
  $node_ids = $query->execute();

  // Log the node IDs for debugging.
  \Drupal::logger('jiobp_jobs')->debug('Fetched Node IDs: <pre>@ids</pre>', ['@ids' => print_r($node_ids, TRUE)]);

  $nodes = Node::loadMultiple($node_ids);

  // Date formatter service for formatting node created time.
  $date_formatter = \Drupal::service('date.formatter');
  $jobs = [];

  foreach ($nodes as $node) {
    if (!$node->get('field_display_name')->isEmpty() && !$node->get('field_job_id')->isEmpty()) {
      $job_name = $node->get('field_display_name')->value;
      $job_id = $node->get('field_job_id')->value;
      $experience = $this->getTaxonomyData($node->get('field_experience'));
      $community = $this->getTaxonomyData($node->get('field_functional_area'));
      $location = $this->getTaxonomyData($node->get('field_location'));

      // Format posted date.
      $posted_on = $date_formatter->format($node->getCreatedTime(), 'custom', 'Y-m-d H:i:s');

      // Fetch the job URL alias (without node ID in the URL).
      $node_path = '/node/' . $node->id();
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath($node_path);

      // Log alias for debugging
      \Drupal::logger('jiobp_jobs')->debug('Alias for Node @nid: @alias', ['@nid' => $node->id(), '@alias' => $alias]);

      // If alias not found, fallback to a custom URL structure (or another route).
      if (empty($alias)) {
        $alias = '/job-details/' . $node->id(); // Custom URL structure without node ID
      }

      // Add job data to the array, including the custom URL.
      $jobs[] = [
        'nid' => $node->id(),
        'id' => $job_id,
        'jobName' => $job_name,
        'location' => $location,
        'experience' => $experience,
        'community' => $community,
        'postedOn' => $posted_on,
        'contract' => 'Permanent', // Static value, modify if needed.
        'url' => $alias, // Pass the custom URL here
      ];
    }
    else {
      // Log error for nodes missing required fields.
      \Drupal::logger('jiobp_jobs')->error('Node @nid is missing required fields.', ['@nid' => $node->id()]);
    }
  }

  return $jobs;
}

  

  /**
   * Helper function to get taxonomy data.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field_info
   *   The field reference to taxonomy terms.
   *
   * @return string
   *   Comma-separated taxonomy term names.
   */
  public function getTaxonomyData($field_info) {
    $referenced_entities = $field_info->referencedEntities();
    $names = [];

    foreach ($referenced_entities as $entity) {
      if ($entity instanceof \Drupal\taxonomy\Entity\Term) {
        $names[] = $entity->getName();
      }
    }

    return implode(', ', $names);
  }

  /**
   * Unpublish job posts older than 15 days.
   */
  public  function unpublishOldJobPosts() {
    // Get the current date.
    $today = strtotime(date('Y-m-d'));

    // Query for published 'job_applicant' nodes where 'Validate Till Date' has passed.
    $query = \Drupal::entityQuery('node')
        ->condition('type', 'job_applicant')
        ->condition('status', 1)
        ->condition('field_posted_on', date('Y-m-d', $today), '<');

    $nids = $query->execute();

    if (!empty($nids)) {
        $nodes = Node::loadMultiple($nids);
        foreach ($nodes as $node) {
            $node->setUnpublished();
            $node->save(); 
        }
    }
  }
}