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
        $map = array_filter([
            'email_address' => $user->email,
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
        ]);

        // if(isset($user->mobile_number)) {
        //     $map['phone_numbers'] = [
        //         ['phone_number' => $user->phone_number, 'kind' => 'mobile']
        //     ];
        // }

        // $map['custom_fields'] = [
        //     [
        //         'custom_field_id' => '874fc484-bfa1-11eb-8e91-fa163eed61c9',
        //         'value' => $user->dob
        //     ],
        //     [
        //         'custom_field_id' => '87518fe4-bfa1-11eb-8e91-fa163eed61c9',
        //         'value' => $user->gender
        //     ],
        //     [
        //         'custom_field_id' => '87530fcc-bfa1-11eb-8e91-fa163eed61c9',
        //         'value' => $user->nationality
        //     ],
        //     [
        //         'custom_field_id' => '87546fca-bfa1-11eb-8e91-fa163eed61c',
        //         'value' => $user->ethnic_background
        //     ]
        // ];

        // Will be set later
        // $map['list_memberships'] = [];

        // $map['street_addresses'] = [
        //     [
        //         "kind" => "home",
        //         "street" => null,
        //         "city" => null,
        //         "state" => null,
        //         "postal_code" => null,
        //         "country" => "United Kingdom"
        //     ]
        // ];

        return $map;
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