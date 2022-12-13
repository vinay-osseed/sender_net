<?php

namespace Drupal\sender_net;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Service description.
 */
class SenderNetApi {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a SenderNetApi object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(MessengerInterface $messenger, EntityTypeManagerInterface $entity_type_manager, ClientInterface $client, LoggerChannelFactoryInterface $logger, ConfigFactoryInterface $config_factory) {
    $this->messenger = $messenger;
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->client = $client;
    $this->logger = $logger;
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->getEditable('sender_net.settings');
  }

  /**
   * Create new subscriber.
   *
   * @see https://api.sender.net/subscribers/add-subscriber/
   */
  public function createSubscriber($param) {
    if (empty($param['email'])) {
      $this->messenger->addError('Please fill the email value.');
      return FALSE;
    }

    // Checking if the API settings are set.
    $header = $this->getApiHeader();
    $base_url = $this->config->get('api_base_url');
    if (empty($header) || empty($base_url)) {
      $this->messenger->addError('Sender.net API settings are not set.');
      return FALSE;
    }

    // API call to create a new subscriber.
    $subscriber_url = $base_url . 'subscribers';
    $data = [
      'headers' => $header,
      'json' => $param,
    ];
    try {
      $response = $this->client->request('POST', $subscriber_url, $data);
      if ($response->getStatusCode() === 200) {
        $this->logger->get('sender_net')->info("@email is subscribed to sender.net", ['@email' => $param['email']]);
        return TRUE;
      }
    }
    catch (\Throwable $th) {
      $this->messenger->addError($th->getMessage());
      $this->logger->get('sender_net')->error($th->getMessage());
    }
  }

  /**
   * Get header of API call.
   *
   * @see https://api.sender.net/authentication/
   */
  public function getApiHeader() {
    // Get the value of the config variable `api_access_tokens`.
    $token = $this->config->get('api_access_tokens');
    if (!empty($token)) {
      return [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ];
    }
    else {
      return NULL;
    }
  }

}
