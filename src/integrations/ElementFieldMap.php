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

        if(isset($user->mobilePhone)) {
            $map['phone_numbers'] = [
                ['phone_number' => $user->mobilePhone, 'kind' => 'mobile']
            ];
        }

        $map['custom_fields'] = [
            [
                'custom_field_id' => '874fc484-bfa1-11eb-8e91-fa163eed61c9',
                'value' => $user->dob->format('Y-m-d')
            ],
            [
                'custom_field_id' => '87518fe4-bfa1-11eb-8e91-fa163eed61c9',
                'value' => $user->gender->value
            ],
            [
                'custom_field_id' => '87530fcc-bfa1-11eb-8e91-fa163eed61c9',
                'value' => $user->nationality->value
            ],
            [
                'custom_field_id' => '87546fca-bfa1-11eb-8e91-fa163eed61c',
                'value' => $user->ethnicBackground->value
            ]
        ];

        // $map['custom_fields'] = [
        //     [
        //         'custom_field_id' => 'c5ee0598-c906-11eb-abe1-fa163e5bc304',
        //         'value' => $user->dob->format('Y-m-d')
        //     ],
        //     [
        //         'custom_field_id' => 'fa701fd8-c6f2-11eb-8fef-fa163ecbdd18',
        //         'value' => $user->gender->value
        //     ],
        //     [
        //         'custom_field_id' => 'f576fbaa-c6f2-11eb-bd78-fa163e24df6a',
        //         'value' => $user->nationality->value
        //     ],
        //     [
        //         'custom_field_id' => 'c5ef6884-c906-11eb-abe1-fa163e5bc304',
        //         'value' => $user->ethnicBackground->value
        //     ]
        // ];

        // Will be set later
        // $map['list_memberships'] = [];

        $map['street_addresses'] = [
            [
                "kind" => "home",
                "street" => $user->addressLine1,
                "city" => $user->townOrCity,
                "state" => $user->county,
                "postal_code" => $user->postcode,
                "country" => "United Kingdom"
            ]
        ];

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