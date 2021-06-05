<?php
/**
 * MailingList plugin for Craft CMS 3.x
 *
 * Synchronize Craft user contact data with ConstantConntact API integration
 *
 * @link      https://github.com/Kris-Driv
 * @copyright Copyright (c) 2021 Kristaps Drivnieks
 */

namespace krisdrivmailing\mailinglist\models;

use krisdrivmailing\mailinglist\MailingList;

use Craft;
use craft\base\Model;

/**
 * MailingList Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Kristaps Drivnieks
 * @package   MailingList
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $apiKey;

    public $secret;

    public $redirectUri;

    public $accessToken;

    public $refreshToken;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['apiKey', 'secret', 'redirectUri', 'accessToken', 'refreshToken'], 'string'
        ];
    }
}
