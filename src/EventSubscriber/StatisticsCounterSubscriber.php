<?php

namespace Drupal\statistics_counter\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Subscribes to Kernel TERMINATE events to recalculate node statistics.
 */
class StatisticsCounterSubscriber implements EventSubscriberInterface {

  protected Connection $database;
  protected RequestStack $requestStack;
  protected ModuleHandlerInterface $moduleHandler;
  protected ConfigFactoryInterface $configFactory;
  protected RouteMatchInterface $routeMatch;

  public function __construct(
    Connection $database,
    RequestStack $requestStack,
    ModuleHandlerInterface $moduleHandler,
    ConfigFactoryInterface $configFactory,
    RouteMatchInterface $routeMatch
  ) {
    $this->database = $database;
    $this->requestStack = $requestStack;
    $this->moduleHandler = $moduleHandler;
    $this->configFactory = $configFactory;
    $this->routeMatch = $routeMatch;
  }

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::TERMINATE => ['updateStatistics'],
    ];
  }

  public function updateStatistics(TerminateEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return;
    }

    // Skip bot traffic
    $user_agent = $request->headers->get('User-Agent');
    if (preg_match('/bot|crawl|slurp|spider/i', $user_agent)) {
      return;
    }

    // Confirm we're viewing a canonical node route
    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }

    // Get the node from the route
    $node = $this->routeMatch->getParameter('node');
    if (!$node instanceof NodeInterface) {
      return;
    }

    $nid = $node->id();
    if (!is_scalar($nid)) {
      return;
    }

    // Optional: skip admin users
    //$account = \Drupal::currentUser();
    //if ($account->hasPermission('administer nodes')) {
    //  return;
    //}

    // Check config setting (mimics Drupal core's behavior toggle)
    $views_enabled = $this->configFactory
      ->get('statistics.settings')
      ->get('count_content_views');

    if (!$views_enabled || !($event->getResponse() instanceof HtmlResponse)) {
      return;
    }

    // Prevent double-counting in the same request
    static $counted = FALSE;
    if ($counted) {
      return;
    }

    // Update counters
    try {
      $this->database->insert('node_counter')
        ->fields([
          'nid' => $nid,
          'weekcount' => 1,
          'monthcount' => 1,
          'yearcount' => 1,
          'timestamp' => \Drupal::time()->getCurrentTime(),
        ])
        ->execute();
    } catch (\Exception $e) {
      $this->database->update('node_counter')
        ->fields([
          'timestamp' => \Drupal::time()->getCurrentTime(),
        ])
        ->expression('weekcount', 'weekcount + 1')
        ->expression('monthcount', 'monthcount + 1')
        ->expression('yearcount', 'yearcount + 1')
        ->condition('nid', $nid)
        ->execute();
    }

    $counted = TRUE;
  }

}
