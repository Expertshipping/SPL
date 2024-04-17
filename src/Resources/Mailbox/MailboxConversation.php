<?php
namespace App\Http\Resources\Mailbox;

class MailboxConversation extends \Illuminate\Http\Resources\Json\JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'emails' => MailboxEmail::collection($this->whenLoaded('emails')),
            'company_id' => $this->company_id,
            'subject' => $this->subject,
            'last_email_date' => $this->date(),
        ];
    }

    private function date(){
        if(!$this->last_email_date){
            return null;
        }

        if($this->last_email_date->isToday()){
            return $this->last_email_date->format('H:i');
        }

        if($this->last_email_date->isYesterday()){
            return 'Yesterday';
        }

        if($this->last_email_date->isCurrentYear()){
            return $this->last_email_date->format('M d');
        }

        return $this->last_email_date->format('M d, Y');
    }
}
