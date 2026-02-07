<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-test-mail {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email via the configured mailer';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $recipient = $this->argument('email');

        $this->info("Attempting to send a test email to: {$recipient}...");

        try {
            Mail::raw('This is a test email.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Test Email');
            });

            $this->info('Success! The test email has been sent.');
        } catch (\Exception $e) {
            $this->error('Failed to send email. Error: ' . $e->getMessage());
        }
    }
}
