<?php

/**
 * | Relative path and name of Document Uploads
 */
return [
    // Live URL
    // "AUTH_URL" => 'http://192.168.0.95:8005/',        
    // "PAYMENT_URL" => 'http://192.168.0.95:8004/',
    // "BASE_URL" => 'http://192.168.0.95:8005/',
    // "95_PAYMENT_URL" => 'http://192.168.0.95:8004/',  
    // "ULB_LOGO_URL" => 'http://203.129.217.246:8000/',                       // Property URL
    "AUTH_URL" => env('AUTH_URL', 'http://localhost:8005/'),
    "PAYMENT_URL" => env('PAYMENT_URL', 'http://localhost:8000/'),
    "BASE_URL" => env('BASE_URL', 'http://localhost/'),
    "95_PAYMENT_URL" => env('95_PAYMENT_URL', 'http://localhost:8000/'),
    //"ULB_LOGO_URL" => env('ULB_LOGO_URL','http://localhost/'),                       // Property URL
    "DOC_URL" => env('DOC_URL', 'http://localhost/'),
    "ULB_LOGO_URL"    => env('ULB_LOGO_URL'),                    // Property URL

    "WHATSAPP_TOKEN"        => env("WHATSAPP_TOKEN", "xxx"),
    "WHATSAPP_NUMBER_ID"    => env("WHATSAPP_NUMBER_ID", "xxx"),
    "WHATSAPP_URL"          => env("WHATSAPP_URL", "xxx"),

    // Local URL
    // "AUTH_URL" => 'http://192.168.0.21:8005/',    
    // "BASE_URL" => 'http://192.168.0.21:8005',         
    // "PAYMENT_URL" => 'http://192.168.0.202:8006/',  

    "AADHAR_RELATIVE_NAME" => "AADHAR",
    "TRADE_RELATIVE_NAME" => "TRADE",
    "HOLDING_RELATIVE_NAME" => "HOLDING",
    "GPS_RELATIVE_NAME" => "GPS",
    "GST_RELATIVE_NAME" => "GST",
    "VEHICLE_RELATIVE_NAME" => "VEHICLE",
    "OWNER_BOOK_RELATIVE_NAME" => "OWNER-BOOK",
    "DRIVING_LICENSE_RELATIVE_NAME" => "DRIVING-LICENSE",
    "INSURANCE_RELATIVE_NAME" => "INSURANCE",
    "BRAND_DISPLAY_RELATIVE_NAME" => "BRAND-DISPLAY",
    "SELF_ADVET_RELATIVE_PATH" => "Uploads/SelfAdvets",
    "TOLL_PATH" => "Uploads/tolls",
    "SHOP_PATH" => "Uploads/shops",

    "SELF_ADVET" => [
        "RELATIVE_PATH" => "Uploads/SelfAdvets"
    ],

    "VEHICLE_ADVET" => [
        "RELATIVE_PATH" => "Uploads/VehicleAdvets"
    ],

    "LAND_ADVET" => [
        "RELATIVE_PATH" => "Uploads/LandAdvets"
    ],

    "AGENCY_ADVET" => [
        "RELATIVE_PATH" => "Uploads/AgencyAdvets"
    ],

    "BANQUTE_MARRIGE_HALL" => [
        "RELATIVE_PATH" => "Uploads/BanquteMarrigeHall"
    ],

    "HOSTEL" => [
        "RELATIVE_PATH" => "Uploads/Hostel"
    ],

    "LODGE" => [
        "RELATIVE_PATH" => "Uploads/Lodge"
    ],

    "DHARAMSHALA" => [
        "RELATIVE_PATH" => "Uploads/Dharamshala"
    ],

    "SELF-LABEL" => [
        "DA" => "6",
        "SI" => "9",
        "EO" => "10"
    ],

    "MARKET-LABEL" => [
        "DA" => "6",
        "SI" => "9",
        "EO" => "10",
        // "Engineer" => "109",
        "Assistant Engineer" => "14",
        "Commitee Member" => "32"
    ],

    "PARAM_IDS" => [
        "TRN" => 37,
    ],

    'PAYMENT_MODE_OFFLINE' => [
        'CASH',
        'CHEQUE',
        'DD',
        'NEFT',
        'RTGS'
    ],
    "ROLE-LABEL" => [
        "BO" => 11,
        "DA" => 6,
        "JE" => 12,     // 40
        "SH" => 13,
        "AE" => 14,
        "EO" => 10
    ],
];
