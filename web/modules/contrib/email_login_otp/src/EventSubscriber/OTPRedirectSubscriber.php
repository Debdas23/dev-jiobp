<?php

namespace Drupal\email_login_otp\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Class OtpRedirectSubscriber.
 */
class OtpRedirectSubscriber implements EventSubscriberInterface {
  protected $currentUser;
  protected $currentPath;
  protected $tempStore;

  /**
   * Constructs a new OtpRedirectSubscriber object.
   */
  public function __construct(AccountInterface $current_user, CurrentPathStack $current_path, PrivateTempStoreFactory $tempStore) {
    $this->currentUser = $current_user;
    $this->currentPath = $current_path;
    $this->tempStore   = $tempStore;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['loginRedirect'];
    return $events;
  }

  /**
   * This method is called when the login_redirect is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function loginRedirect(GetResponseEvent $event) {
    $uid = $this->tempStore->get('email_login_otp')->get('uid');
    if (($this->currentPath->getPath() == '/login-otp' && $this->currentUser->isAuthenticated()) ||
    ($this->currentPath->getPath() == '/login-otp' && $uid == null) ||
    ($this->currentPath->getPath() == '/login-otp/resend' && $uid == null)) {
      $redirect = new RedirectResponse('/user');
      return $redirect->send();
    }
  }

}
