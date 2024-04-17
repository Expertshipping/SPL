<?php

namespace ExpertShipping\Spl\Resources;

use DOMDocument;
use Illuminate\Http\Resources\Json\JsonResource;
use SimpleXMLElement;

class CarrierEvent extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'company'   => $this->whenLoaded('company'),
            'user'      => $this->whenLoaded('user'),
            'carrier'   => $this->whenLoaded('carrier'),
            'type'      => $this->type,
            'tracking_number'   => $this->tracking_number,
            'route_code'        => $this->route_code,
            'cycle'             => $this->cycle,
            'route_identifier'  => $this->route_identifier,
            'courier_number'    => $this->courier_number,
            'signatory'         => $this->signatory,
            'organization_function_identifier'  => $this->organization_function_identifier,
            'date'                              => $this->created_at->format("d-m-Y/H:i"),
            'response'                          => $this->getMessageFromXml($this->response),
            'signature_origin'                  => $this->signature_origin,
        ];
    }

    private function getMessageFromXml ($xml){
        if(!$xml || $xml==''){
            return '';
        }

        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML( $xml ?? '' );
        $XMLresults = $doc->getElementsByTagName("eventCreateAndPublishResp");
        if(
            $XMLresults->item(0) &&
            $XMLresults->item(0)->childNodes->length>0 &&
            $XMLresults->item(0)->childNodes->item(0) &&
            $XMLresults->item(0)->childNodes->item(0)->childNodes->length>0
        ){
            for ($i=0; $i < $XMLresults->item(0)->childNodes->item(0)->childNodes->length-1; $i++) {
                $item = $XMLresults->item(0)->childNodes->item(0)->childNodes->item($i);
                if($item->tagName === "RspSt"){
                    $message = $item->childNodes->item(4)->textContent;
                    if($message === "Service operation execution finished successfully") {
                        return $message;
                    }
                }
            }
        }


        $XMLresults2 = $doc->getElementsByTagName("eventCreateAndPublishMultipleCM2Resp");

        if($XMLresults2->item(0) && $XMLresults2->item(0)->childNodes->length>0){
            return trim(preg_replace('/\s\s+/', ' ', $XMLresults2->item(0)->textContent ?? ''));
        }


        $res = new SimpleXMLElement($xml);
        return $res->faultstring[0];
    }
}
