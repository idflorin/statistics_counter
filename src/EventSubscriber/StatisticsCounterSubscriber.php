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
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      return;
    }

    $node = $this->routeMatch->getParameter('node');
    $views_enabled = $this->configFactory
      ->get('statistics.settings')
      ->get('count_content_views');

    if (
      ($node instanceof NodeInterface) &&
      ($event->getResponse() instanceof HtmlResponse) &&
      $views_enabled
    ) {
      if (
        $this->moduleHandler->moduleExists('statistics_filter')
        && function_exists('statistics_filter_do_filter')
        && statistics_filter_do_filter()
      ) {
        return;
      }

      $this->database->merge('node_counter')
        ->key(['nid' => $node->id()])
        ->fields([
          'weekcount' => 1,
          'monthcount' => 1,
          'yearcount' => 1,
        ])
        ->expression('weekcount', 'weekcount + 1')
        ->expression('monthcount', 'monthcount + 1')
        ->expression('yearcount', 'yearcount + 1')
        ->execute();
    }
  }

}
