<?php

namespace Drupal\email_login_otp\Services;
use Drupal\Core\Database\Connection;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

class Otp {
  protected $database;
  protected $mailManager;
  protected $languageManager;

  private $username;
  private $tempStorageFactory;

  public function __construct(Connection $connection, MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager, PasswordInterface $hasher, PrivateTempStoreFactory $tempStoreFactory) {
    $this->tempStorageFactory = $tempStoreFactory;
    $this->database           = $connection;
    $this->mailManager        = $mail_manager;
    $this->languageManager    = $language_manager;
    $this->hasher             = $hasher;
  }

  public function generate($username) {
    $this->username = $username;
    $uid = $this->getUserField('uid');
    $this->tempStorageFactory->get('email_login_otp')->set('uid', $uid);

    if ($this->exists($uid)) {
      return $this->update($uid);
    }
    return $this->new($uid);
  }

  public function send($otp, $to = null) {
    $to                = $to ? $to : $this->getField('email');
    $langcode          = $this->languageManager->getCurrentLanguage()->getId();
    $mail_template     = file_get_contents(drupal_get_path('module', 'email_login_otp').'/templates/email_login_otp_mail.html.twig');
    $mail_message      = str_replace('{{username}}', $this->username, $mail_template);
    $mail_message      = str_replace('{{otp}}', $otp, $mail_message);
    $params['message'] = $mail_message;
    return $this->mailManager->mail('email_login_otp', 'email_login_otp_mail', $to, $langcode, $params, NULL, TRUE);
  }

  public function check($uid, $otp) {
    if ($this->exists($uid)) {
      $select = $this->database->select('email_login_otp', 'u')
                ->fields('u', ['otp', 'expiration'])
                ->condition('uid', $uid, '=')
                ->execute()
                ->fetchAssoc();
      if ($select['expiration'] >= time() && $this->hasher->check($otp, $select['otp'])) {
        return true;
      }
      return false;
    }
    return false;
  }

  public function expire($uid) {
    $delete = $this->database->delete('email_login_otp')
              ->condition('uid', $uid)
              ->execute();
    return $delete;
  }

  public function is_enabled($uid) {
    $exists = $this->database->select('otp_settings', 'o')
              ->fields('o')
              ->condition('uid', $uid, '=')
              ->execute()
              ->fetchAssoc();
    return $exists ? $exists['enabled'] : false;
  }

  public function storeSettings(array $settings) {
    $exists = $this->database->select('otp_settings', 'o')
              ->fields('o')
              ->condition('uid', $settings['uid'], '=')
              ->execute();
    if($exists->fetchAssoc()) {
      $update = $this->database->update('otp_settings')
                ->fields([
                  'email' => $settings['email'],
                  'enabled' => $settings['enabled'],
                ])
                ->condition('uid', $settings['uid'], '=')
                ->execute();
      return $update ?? TRUE;
    }
    $store_settings = $this->database->insert('otp_settings')
                      ->fields($settings)
                      ->execute();

    return $store_settings ?? TRUE;
  }

  public function getExpirationTime($uid) {
    $unixTime = $this->database->select('email_login_otp', 'o')
              ->fields('o', ['expiration'])
              ->condition('uid', $uid, '=')
              ->condition('otp', '', '!=')
              ->execute()
              ->fetchAssoc();
    if ($unixTime) {
      return $unixTime['expiration'];
    }
    return FALSE;
  }

  private function getUserField($field) {
    $query = $this->database->select('users_field_data', 'u')
              ->fields('u', [$field])
              ->condition('name', $this->username, '=')
              ->execute()
              ->fetchAssoc();
    return $query[$field];
  }

  private function getField($field) {
    $uid = $this->tempStorageFactory->get('email_login_otp')->get('uid');
    $query = $this->database->select('otp_settings', 'u')
              ->fields('u', [$field])
              ->condition('uid', $uid, '=')
              ->execute()
              ->fetchAssoc();
    return $query[$field];
  }

  private function exists($uid) {
    $exists = $this->database->select('email_login_otp', 'u')
              ->fields('u')
              ->condition('uid', $uid, '=')
              ->execute()
              ->fetchAssoc();
    return $exists ?? true;
  }

  private function new($uid) {
    $human_readable_otp = rand(100000, 999999);
    $insert_otp_info = $this->database->insert('email_login_otp')->fields([
      'uid' => $uid,
      'otp' => $this->hasher->hash($human_readable_otp),
      'expiration' => strtotime("+5 minutes",time())
    ])->execute();
    return $human_readable_otp;
  }

  private function update($uid) {
    $human_readable_otp = rand(100000, 999999);
    $update_otp_info = $this->database->update('email_login_otp')
              ->fields([
                'otp' => $this->hasher->hash($human_readable_otp),
                'expiration' => strtotime("+5 minutes",time())
              ])
              ->condition('uid', $uid, '=')
              ->execute();
    return $human_readable_otp;
  }

}
