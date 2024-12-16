<?php

namespace Drupal\jiobp_jobs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a 'CareerPage' block.
 *
 * @Block(
 *   id = "blk_careerpage",
 *   admin_label = @Translation("Career Page Block"),
 *   category = @Translation("Custom")
 * )
 */
class JobApplicationBlock extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    $locations = $this->getTemNames('location');
    $experiences = $this->getTemNames('experiance');
    $communities = $this->getTemNames('functional_area');
   
    $jobs = $this->getAllJobs();

    return [
        '#theme' => 'job_block_template',
        '#jobs' => $jobs,
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
   * Load all Filter from taxonomy.
   */
  public function getTemNames($field_info) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($field_info);
    $data = [];
    foreach ($terms as $term) {
      $data[] = $term->name;
    }
    return $data;
  }
  /**
   * Load all node data form job aplication contetnt type.
   */
  public function getAllJobs() {
    // Load all nodes of content type 'job_application'
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'job_application');
    $node_ids = $query->execute();

    // Load the nodes based on the ids
    $nodes = Node::loadMultiple($node_ids);

    // Initialize an array to store the formatted job data
    $jobs = [];

    foreach ($nodes as $node) {
      // Get the necessary fields
      $job_name = $node->getTitle();
      $job_id = $node->get('field_job_id')->value; 
      $experience = $this->getTaxonomyData($node->get('field_experiance'));
      $community = $this->getTaxonomyData($node->get('field_functional_area')); 
      $location = $this->getTaxonomyData($node->get('field_location'));

      // Add the job data to the jobs array
      $jobs[] = [
        'id' => $node->id(),
        'jobName' => $job_name,
        'location' => $location,
        'experience' => $experience,
        'community' => $community,
        'contract' => 'Permanent', 
      ];
    }

    return $jobs;
  } 

  /**
   * Helper function to get taxonomyData.
   */
  public function getTaxonomyData($field_info) {
    // Assuming functional area is an entity reference (e.g., a taxonomy term or user)
    $functional_area = $field_info->referencedEntities();
    $data = [];

    foreach ($functional_area as $area) {
      if ($area instanceof \Drupal\taxonomy\Entity\Term) {
        $data[] = $area->getName();
      }
    }

    return implode(', ', $data); 
  }

}
