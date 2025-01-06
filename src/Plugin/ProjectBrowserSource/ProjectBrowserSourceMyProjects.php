<?php

namespace Drupal\myprojects\Plugin\ProjectBrowserSource;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\project_browser\Plugin\ProjectBrowserSourceBase;
use Drupal\project_browser\ProjectBrowser\Project;
use Drupal\project_browser\ProjectBrowser\ProjectsResultsPage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Project Browser Source Plugin for my projects.
 *
 * @ProjectBrowserSource(
 *   id = "myprojects",
 *   label = @Translation("My projects"),
 *   description = @Translation("Source plugin for Project Browser."),
 *   local_task = {
 *     "title" = @Translation("My projects"),
 *   },
 * )
 */
class ProjectBrowserSourceMyProjects extends ProjectBrowserSourceBase {

  /**
   * Constructor for plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request from the browser.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly RequestStack $requestStack,
    protected ModuleExtensionList $moduleExtensionList,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get(RequestStack::class),
      $container->get(ModuleExtensionList::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getProjects(array $query = []): ProjectsResultsPage {
    $request = $this->requestStack->getCurrentRequest();
    $projects_from_source = [
      [
        'identifier' => 'potx',
        'machine_name' => 'potx',
        'label' => 'Translation template extractor',
        'short_description' => 'Quick summary for potx.',
        'long_description' => 'Extended project information for potx.',
        'author' => 'Gábor Hojtsy',
        'logo' => $request->getSchemeAndHttpHost() . '/core/misc/logo/drupal-logo.svg',
        'created_at' => strtotime('10 years ago'),
        'updated_at' => strtotime('1 year ago'),
        'categories' => ['good:Good', 'great:Great'],
        'composer_namespace' => 'drupal/potx',
        'usage' => 500,
      ],
      [
        'identifier' => 'upgrade_status',
        'machine_name' => 'upgrade_status',
        'label' => 'Upgrade status',
        'short_description' => 'Quick summary for upgrade_status.',
        'long_description' => 'Extended project information for upgrade_status.',
        'author' => 'Gábor Hojtsy',
        'logo' => $request->getSchemeAndHttpHost() . '/core/misc/logo/drupal-logo.svg',
        'created_at' => strtotime('12 years ago'),
        'updated_at' => strtotime('1 month ago'),
        'categories' => ['great:Great'],
        'composer_namespace' => 'drupal/upgrade_status',
        'usage' => 50000,
      ],
      [
        'identifier' => 'deprecation_status',
        'machine_name' => 'deprecation_status',
        'label' => 'Deprecation status',
        'short_description' => 'Quick summary for deprecation_status.',
        'long_description' => 'Extended project information for deprecation_status.',
        'author' => 'Gábor Hojtsy',
        'logo' => $request->getSchemeAndHttpHost() . '/core/misc/logo/drupal-logo.svg',
        'created_at' => strtotime('6 years ago'),
        'updated_at' => strtotime('1 month ago'),
        'categories' => ['good:Good'],
        'composer_namespace' => 'drupal/upgrade_status',
        'usage' => 2,
      ],
    ];

    $projects = [];
    foreach ($projects_from_source as $project_from_source) {
      // Remove items that don't match the query. Could do above if on a 3rd
      // party API, but we can post-filter here.

      if (!empty($query['search']) && stripos($project_from_source['machine_name'], $query['search']) === FALSE && stripos($project_from_source['label'], $query['search']) === FALSE) {
        // Skip this project if it did not match the search query in label or machine name.
        continue;
      }

      $author = [
        'name' => $project_from_source['author'],
      ];
      $logo = [
        'file' => [
          // Url of the logo in "https" format.
          'uri' => $project_from_source['logo'],
          'resource' => 'image',
        ],
        'alt' => 'Project logo',
      ];
      $categories = [];
      foreach ($project_from_source['categories'] as $category) {
        [$id, $name] = explode(':', $category);
        $categories[] = [
          'id' => $id,
          'name' => $name,
        ];
      }
      $projects[] = new Project(
        logo: $logo,
        // Need to populate the values of all the properties.
        isCompatible: TRUE,
        isMaintained: TRUE,
        isCovered: TRUE,
        projectUsageTotal: $project_from_source['usage'],
        machineName: $project_from_source['machine_name'],
        body: [
          'summary' => $project_from_source['short_description'],
          'value' => $project_from_source['long_description'],
        ],
        title: $project_from_source['label'],
        author: $author,
        packageName: $project_from_source['composer_namespace'],
        categories: $categories,
        // Images: Array of images using the same structure as $logo, above.
        images: [],
      );
    }

    // Sort as needed.
    if (!empty($query['sort'])) {
      $sort = $query['sort'];
      switch ($sort) {
        case 'a_z':
          usort($projects, fn($x, $y) => $x->title <=> $y->title);
          break;

        case 'z_a':
          usort($projects, fn($x, $y) => $y->title <=> $x->title);
          break;

        case 'usage_total':
          usort($projects, fn($x, $y) => $y->projectUsageTotal <=> $x->projectUsageTotal);
          break;
      }
    }

    // Return one page of results. The first parameter is the total number of
    // results for the set, as filtered by $query.
    return $this->createResultsPage($projects);
  }

  /**
   * {@inheritdoc}
   */
  public function getCategories(): array {
    return [
      [
        'id' => 'good',
        'name' => 'Good',
      ],
      [
        'id' => 'great',
        'name' => 'Great',
      ],
    ];
  }

}
