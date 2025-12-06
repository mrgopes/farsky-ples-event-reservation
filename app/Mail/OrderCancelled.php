<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $eventTitle = optional($this->order->event)->title ?? '';

        return $this->subject('Objednávka zrušená - ' . $eventTitle)
                    ->view('emails.order-cancelled')
                    ->with(['order' => $this->order]);
    }
}

