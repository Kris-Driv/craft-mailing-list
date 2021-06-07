<?php

namespace krisdrivmailing\mailinglist\integrations;

use craft\elements\User;

class ElementFieldMap 
{

    /**
     * @var array
     */
    public $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function mapUserFields(User $user): array
    {
        return array_filter([
            'email_address' => $user->email,
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
        ]);
    }

    // First Name - first_name
    // Last Name - last_name
    // Email Address - email_address
    // Mobile Phone - phone_numbers: [{ phone_number: xxx, kind: 'mobile' }]
    // street_addresses.kind: home
    // Address Line 1 + Address Line 2 - street_addresses.street
    // Town or City - street_addresses.city
    // County - street_addresses.state
    // Postcode - street_addresses.postal_code
    // Country - hardcode to United Kingdom
    // DOB - custom_fields id: 874fc484-bfa1-11eb-8e91-fa163eed61c9
    // Gender - custom_fields id: 87518fe4-bfa1-11eb-8e91-fa163eed61c9
    // Nationality - custom_fields id: 87530fcc-bfa1-11eb-8e91-fa163eed61c9
    // Ethnic Background - custom_fields id: 87546fca-bfa1-11eb-8e91-fa163eed61c

    // Under TODO

}