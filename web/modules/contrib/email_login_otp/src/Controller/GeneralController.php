<?php

namespace Drupal\email_login_otp\Controller;

use Drupal\user\Entity\User;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class GeneralController.
 */
class GeneralController extends ControllerBase {

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempstorePrivate;

  /**
   * Drupal\email_login_otp\Services\Otp definition.
   *
   * @var \Drupal\email_login_otp\Services\Otp
   */
  protected $otp;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal\Core\Path\CurrentPathStack definition.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->tempstorePrivate = $container->get('tempstore.private');
    $instance->otp              = $container->get('email_login_otp.otp');
    $instance->currentUser      = $container->get('current_user');
    $instance->currentPath      = $container->get('path.current');

    return $instance;
  }

  /**
   * Resend.
   *
   * @return string
   *   Return Hello string.
   */
  public function resend() {
    $otp = $this->otp;
    $uid = $this->tempstorePrivate->get('email_login_otp')->get('uid');
    $account = User::load($uid);
    $otp_code = $otp->generate($account->getDisplayname());
    if ($otp_code && $otp->send($otp_code)) {
      \Drupal::messenger()->addMessage(t('An OTP was sent to you via email. Please check your inbox.'));
      $redirect = new RedirectResponse('/login-otp');
      return $redirect->send();
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: resend')
    ];
  }

  public function otpSettingsAccess() {
    $path = $this->currentPath->getPath();
    $params = explode('/', $path);
    if ($this->currentUser->id() == $params[2]) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
