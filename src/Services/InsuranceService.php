<?php

namespace ExpertShipping\Spl\Services;

use App\Jobs\CancelShipment;
use App\Notifications\CreateInsuranceTransactionFailed;
use App\Notifications\ShipmentInsured;
use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InsuranceService
{
    private $http;
    private static $carriers = [
        'ups' => 1,
        'fedex' => 2,
        'usps' => 3,
        'dhl' => 4,
        'canada-post' => 29,
        'purolator' => 61,
        'canpar' => 66,
        'aramex' => 127
    ];

    const CHANGE_RATE = 1.4;

    private static $fileTypes = [
        'evidences_0' => 1,
        'evidences_1' => 2,
        'evidences_2' => 3,
        'evidences_3' => 4,
        'evidences_4' => 5,
    ];

    private static $services = [
        '01' => '1',
        '02' => '2',
        '03' => '3',
        '12' => '12',
        '13' => '13',
        '14' => '14',
        '59' => '59',
        '65' => '65',
        '07' => '7',
        '7' => '7',
        '08' => '8',
        '11' => '11',
        '54' => '54',
        'SAME_DAY' => 'SAME_DAY',
        'SAME_DAY_CITY' => 'SAME_DAY_CITY',
        'FIRST_OVERNIGHT' => 'FIRST_OVERNIGHT',
        'PRIORITY_OVERNIGHT' => 'PRIORITY_OVERNIGHT',
        'STANDARD_OVERNIGHT' => 'STANDARD_OVERNIGHT',
        'FEDEX_2_DAY_AM' => 'FEDEX_2_DAY_AM',
        'FEDEX_2_DAY' => 'FEDEX_2_DAY',
        'FEDEX_EXPRESS_SAVER' => 'FEDEX_EXPRESS_SAVER',
        'FEDEX_GROUND' => 'FEDEX_GROUND',
        'GROUND_HOME_DELIVERY' => 'GROUND_HOME_DELIVERY',
        'SMART_POST' => 'SMART_POST',
        'INTERNATIONAL_FIRST' => 'INTERNATIONAL_FIRST',
        'INTERNATIONAL_PRIORITY' => 'INTERNATIONAL_PRIORITY',
        'INTERNATIONAL_ECONOMY' => 'INTERNATIONAL_ECONOMY',
        'INTERNATIONAL_PRIORITY_DISTRIBUTION' => 'INTERNATIONAL_PRIORITY_DISTRIBUTION',
        'INTERNATIONAL_ECONOMY_DISTRIBUTION' => 'INTERNATIONAL_ECONOMY_DISTRIBUTION',
        'FEDEX_1_DAY_FREIGHT' => 'FEDEX_1_DAY_FREIGHT',
        'FEDEX_2_DAY_FREIGHT' => 'FEDEX_2_DAY_FREIGHT',
        'FEDEX_3_DAY_FREIGHT' => 'FEDEX_3_DAY_FREIGHT',
        'FEDEX_FREIGHT_PRIORITY' => 'FEDEX_FREIGHT_PRIORITY',
        'FEDEX_FREIGHT_ECONOMY' => 'FEDEX_FREIGHT_ECONOMY',
        'INTERNATIONAL_PRIORITY_FREIGHT' => 'INTERNATIONAL_PRIORITY_FREIGHT',
        'INTERNATIONAL_ECONOMY_FREIGHT' => 'INTERNATIONAL_ECONOMY_FREIGHT',
        // 'USPS' => 'PMES',
        // 'USPS' => 'PMS',
        // 'USPS' => 'FCMS',
        // 'USPS' => 'SPS',
        // 'USPS' => 'MMS',
        // 'USPS' => 'GEGS',
        // 'USPS' => 'PMEIS',
        // 'USPS' => 'PMIS',
        // 'USPS' => 'FCMIS',
        // 'USPS' => 'FCPIS',
        // 'USPS' => 'AMBS',
        // 'USPS' => 'PSLWS',
        // 'USPS' => 'PMIP',
        'USPS' => 'RMS',
        'P' => 'CX',
        'D' => 'CX',
        'F' => 'R5',
        'K' => 'Express900',
        'X' => 'DHLE',
        'L' => 'Express1030',
        'M' => 'Express1030NonDoc',
        'N' => 'DomesticExpress',
        'Y' => 'Express1200NonDoc',
        // 'DHLE' => '81',
        // 'DHLE' => '631',
        // 'DHLE' => '82',
        // 'DHLE' => '36',
        // 'DHLE' => '83',
        // 'DHLE' => '532',
        // 'DHLE' => '531',
        // 'DHLE' => '491',
        // 'DHLE' => '76',
        // 'DHLE' => '77',
        // 'DHLE' => '72',
        // 'DHLE' => '73',
        // 'DHLE' => '27',
        // 'DHLE' => '54',
        'DHLE' => '60',
        'canada-post_regular_parcel' => 'RegularParcel',
        'canada-post_expedited_parcel' => 'ExpeditedParcel',
        'canada-post_xpresspost' => 'Xpresspost',
        'canada-post_xpresspost_certified' => 'XpresspostCertified',
        'canada-post_priority' => 'Priority',
        'PurolatorExpress' => 'PurolatorExpress',
        'PurolatorExpressInternational' => 'PurolatorExpressInternational',
        'PurolatorExpressUS' => 'PurolatorExpressUS',
        'PurolatorGroundUS' => 'PurolatorGroundUS',
        'PurolatorExpress12PM' => "PurolatorExpress12PM",
        'PurolatorExpressBox9AM' => "PurolatorExpressBox9AM",
        'PurolatorExpressEnvelope10:30AM' => "PurolatorExpressEnvelope10:30AM",
        'PurolatorExpressEnvelope9AM' => "PurolatorExpressEnvelope9AM",
        'PurolatorExpressPack' => "PurolatorExpressPack",
        'PurolatorExpressPack12PM' => "PurolatorExpressPack12PM",
        'PurolatorGround10:30AM' => "PurolatorGround10:30AM",
        'PurolatorExpress10:30AM' => "PurolatorExpress10:30AM",
        'PurolatorExpress9AM' => "PurolatorExpress9AM",
        'PurolatorExpressBox10:30AM' => "PurolatorExpressBox10:30AM",
        'PurolatorExpressEnvelope' => "PurolatorExpressEnvelope",
        'PurolatorExpressEnvelope12PM' => "PurolatorExpressEnvelope12PM",
        'PurolatorExpressPack10:30AM' => "PurolatorExpressPack10:30AM",
        'PurolatorExpressPack9AM' => "PurolatorExpressPack9AM",
        "PurolatorExpressUS1030AM" => "PurolatorExpressUS1030AM",
        "PurolatorExpressUS9AM" => "PurolatorExpressUS9AM",
        'PurolatorGround' => "PurolatorGround",
        'PurolatorGround9AM' => "PurolatorGround9AM",
        "canpar_ground" => "Ground",
        "canpar_select" => "Select",
        "canpar_select_pak" => "SelectPak",
        "canpar_overnight_pak" => "OvernightPak",
        "canpar_select_usa" => "SelectUSA",
        "canpar_overnight" => "Overnight",
        "canpar_usa_pack" => "USAPak",
        "canpar_usa" => "USA",
        "canpar_international" => "International",
        'PPX' => 'PPE',
    ];

    private static $countries = [
        [
            'code' => 'CA',
            'min' => 1.25,
            'rate' => 0.825
        ],
        [
            'code' => 'PR',
            'min' => 1.25,
            'rate' => 0.825
        ],
        [
            'code' => 'US',
            'min' => 1.25,
            'rate' => 0.825
        ],
        [
            'code' => 'UM',
            'min' => 1.25,
            'rate' => 0.825
        ],
        [
            'code' => 'VI',
            'min' => 1.25,
            'rate' => 0.825
        ],
        [
            'code' => 'AU',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AT',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'BE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'DK',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'FI',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'FR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'FX',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'DE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'HK',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IS',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'JP',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'KR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'NL',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'NZ',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'NO',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PL',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PT',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'SG',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'ES',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'SE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CH',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GB',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'VG',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'FO',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'Gl',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'LU',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'VA',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CL',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CN',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CZ',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'HU',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IN',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'ID',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IL',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IT',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MX',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AN',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PH',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'TW',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'TH',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'TR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AS',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AD',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AI',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AG',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'AW',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'BS',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'BB',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IO',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'KY',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CX',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CC',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CK',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'HR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CY',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'DM',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'EC',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'EE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'FJ',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PF',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'TF',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GI',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GD',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GP',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GU',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'HN',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'LA',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'LI',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'LT',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MK',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MY',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MV',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MT',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MH',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MQ',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'FM',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MS',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'NC',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'NF',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'MP',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PA',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PY',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'PE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'KN',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'LC',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'VC',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'WS',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'SM',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'SB',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GS',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'TT',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'TC',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'UY',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'VN',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'GG',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'SX',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'JE',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'IM',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'CR',
            'min' => 3.75,
            'rate' => 0.925
        ],
        [
            'code' => 'DZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BJ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BF',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BI',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'CM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'CV',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'CF',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TD',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'CI',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'DJ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'EG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GQ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ER',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ET',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GH',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GN',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KE',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'LS',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'LR',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ML',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MR',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MU',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NE',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'QA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'RW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ST',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SN',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SC',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SL',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ZA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TN',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'UG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ZM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BL',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AF',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AL',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AQ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BH',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BD',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BY',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BT',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BA',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BV',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BR',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BN',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KH',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'CO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'DO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'FK',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GF',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GT',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'GY',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'HT',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'HM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'IQ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'JM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'JO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KI',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'KG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'LV',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'LB',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'YT',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MD',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MN',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'MM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NR',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NP',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NI',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'NU',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'OM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'PK',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'PW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'PG',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'PN',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'RE',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'RO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SK',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SI',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'LK',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SR',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SJ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TJ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TK',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TO',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TM',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'TV',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'UZ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'VU',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'VE',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'WF',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'RS',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'CW',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'BQ',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'AX',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'ME',
            'min' => 5,
            'rate' => 1.25
        ],
        [
            'code' => 'SV',
            'min' => 5,
            'rate' => 1.25
        ]
    ];

    protected $user = null;

    public function __construct()
    {
        $this->http = Http::baseUrl(config('services.insurance.base_uri'));
    }

    public function getRate($insuredValue, $shipFromCountry, $shipToCountry, $carrier, $serviceCode, $cost = false)
    {
        $service = \App\Service::where('code', $serviceCode)->first();
        $currency = auth()->user()?->company?->platformCountry?->currency ?? 'CAD';
        // TODO: Make sure to use the correct exchange rate
        $insuredValueInUSD =  (int) ($currency === 'CAD' ? round(($insuredValue / self::CHANGE_RATE), 0) : $insuredValue);
        $availableDestination = collect(static::$countries)->where('code', $shipToCountry)->first();
        if(!$service->insurance_active){
            return [
                'message' => $service->name .' '. __('is not supported'),
                'rate'  => false
            ];
        }

        if(!$availableDestination){
            return [
                'message' => 'Nous n\'offrons pas d\'assurance pour cette destination',
                'rate'  => false
            ];
        }

        if(!$service->ecabrella_limit){
            return [
                'message' => $service->name . ' ' . __('is not a supported service'),
                'rate'  => false
            ];
        }

        if($service->ecabrella_limit < $insuredValueInUSD){
            return [
                'message' => __('The max supported value for this service is : ') . ($this->convertToCanadianDollar($service->ecabrella_limit) - 1) . __(" american dollars"),
                'rate'  => false
            ];
        }

        if(!isset(static::$carriers[$carrier])){
            return [
                'message' => __("Carrier not found"),
                'rate'  => false
            ];
        }

        //Change the min value to canadian dollar
        $min = $availableDestination['min'] * 1.6;
        $insuranceRate = $insuredValue * ($availableDestination['rate']/100);

        if(!$cost){
            // $rate = self::EXPERT_SHIPPING_RATE_RETAIL;
            // if($this->user){
            //     $user = $this->user;
            // }else{
            //     $user = auth()->user();
            // }
            // if($user && $user->account_type && $user->account_type === "business"){
            //     $rate = self::EXPERT_SHIPPING_RATE_BUSINESS;
            // }

            // $calulatedRate = max($min, $insuranceRate) * $rate;

            $min = 8;
            $calulatedRate = max($min, $insuredValue * 0.04);
            $calulatedRate = round($calulatedRate, 2);
        }else{
            $calulatedRate = max($min, $insuranceRate);
        }

        $charge = $calulatedRate;

        if(
            auth()->user() &&
            auth()->user()->company->is_retail_reseller
            && isset(auth()->user()->company->theme_setting['insurance_rate'])
            && auth()->user()->company->theme_setting['insurance_rate']!=0
        ){
            $calulatedRate += ($calulatedRate * auth()->user()->company->theme_setting['insurance_rate']) / 100;
        }


        return [
            'message' => 'calculated',
            'rate'  => round(($calulatedRate), 2),
            'charge'  => round(($charge), 2),
        ];
    }

    public function createTransaction(\App\Shipment $shipment)
    {
        $params = [
            'userId' => config('services.insurance.user_id'),
            'carrier' => (string) static::$carriers[$shipment->carrier->slug],
            'service' => static::$services[$shipment->service_code],
            'declaredValue' => (string) round(($shipment->package->insured_value / self::CHANGE_RATE), 0),
            'shipFrom' => $shipment->from_country,
            'shipTo' => $shipment->to_country,
            'trackingNum' => $shipment->tracking_number,
            'shipDate' => $shipment->start_date->format('Y-m-d'),
        ];

        $response = $this->http->get('TransactionService.svc/CreateTrans', $params);
        if($response->status()===200 && $response->json()['CreateTransResult']['Result']==="Success"){
            $shipment->update([
                'insurance_transaction_number' => $response->json()['CreateTransResult']['TransactionNum']
            ]);
            $shipment->user->notify(new ShipmentInsured($shipment));
        }else{
            // (new AnonymousNotifiable)
            // ->route('mail', config('mail.to.info'))
            // ->notify(new CreateInsuranceTransactionFailed());
        }
    }

    public function createTransactionForInsurance(Insurance $insurance, $user = null)
    {
        if(!$user){
            $this->user = auth()->user();
        }else{
            $this->user = $user;
        }

        $params = [
            "Carrier" => (string) static::$carriers[$insurance->carrier->slug],
            "Service" => static::$services[$insurance->service->code],
            "DeclaredValue" => round(($insurance->declared_value / self::CHANGE_RATE), 0),
            "FromCountryCode" => $insurance->ship_from,
            "ToCountryCode" => $insurance->ship_to,
            "TrackingNum" => $insurance->tracking_number,
            "ShipDate" => $insurance->ship_date->format('Y-m-d'),
        ];

        if($insurance->shipment){
            $params['FromCountryCode'] = $insurance->shipment->from_country;
            $params['FromName'] = $insurance->shipment->from_name;
            $params['FromEmail'] = $insurance->shipment->from_email;
            $params['FromPhone'] = $insurance->shipment->from_phone;
            $params['FromAddress1'] = $insurance->shipment->from_address_1;
            $params['FromAddress2'] = $insurance->shipment->from_address_2;
            $params['FromCity'] = $insurance->shipment->from_city;
            $params['FromState'] = $insurance->shipment->from_province;
            $params['FromZip'] = $insurance->shipment->from_zip_code;


            $params['ToCountryCode'] = $insurance->shipment->to_country;
            $params['ToName'] = $insurance->shipment->to_name;
            $params['ToEmail'] = $insurance->shipment->to_email;
            $params['ToPhone'] = $insurance->shipment->to_phone;
            $params['ToAddress1'] = $insurance->shipment->to_address_1;
            $params['ToAddress2'] = $insurance->shipment->to_address_2;
            $params['ToCity'] = $insurance->shipment->to_city;
            $params['ToState'] = $insurance->shipment->to_province;
            $params['ToZip'] = $insurance->shipment->to_zip_code;
        }else{
            $params['FromAddress1'] = $insurance->from['address'];
            $params['FromCity'] = $insurance->from['city'];
            $params['FromState'] = $insurance->from['state'];
            $params['FromZip'] = $insurance->from['zip_code'];

            $params['ToAddress1'] = $insurance->to['address'];
            $params['ToCity'] = $insurance->to['city'];
            $params['ToState'] = $insurance->to['state'];
            $params['ToZip'] = $insurance->to['zip_code'];
        }

        if($params['TrackingNum'] === '1ZXXXXXXXXXXXXXXXX'){
            $params['TrackingNum'] = $insurance->shipment->uuid ?? $insurance->tracking_number;
        }

        $response = $this->callApi('post', 'transactions', $params);
        if($response->status()===200){
            $insurance->update([
                'transaction_number' => $response->json()['transactionNum'],
                'status' => 'completed',
                'charge' => $response->json()['fee'] ?? null
            ]);

            return [
                "succeeded" => true,
                "message" => "success",
                "transaction" => $response->json()['transactionNum']
            ];
        }else{
            if($insurance->shipment){
                dispatch_sync(new CancelShipment($insurance->shipment->load('pickup.shipments')));
                // $insurance->shipment->delete();
                throw ValidationException::withMessages([
                    'payment_failed' => "Insurance not created. response is : {$response->body()}"
                ]);
            }
            Log::critical("Insurance not created. response is : {$response->body()}");
            $emails = [config('mail.to.info'),...$this->user->company->managers->filter(fn($m) => $m->pivot->activate_notification)->pluck('email')->toArray()];
            $emails = array_filter($emails);

            (new AnonymousNotifiable())
                ->route('mail', $emails)
                ->notify(new CreateInsuranceTransactionFailed($response->body(), $insurance, $this->user));
        }

        $error = $response->json()['message'] ?? $response->body();

        return [
            "succeeded" => false,
            "message" => $error,
        ];
    }

    public function voidTransaction(Shipment $shipment)
    {
        $tackingNum = $shipment->tracking_number;

        if($tackingNum === '1ZXXXXXXXXXXXXXXXX'){
            $tackingNum = $shipment->uuid;
        }

        $response = $this->callApi('post', "transactions/void/$tackingNum");

        if($response->status()===200){
            $shipment->update([
                'insurance_transaction_voided' => true
            ]);
        }
    }

    public function voidTransactionForInsurance(Insurance $insurance)
    {
        $tackingNum = $insurance->tracking_number;

        if($tackingNum === '1ZXXXXXXXXXXXXXXXX'){
            $tackingNum = $insurance->shipment->uuid;
        }

        $response = $this->callApi('post', "transactions/void/$tackingNum");

        if($response->status()===200){
            $insurance->update([
                'status' => 'voided'
            ]);
            return true;
        }

        return false;
    }

    public function createClaim(\App\Claim $claim)
    {
        $senderData = $claim->meta_data['sender'];
        $sender = [
            'Name'=> $senderData['Name'],
            'Phone'=> $senderData['Phone'],
            'Email'=> $senderData['Email'],
            'Address1'=> $senderData['Address1'],
            'Address2'=> $senderData['Address2'],
            'City'=> $senderData['City'],
            'State'=> $senderData['State']??'N/A',
            'PostalCode'=> $senderData['PostalCode'],
            'CountryCode'=> $senderData['CountryCode'],
            'IsResidential'=> $senderData['IsResidential'],
        ];

        $recipientData = $claim->meta_data['recipient'];
        $recipient = [
            'Name'=> $recipientData['Name'],
            'Phone'=> $recipientData['Phone'],
            'Email'=> $recipientData['Email'],
            'Address1'=> $recipientData['Address1'],
            'Address2'=> $recipientData['Address2'],
            'City'=> $recipientData['City'],
            'State'=> $recipientData['State']??'N/A',
            'PostalCode'=> $recipientData['PostalCode'],
            'CountryCode'=> $recipientData['CountryCode'],
            'IsResidential'=> true,
        ];

        $claimFiles = $claim->media->map(function($file){
            //Case copy of label for dropoff insurances
            if($file->collection_name==="evidences_3"){
                $fileType = 6;
            }else{
                $fileType = static::$fileTypes[$file->collection_name]??1;
            }
            return [
                'FileType' => $fileType,
                'FileUrl' => $file->getUrl(),
                'FileName' => $file->file_name
            ];
        })->toArray();

        if($claim->claimable && $claim->claimable->shipment){
            array_push($claimFiles, [
                'FileType' => 6,
                'FileUrl' => $claim->claimable->shipment->label_url,
                'FileName' => "Copy of Label"
            ]);
        }

        $hasSalvage = $claim->meta_data['claimType']=="2"?$claim->meta_data['hasSalvage']:false;
        $refundGiven = $claim->meta_data['replacementSent']?$claim->meta_data['refundGiven']:false;

        $params = [
            'TrackingNum' => $claim->meta_data['trackingNum'],
            'DiscoveredDate' => $claim->meta_data['discoveredDate'],
            'ClaimType' => $claim->meta_data['claimType'],
            'ClaimAmount' => round(($claim->meta_data['claimAmount'] / self::CHANGE_RATE), 0),
            'LossType' => $claim->meta_data['lossType'],
            'HasSalvage' => $hasSalvage,
            'Contents' => $claim->meta_data['contents'],
            'ReplacementSent' => $claim->meta_data['replacementSent'],
            'RefundGiven' => $refundGiven,
            'ShippingCharge' => $claim->meta_data['shippingCharge'],
            // "ClientNum" : "A100",
            'Sender' => $sender,
            'Recipient' => $recipient,
            'ClaimFiles'=> $claimFiles,
            // "OuterPackaging" : 1,
            // "DoubleBoxed" : false
        ];

        $response = $this->callApi('post', 'claims', $params);
        return $response->json();
    }

    protected function convertToCanadianDollar($amount){
        return $amount*self::CHANGE_RATE;
    }

    private function callApi($method, $uri, $params = [])
    {
        $response = $this->http
            ->withHeaders([
                'Authorization' => 'Bearer ' . Cache::get('cabrella_access_token')
            ])
            ->{$method}($uri, $params);

        if($response->status()===401){
            $this->getAccessToken();
            $response = $this->http->{$method}($uri, $params);
        }

        return $response;
    }

    private function getAccessToken()
    {
        $response = $this->http
            ->withBasicAuth(config('services.insurance.username'), config('services.insurance.password'))
            ->get('token/ApiToken');

        $accessToken = $response->json()['access_token'] ?? null;

        Cache::put('cabrella_access_token', $accessToken, now()->addHours(2));
    }
}
