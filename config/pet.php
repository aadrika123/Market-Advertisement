<?php

/**
 * | Config Constants for master parameter for Pet Module
 * | Created by: Sam Kerketta
 * | Created on: 14-06-2023
 */

return [
    "MASTER_DATA" => [
        "OWNER_TYPE_MST" => [
            "Owner"     => 1,
            "Tenant"    => 2
        ],
        "PET_GENDER" => [
            "Male"      => 1,
            "Female"    => 2
        ],
        "REGISTRATION_THROUGH" =>
        [
            "Holding" => 1,
            "Saf"     => 2
        ],
    ],
    "API_END_POINTS" => [
        "get_prop_detils" => 229,
    ],
    "HTTP_HEADERS" => [
        "JSON" => "application/json",
    ],
    "PROP_TYPE" => [
        "VACANT_LAND" => 4
    ],
    "PROP_OCCUPANCY_TYPE" => [
        1 =>  "SELF_OCCUPIED",
        2 =>  "TENANTED",
    ],
];
