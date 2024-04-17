<?php
namespace App\Http\Resources\Mailbox;

use ExpertShipping\Spl\Models\Mailbox\MailboxSeen;
use PhpImap\Mailbox;

class MailboxEmail extends \Illuminate\Http\Resources\Json\JsonResource
{
    protected $loadSeenBy = false;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->sender_name ?? $this->sender_email ?? 'Unknown',
            'mailbox_folder_id' => $this->mailbox_folder_id,
            // 'mailbox_folder' => $this->whenLoaded('mailboxFolder'),
            'company_id' => $this->company_id,
            'email_id' => $this->id,
            'message_id' => $this->message_id,
            'sender_email' => $this->sender_email,
            'sender_name' => $this->sender_name,
            'subject' => $this->subject,
            'text_plain' => $this->text_plain,
            'text_html' => $this->html(),
            'is_seen' => $this->is_seen,
            'is_answered' => $this->is_answered,
            'is_recent' => $this->is_recent,
            'is_flagged' => $this->is_flagged,
            'is_deleted' => $this->is_deleted,
            'is_draft' => $this->is_draft,
            'cc' => $this->cc,
            'to' => $this->to,
            'bcc' => $this->bcc,
            'reply_to' => $this->reply_to,
            'date' => $this->date(),
            'date_details' => $this->dateDetails(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attachments' => $this->whenLoaded('attachments'),
            'seen_by' => $this->getSeenBy(),
        ];
    }

    public function withSeenBy(){
        $this->loadSeenBy = true;

        return $this;
    }

    private function html()
    {
        if(!$this->text_html_url || empty($this->text_html_url)){
            return null;
        }

        $html = file_get_contents($this->text_html_url, false, stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]));

        return $html;
    }

    private function date(){
        if(!$this->date){
            return null;
        }

        if($this->date->isToday()){
            return $this->date->format('H:i');
        }

        if($this->date->isYesterday()){
            return 'Yesterday';
        }

        if($this->date->isCurrentYear()){
            return $this->date->format('M d');
        }

        return $this->date->format('M d, Y');
    }

    private function dateDetails(){
        if(!$this->date){
            return null;
        }

        // if less then 1 month
        if($this->date->diffInDays() < 30){
            return $this->date->format('M d, Y') . ' at ' . $this->date->format('H:i') . ' (' . $this->date->diffForHumans() . ')';
        }

        // if less then 1 year
        if($this->date->diffInMonths() < 12){
            return $this->date->format('M d, Y') . ' at ' . $this->date->format('H:i');
        }

        return $this->date->format('M d, Y') . ' at ' . $this->date->format('H:i');
    }

    private function getSeenBy(){
        if(!$this->loadSeenBy){
            return null;
        }

        $seenBy = MailboxSeen::where('mailbox_email_id', $this->id)
            ->with('user')
            ->get()
            ->pluck('user');

        $seenBy = $seenBy->map(function($user){
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return $seenBy;
    }
}
