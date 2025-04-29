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

    $node = $this->routeMatch->getParameter('node');
    $nid = null;
    $is_node_view = FALSE;

    if ($node instanceof NodeInterface) {
      $nid = $node->id();
      $is_node_view = TRUE;
    } elseif (is_object($node) && method_exists($node, 'getEntityId')) {
      $nid = $node->getEntityId();
      if ($node->getEntityTypeId() === 'node') {
        $is_node_view = TRUE;
      }
    } elseif (is_array($node) && isset($node['nid'])) {
      $nid = $node['nid'];
      $is_node_view = TRUE;
    } elseif (is_numeric($node) || is_string($node)) {
      $nid = $node;
      $is_node_view = TRUE;
    }

    //\Drupal::logger('statistics_counter')->debug('Final Node ID (Attempt 5): @nid (Type: @type), Is Node View: @is_node_view, Is Main Request: @is_main', ['@nid' => print_r($nid, TRUE), '@type' => gettype($nid), '@is_node_view' => $is_node_view, '@is_main' => $event->isMainRequest() ? 'TRUE' : 'FALSE']);

    $views_enabled = $this->configFactory
      ->get('statistics.settings')
      ->get('count_content_views');

    if (
      $is_node_view &&
      ($event->getResponse() instanceof HtmlResponse) &&
      $views_enabled &&
      is_scalar($nid)
    ) {
      //\Drupal::logger('statistics_counter')->debug('Attempting database update for node ID: @nid (Main Request)', ['@nid' => $nid]);
      try {
        $this->database->insert('node_counter')
          ->fields([
            'nid' => $nid,
            'weekcount' => 1,
            'monthcount' => 1,
            'yearcount' => 1,
            'timestamp' => time(),
          ])
          ->execute();
      } catch (\Exception $e) {
        $this->database->update('node_counter')
          ->fields([
            'timestamp' => time(),
          ])
          ->expression('weekcount', 'weekcount + 1')
          ->expression('monthcount', 'monthcount + 1')
          ->expression('yearcount', 'yearcount + 1')
          ->condition('nid', $nid)
          ->execute();
      }
    } else {
      //\Drupal::logger('statistics_counter')->debug('Skipping statistics update (Main Request Check). Is Node View: @is_node_view, Views Enabled: @views_enabled, Node ID Scalar: @is_scalar_nid, Is HTML Response: @is_html_response, Is Main Request: @is_main, Node Parameter: @node', [        '@is_node_view' => $is_node_view,        '@views_enabled' => $views_enabled,        '@is_scalar_nid' => is_scalar($nid),        '@is_html_response' => ($event->getResponse() instanceof HtmlResponse) ? 'TRUE' : 'FALSE',        '@is_main' => $event->isMainRequest() ? 'TRUE' : 'FALSE',        '@node' => print_r($node, TRUE),      ]);
    }
  }
}