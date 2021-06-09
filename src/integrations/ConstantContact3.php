<?php

namespace krisdrivmailing\mailinglist\integrations;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Solspace\Freeform\Library\Integrations\MailingLists\DataObjects\ListObject;
use krisdrivmailing\mailinglist\exceptions\IntegrationException;
use krisdrivmailing\mailinglist\MailingList;

class ConstantContact3 
{

    const TITLE = 'Constant Contact';
    const LOG_CATEGORY = 'Constant Contact';
    const SETTING_REFRESH_TOKEN = 'refresh_token';

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function getSetting(string $key)
    {
        return $this->settings->$key ?? null;
    }

    public function setSetting(string $key, $value)
    {
        $this->settings->$key = $value;

        \Craft::$app->plugins->savePluginSettings(MailingList::$plugin, $this->settings->toArray());
    }

    public function getEndpoint(string $target): string 
    {
        return $this->getApiRootUrl() . $target;
    }

    /**
     * Check if it's possible to connect to the API.
     *
     * @throws IntegrationException
     */
    public function checkConnection(bool $refreshTokenIfExpired = true): bool
    {
        // Having no Access Token is very likely because this is
        // an attempted connection right after a first save. The response
        // will definitely be an error so skip the connection in this
        // first-time connect situation.
        if ($this->getSetting('accessToken')) {
            $client = $this->generateAuthorizedClient($refreshTokenIfExpired);
            // TODO
            $endpoint = $this->getEndpoint('/contact_lists');

            try {
                $response = $client->get($endpoint);
                $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

                return isset($json->lists);
            } catch (RequestException $exception) {
                $responseBody = (string) $exception->getResponse()->getBody();

                // We want to log errors when the error is caused
                // by something else than a stale access token
                if (!$refreshTokenIfExpired) {
                    // var_dump($responseBody, ['exception' => $exception->getMessage()]);
                    // die;
                    // ERROR: TODO
                }

                throw new IntegrationException(
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getPrevious()
                );
            }
        }

        return false;
    }

    /**
     * @throws IntegrationException
     */
    public function pushEmails(ListObject $mailingList, array $emails, array $mappedValues): bool
    {
        $client = $this->generateAuthorizedClient();

        try {
            $data = array_merge(
                [
                    'email_address' => $emails[0],
                    'create_source' => 'Contact',
                    'list_memberships' => [$mailingList->getId()],
                ],
                $mappedValues
            );

            $response = $client->post($this->getEndpoint('/contacts/sign_up_form'), ['json' => $data]);
        } catch (RequestException $e) {
            // $responseBody = (string) $e->getResponse()->getBody();
            // $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new IntegrationException('Could not connect to API endpoint');
        }

        $status = $response->getStatusCode();
        if (!\in_array($status, [200, 201])) { // 200 Contact successfully update, 201 Contact successfully created
            // $this->getLogger()->error('Could not add contacts to list', ['response' => (string) $response->getBody()]);

            throw new IntegrationException('Could not add emails to lists');
        }

        return 201 === $status;
    }

    /**
     * A method that initiates the authentication.
     */
    public function initiateAuthentication()
    {
        $apiKey = $this->getSetting('apiKey');
        $secret = $this->getSetting('secret');
        $redirect = $this->getSetting('redirectUri');

        if (!$apiKey || !$secret || !$redirect) {
            throw new IntegrationException('Invalid credentials, check your plugin settings and try again');
        }

        $payload = [
            'response_type' => 'code',
            'client_id' => $apiKey,
            'redirect_uri' => urlencode($redirect),
            'scope' => 'contact_data',
        ];

        header('Location: '.$this->getAuthorizeUrl().'?'.http_build_query($payload));

        exit();
    }


    /**
     * @throws IntegrationException
     */
    public function fetchAccessToken($code): string
    {
        $client = new Client();

        if (null === $code) {
            return '';
        }

        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->getSetting('apiKey'),
            'client_secret' => $this->getSetting('secret'),
            'redirect_uri' => $this->getSetting('redirectUri'),
            'code' => $code,
        ];

        try {
            $response = $client->post(
                $this->getAccessTokenUrl(),
                ['form_params' => $payload]
            );
        } catch (RequestException $e) {
            throw new IntegrationException((string) $e->getResponse()->getBody());
        }

        $json = json_decode((string) $response->getBody());

        if (!isset($json->access_token)) {
            throw new IntegrationException("No 'access_token' present in auth response for {serviceProvider}");
        }

        $this->setSetting('accessToken', $json->access_token);

        if (isset($json->refresh_token)) {
            $this->setSetting('refreshToken', $json->refresh_token);
        }

        return $this->getSetting('accessToken');
    }

    public function fetchContacts(...$lists): array
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contacts');

        try {
            $response = $client->get($endpoint, [
                'list_id' => rtrim(implode(', ', $lists))
            ]);
        } catch (RequestException $e) {
            throw new IntegrationException('Could not connect to API endpoint: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        if (200 !== $status) {

            throw new IntegrationException('Could not fetch ConstantContact lists');
        }

        $json = json_decode((string) $response->getBody(), false);

        return $json->contacts ?? [];
    }

    /**
     * Makes an API call that fetches mailing lists
     * Builds ListObject objects based on the results
     * And returns them.
     *
     * @throws IntegrationException
     *
     * @return array
     */
    public function fetchLists(): array
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contact_lists');

        try {
            $response = $client->get($endpoint);
        } catch (RequestException $e) {
            throw new IntegrationException('Could not connect to API endpoint: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        if (!in_array($status, [200, 201], true)) {

            throw new IntegrationException('Could not fetch ConstantContact lists. (status: ' . $status);
        }

        $json = json_decode((string) $response->getBody(), false);

        return $json->lists ?? [];
    }

    public function createOrUpdateContact(array $contact, ?string $listId): array
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contacts/sign_up_form');

        try {
            $listMemberships = ['list_memberships' => [$listId]];

            $requestBody = [ 
                RequestOptions::JSON => array_merge($contact, $listMemberships) 
            ];
            
            $response = $client->post($endpoint, $requestBody);
        } catch (RequestException $e) {
            throw new IntegrationException('Could not connect to API endpoint: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        if (!\in_array($status, [200, 201])) {
            throw new IntegrationException('Could not fetch ConstantContact lists');
        }

        $json = json_decode((string) $response->getBody(), true);

        return $json ?? [];
    }

    public function updateContact(string $contactId, array $data): array
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contacts/'.$contactId);

        $data['email_address'] = [
            'address' => $data['email_address']
        ];

        try {
            $response = $client->put($endpoint, [ 
                RequestOptions::JSON => $data
            ]);
        } catch (RequestException $e) {

            throw new IntegrationException('Could not connect to API endpoint: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        if (200 !== $status) {
            throw new IntegrationException('Could not fetch ConstantContact lists');
        }

        $json = json_decode((string) $response->getBody(), true);

        return $json ?? [];
    }

    /**
     * Returns the API root url without endpoints specified.
     */
    protected function getApiRootUrl(): string
    {
        return 'https://api.cc.email/v3';
    }

    /**
     * URL pointing to the OAuth2 authorization endpoint.
     */
    protected function getAuthorizeUrl(): string
    {
        return 'https://api.cc.email/v3/idfed';
    }

    /**
     * URL pointing to the OAuth2 access token endpoint.
     */
    protected function getAccessTokenUrl(): string
    {
        return 'https://idfed.constantcontact.com/as/token.oauth2';
    }

    /**
     * @throws IntegrationException
     */
    private function generateAuthorizedClient(bool $refreshTokenIfExpired = true): Client
    {
        $client = new Client(
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getSetting('accessToken'),
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        if ($refreshTokenIfExpired) {
            try {
                $this->checkConnection(false);
            } catch (IntegrationException $e) {
                if (401 === $e->getCode()) {
                    $client = new Client(
                        [
                            'headers' => [
                                'Authorization' => 'Bearer '.$this->getRefreshedAccessToken(),
                                'Content-Type' => 'application/json',
                            ],
                        ]
                    );
                }
            }
        }

        return $client;
    }

    /**
     * @throws IntegrationException
     */
    private function getRefreshedAccessToken(): string
    {
        if (!$this->getSetting('refreshToken') || !$this->getSetting('apiKey') || !$this->getSetting('secret')) {
            throw new IntegrationException('Trying to refresh Constant Contact access token with no credentials present');
        }

        $client = new Client();
        $payload = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getSetting('refreshToken'),
        ];

        try {
            $response = $client->post(
                $this->getAccessTokenUrl(),
                [
                    'auth' => [$this->getSetting('apiKey'), $this->getSetting('secret')],
                    'form_params' => $payload,
                ]
            );

            $json = json_decode((string) $response->getBody());
            if (!isset($json->access_token)) {
                throw new IntegrationException("No 'access_token' present in auth response for Constant Contact");
            }

            $this->setSetting('accessToken', $json->access_token);
            $this->setSetting('refreshToken', $json->refresh_token);

            return $this->getSetting('accessToken');
        } catch (RequestException $e) {
            // $responseBody = (string) $e->getResponse()->getBody();
            // $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new IntegrationException(
                $e->getMessage(),
                $e->getCode(),
                $e->getPrevious()
            );
        }
    }
    
}
