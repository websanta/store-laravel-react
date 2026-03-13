<?php

namespace App\Mail;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Vendor $vendor,
        public string $status,
        public ?string $rejectionReason = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->status) {
            'approved' => 'Your request has been approved',
            'rejected' => 'Your request has been rejected',
            default => 'Vendor Status Changed',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.vendor_status_changed',
        );
    }
}
