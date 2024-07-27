<?php

namespace Drupal\disable_link_rel\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disable link rel event subscriber.
 */
class DisableLinkRelSubscriber implements EventSubscriberInterface {

  /**
   * The module config.
   */
  protected ImmutableConfig $config;

  /**
   * Constructors.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory
  ) {
    $this->config = $configFactory->get('disable_link_rel.import');
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Response event.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $response = $event->getResponse();

    if (!$response instanceof HtmlResponse) {
      return;
    }

    $response->addCacheableDependency($this->config);
    $enable = $this->config->get('enable');

    if (empty($enable)) {
      return;
    }

    $attachments = $response->getAttachments();

    if (empty($attachments['html_head_link'])) {
      return;
    }

    $uselessLinks = _disable_link_rel_parse_values($this->config->get('links', ''));

    foreach ($attachments['html_head_link'] as $delta => $attachment) {
      if (isset($attachment[0]['rel']) && in_array($attachment[0]['rel'], $uselessLinks)) {
        unset($attachments['html_head_link'][$delta]);
      }
    }

    $response->setAttachments($attachments);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse', 1000],
    ];
  }

}
