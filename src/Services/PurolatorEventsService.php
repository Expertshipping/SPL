<?php

namespace ExpertShipping\Spl\Services;

use App\CarrierEvent;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\Sns\SnsClient;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class PurolatorEventsService
{

    public static $updates = [
        'ARPC_1' => '9550',
        'ARPC_2' => '9550',
        'ARPC_3' => '6710',
        'ARPC_4' => '9620',
        'ARPC_5' => '6730',
        'ARPC_6' => '6720',

        'PAYMENT_RECEIVED_1' => '9650',
        'PAYMENT_RECEIVED_2' => '9650',
        'PAYMENT_RECEIVED_3' => '9650',

        'CODE_LOCALISATION_TABLETTE' => '9550',

        'REFUSED' => '9640',

        'DAMAGED_1' => '6500',
        'DAMAGED_2' => '6570',
    ];

    public static $categoriesCodes = [
        'delivery' => '9500',
        'dropoff' => '2520',
        'late_dropoff' => '2359',
    ];

    public static $categoriesHubEvents = [
        'delivery' => null,
        'dropoff' => null,
        'late_dropoff' => 'RETAIL',
    ];

    public static $updatesCodes = [
        'ARPC_1' => '9550',
        'ARPC_2' => '9550',
        'ARPC_3' => '6710',
        'ARPC_4' => '9620',
        'ARPC_5' => '6730',
        'ARPC_6' => '6720',

        'PAYMENT_RECEIVED_1' => '9650',
        'PAYMENT_RECEIVED_2' => '9650',
        'PAYMENT_RECEIVED_3' => '9650',

        'CODE_LOCALISATION_TABLETTE' => '9550',

        'REFUSED' => '9640',

        'DAMAGED_1' => '6500',
        'DAMAGED_2' => '6570',
    ];

    public static $updatesHubEvents = [
        'ARPC_1' => 'AGENT',
        'ARPC_2' => 'HOLD UNTIL',
        'ARPC_3' => 'HFPU',
        'ARPC_4' => 'RECV-NO PICKUP',
        'ARPC_5' => 'HFPU',
        'ARPC_6' => 'HFPU',

        'PAYMENT_RECEIVED_1' => null,
        'PAYMENT_RECEIVED_2' => 'INBOUND_COLLECT',
        'PAYMENT_RECEIVED_3' => 'REFUND',

        'CODE_LOCALISATION_TABLETTE' => null,

        'REFUSED' => 'RECEIVER_REFUSE',

        'DAMAGED_1' => 'DAMAGED_SHIP',
        'DAMAGED_2' => 'EMPTY_SHIPMENT',
    ];

    public static $sourceEvents = [
        'update' => 'DeliveryEventLegacy',
        'delivery' => 'DeliveryEventLegacy',
        'dropoff' => 'PickupEventLegacy',
        'late_dropoff' => 'PickupEventLegacy',
    ];

    public function sendEvent($attributes, $store)
    {
        if(env('WHITE_LABEL_COUNTRY') !== 'CA'){
            return;
        }

        $msgHdrID = (string) Uuid::uuid4();
        $routeNumber = "AGN";

        $comment = $attributes['comment'] ?? '';

        if(isset($attributes['update']) && $attributes['update']==='CODE_LOCALISATION_TABLETTE'){
            $comment = $store->purolator_loc_id ." SHELF ". $comment;
        }

        if(isset($attributes['update']) && $attributes['update']==='ARPC_1'){
            $comment = $store->purolator_loc_id;
        }

        if($attributes['category']==='delivery'){
            $comment = $comment . " at ".env('APP_NAME')." " . $store->addr1 . " " . $store->city;
        }

        $eventCode = '';
        $reasonCode = '';
        if(in_array($attributes['category'], ['delivery','dropoff', 'late_dropoff'])){
            $eventCode = self::$categoriesCodes[$attributes['category']];
            $reasonCode = self::$categoriesHubEvents[$attributes['category']];
        }

        if(in_array($attributes['category'], ['update'])){
            $eventCode = self::$updatesCodes[$attributes['update']];
            $reasonCode = self::$updatesHubEvents[$attributes['update']];
        }

        $trackings = $attributes['trackings'];
        foreach ($trackings as $tracking) {
            $carrierEvent = CarrierEvent::find($tracking['carrier_event_id']);
            if(is_string($tracking)){
                $tracking = (array) json_decode($tracking);
            }

            $scanItems = [
                "scanItm" => [
                    "scanTms" => date('Y-m-d\TH:i:s.vp'),
                    "trkId" => $tracking['pin'],
                    "brcd" => [
                        // "captureMthdCd" => ($tracking['barcode_type']==="PIN" || is_null($tracking['barcode_type'])) ? "MANUAL" : "AUTOMATED",
                        "captureMthdCd" => "AUTOMATED",
                        "brcdTypCd" => $tracking['barcode_type'] ?? 'PIN',
                        "brcdStrng" => $tracking['code'],
                    ]
                ]
            ];

            $payload = [
                "msgHdr" => [
                    "id" => $msgHdrID,
                    "vers" => "1.3",
                    "pubTms" => date('Y-m-d\TH:i:s.vp')
                ],
                "eventFldr" => [
                    "fldrHdr" => [
                        "busCd" => "PURO",
                        "lobCd" => "COURIER",
                        "termId" => $store->purolator_term_id,
                        "srcSysCd" => "AGENT",
                        "srcSysRefCd" => "EXPERTSHIPPING",
                        "usrId" => (string) auth()->id(),
                        "rteId" => $routeNumber
                    ],
                    "events" => [
                        [
                            "eventHdr" => [
                                "eventTms" => date('Y-m-d\TH:i:s.vp'),
                                "eventCd" => $eventCode,
                                "eventRsnCd" => $reasonCode
                            ],
                            "scanItms" => [$scanItems],
                            "loc" => [
                                "locTypCd" => "RETAIL",
                                "locId" => $store->purolator_loc_id,
                                "gps" => [
                                    "lat" => $store->lat_lng['lat'],
                                    "lng" => $store->lat_lng['lng']
                                ],
                                "eventAddr" => [
                                    "consAddrLn1" => $store->addr1,
                                    "muni" => $store->city,
                                    "rgnCd" => $store->state,
                                    "ctryCd" => $store->country,
                                    "postCd" => $store->zip_code,
                                ]
                            ],
                            'comment' => $comment
                        ]
                    ]
                ]
            ];

            if(isset($attributes['update']) && $attributes['update'] === 'ARPC_2'){
                try {
                    $formatedDate = Carbon::parse($payload['eventFldr']['events'][0]['comment']);
                    $payload['eventFldr']['events'][0]['comment'] = $formatedDate->format('M d');
                } catch (Exception $e) {
                    throw $e;
                }
            }

            if($attributes['category']=='dropoff' || $attributes['category']=='late_dropoff'){
                unset($payload['eventFldr']['events'][0]['loc']['eventAddr']);
            }

            if($payload['eventFldr']['events'][0]['comment']==='' || is_null($payload['eventFldr']['events'][0]['comment'])){
                unset($payload['eventFldr']['events'][0]['comment']);
            }

            if($payload['eventFldr']['events'][0]['eventHdr']['eventRsnCd']==='' || is_null($payload['eventFldr']['events'][0]['eventHdr']['eventRsnCd'])){
                unset($payload['eventFldr']['events'][0]['eventHdr']['eventRsnCd']);
            }

            if($attributes['category']==='delivery'){
                $payload['eventFldr']['events'][0]['loc']['custCntct'] = [
                    "attnNm" => $attributes['comment'] ?? '',
                ];
                if(!$attributes['verbal_signature']){
                    $s3client = new S3Client([
                        'region' => config('services.purolator_events.region'),
                        'version' => 'latest',
                        'credentials' => new Credentials(
                            config('services.purolator_events.key'),
                            config('services.purolator_events.secret')
                        )
                    ]);

                    $imgObjNm = date('His').".PNG";
                    $imgFldr = "EXPERTSHIPPING/PNG/".date('Y')."/".date('m')."/".date('d')."/".$store->purolator_term_id."/".$routeNumber."/";

                    try {
                        $url = asset('static/images/signature'.rand(1, 18).'.png');
                        $filename = time() .".png";
                        $filenameCopy = time() ."-copy.png";
                        if(request()->has('signature_file')){
                            Storage::disk('tmp')->put($filename, request('signature_file'));
                            optional($carrierEvent)->update([
                                'signature_origin' => 'mobile'
                            ]);
                        }else{
                            $arrContextOptions=array(
                                "ssl"=>array(
                                    "verify_peer"=>false,
                                    "verify_peer_name"=>false,
                                ),
                            );
                            Storage::disk('tmp')->put($filename, file_get_contents($url, false, stream_context_create($arrContextOptions)));
                            optional($carrierEvent)->update([
                                'signature_origin' => 'desktop'
                            ]);
                        }

                        $path = config('filesystems.disks.tmp.root'). "/".$filename;
                        $pathCopy = config('filesystems.disks.tmp.root'). "/".$filename;
                        copy($path, $pathCopy);

                        if($carrierEvent){
                            $s3client->putObject([
                                'Bucket' => config('services.purolator_events.env'),
                                'Key' => $imgFldr.$imgObjNm,
                                'SourceFile' => $path
                            ]);

                            $carrierEvent->addMedia($pathCopy)
                                ->usingFileName($filename)
                                ->toMediaCollection("carrier-event-signatures", 's3');
                        }else{
                            $s3client->putObject([
                                'Bucket' => config('services.purolator_events.env'),
                                'Key' => $imgFldr.$imgObjNm,
                                'SourceFile' => $path
                            ]);
                        }

                    } catch (Exception $exception) {
                        throw $exception;
                    }

                    $payload['eventFldr']['events'][0]['sig'] = [
                        "sigCd" => "RECEIVER",
                        "sigNm" => $attributes['comment'],
                        "imgCntnr" => config('services.purolator_events.env'),
                        "imgFldr" => $imgFldr,
                        "imgObjNm" => $imgObjNm
                    ];
                }else{
                    $payload['eventFldr']['events'][0]['eventHdr']['eventRsnCd']= "VERBAL";
                }
            }

            $client = new SnsClient([
                'version' => config('services.purolator_events.version'),
                'region' => config('services.purolator_events.region'),
                'credentials' => new Credentials(
                    config('services.purolator_events.key'),
                    config('services.purolator_events.secret')
                )
            ]);

            $subject = 'SNS Message';

            $message = json_encode($payload);

            $res = $client->publish([
                'TopicArn' => config('services.purolator_events.topic_arn'),
                'Message' => $message,
                'Subject' => $subject,
                'Username' => "infohub-prod-ExpertShipping-user",
                "MessageAttributes" => [
                    "sourceEvent" => [
                        "DataType" => "String",
                        "StringValue" => self::$sourceEvents[$attributes['category']],
                    ]
                ]
            ]);

            if(
                (isset($attributes['update']) && $attributes['update']!=='CODE_LOCALISATION_TABLETTE' && $attributes['update']!=='ARPC_1') ||
                !isset($attributes['update'])
            ){
                CarrierEvent::where('tracking_number', $tracking['pin'])->where('meta_data->update', 'CODE_LOCALISATION_TABLETTE')->update([
                    'arpc_agent_count' => -1,
                ]);
            }
        }

    }

    public static function getUpdatesName($code){
        $updates = [
            'ARPC_1' => __("Tracking Update / 28- ÀRPC / Agent"),
            'ARPC_2' => __("Tracking Update / 28- ÀRPC / Hold Until"),
            'ARPC_3' => __("Tracking Update / 28- ÀRPC / Return to sender"),
            'ARPC_4' => __("Tracking Update / 28- ÀRPC / Unclaimed - returned to sender"),
            'ARPC_5' => __("Tracking Update / 28- ÀRPC / Redirected"),
            'ARPC_6' => __("Tracking Update / 28- ÀRPC / Re-delivered by carrier"),

            'PAYMENT_RECEIVED_1' => __("Tracking Update / 37- Payment Received / Payment Received"),
            'PAYMENT_RECEIVED_2' => __("Tracking Update / 37- Payment Received / Inbound Collect"),
            'PAYMENT_RECEIVED_3' => __("Tracking Update / 37- Payment Received / Refund"),

            'CODE_LOCALISATION_TABLETTE' => __("Tracking Update / 28- Code Shelf localization"),

            'REFUSED' => __("Tracking Update / 4- Refused"),

            'DAMAGED_1' => __("Tracking Update / 18- Damaged / Damaged Shipment"),
            'DAMAGED_2' => __("Tracking Update / 18- Damaged / Empty Shipment"),
        ];

        return $updates[$code];
    }

    public static function getCategorieName($code){
        $categories = [
            'update' => __("Tracking Update"),
            'delivery' => __("Received by Customer"),
            'dropoff' => __("Customer Drop Off"),
            'late_dropoff' => __("Late Customer Drop Off"),
        ];

        return $categories[$code];
    }

    public static function getMetaData($code){
        $allData = explode('|', $code);
        $collection = collect($allData)->map(function($line){
            $lineArray = explode('~', $line);

            return [
                'key' =>  $lineArray[0],
                'value' =>  $lineArray[1],
            ];
        });

        return [
            'barcode_id'              => $collection->where('key', 'V01')->first()['value'] ?? null,
            'origin_postal_code'      => $collection->where('key', 'D01')->first()['value'] ?? null,
            'receiver_name'           => $collection->where('key', 'R01')->first()['value'] ?? null,
            'receiver_suite'          => $collection->where('key', 'R02')->first()['value'] ?? null,
            'receiver_street'         => $collection->where('key', 'R03')->first()['value'] ?? null,
            'receiver_address_1'      => $collection->where('key', 'R04')->first()['value'] ?? null,
            'receiver_address_2'      => $collection->where('key', 'R05')->first()['value'] ?? null,
            'receiver_city'           => $collection->where('key', 'R06')->first()['value'] ?? null,
            'receiver_postal_code'    => $collection->where('key', 'R07')->first()['value'] ?? null,
            'lead_pin_pro'            => $collection->where('key', 'S01')->first()['value'] ?? null,
            'piece_unit_pin'          => $collection->where('key', 'S02')->first()['value'] ?? null,
            'transportation_mode'     => $collection->where('key', 'S03')->first()['value'] ?? null,
            'delivery_time'           => $collection->where('key', 'S04')->first()['value'] ?? null,
            'shipment_type'           => $collection->where('key', 'S05')->first()['value'] ?? null,
            'delivery_type'           => $collection->where('key', 'S06')->first()['value'] ?? null,
            'diversion_code'          => $collection->where('key', 'S07')->first()['value'] ?? null,
            'shipment_date'           => $collection->where('key', 'S08')->first()['value'] ?? null,
            'piece_number'            => $collection->where('key', 'S09')->first()['value'] ?? null,
            't_otal_piece_count'      => $collection->where('key', 'S10')->first()['value'] ?? null,
            'piece_unit_weight'       => $collection->where('key', 'S11')->first()['value'] ?? null,
            'total_shipment_weight'   => $collection->where('key', 'S12')->first()['value'] ?? null,
            'unicode'                 => $collection->where('key', 'S13')->first()['value'] ?? null,
            'airport_code'            => $collection->where('key', 'S14')->first()['value'] ?? null,
            'handling_class_type'     => $collection->where('key', 'S15')->first()['value'] ?? null,
            'product_number'          => $collection->where('key', 'B01')->first()['value'] ?? null,
            'billing_type'            => $collection->where('key', 'B02')->first()['value'] ?? null,
        ];
    }
}
