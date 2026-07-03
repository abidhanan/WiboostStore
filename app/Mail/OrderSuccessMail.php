<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->loadMissing('product.category.parent.parent.parent', 'user');
    }

    public function envelope(): Envelope
    {
        $statusLabel = $this->transaction->order_status === 'failed' ? 'Gagal' : 'Berhasil';

        return new Envelope(
            subject: 'Pesanan Wiboost ' . $statusLabel . ' - ' . $this->transaction->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_success',
        );
    }
}
