<?php

namespace Drupal\registration_link\Access;

use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Access check for user registration routes.
 *
 * @package Drupal\registration_link\Access
 */
class RegistrationLinkAccessCheck implements AccessInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * RegistrationLinkAccessCheck constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The Config Factory service.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory->get('user.settings');
  }

  /**
   * Checks access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return $this|\Drupal\Core\Access\AccessResultAllowed
   *   The access result.
   */
  public function access(AccountInterface $account) {
    if (in_array('administrator', $account->getRoles())) {
      return AccessResult::allowed();
    }

    return AccessResult::allowedIf(
      $account->isAnonymous()
      && $this->configFactory->get('register') != UserInterface::REGISTER_ADMINISTRATORS_ONLY)
      ->addCacheableDependency($this->configFactory);
  }

}
