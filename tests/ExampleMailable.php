<?php

namespace SynergiTech\Postal\Tests;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExampleMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        private ?string $fromEmail = null
    )
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailable = $this
            ->html('
                <html>
                    <body>
                        Example Mailable
                    </body>
                </html>
            ')
            ->subject('Example Notification')
            ->replyTo('noreply@example.com');

        if ($this->fromEmail !== null) {
            $mailable->from($this->fromEmail);
        }

        return $mailable;
    }
}
