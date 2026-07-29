<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\WelcomeShopperMail;
use App\Mail\VerifyEmailMail;
use App\Mail\NewsletterWelcomeMail;
use App\Mail\ResetPasswordMail;

class SendTestEmail extends Command
{
    protected $signature = 'email:send-test {email=hi.pankajtiwari86@gmail.com : Recipient email address} {--type=welcome : Type of email: welcome, verify, newsletter, reset}';

    protected $description = 'Send a test email using info-noreply@latestdeal.in sender configuration';

    public function handle()
    {
        $recipient = $this->argument('email');
        $type = $this->option('type');

        $this->info("Sending {$type} test email from info-noreply@latestdeal.in to {$recipient}...");

        $dummyUser = new User([
            'name' => 'Pankaj Tiwari',
            'email' => $recipient,
        ]);
        $dummyUser->id = 1;

        try {
            switch ($type) {
                case 'verify':
                    $url = url('/email/verify/1/' . sha1($recipient));
                    Mail::to($recipient)->send(new VerifyEmailMail($dummyUser, $url));
                    break;

                case 'newsletter':
                    $url = url('/unsubscribe/test-token-1234567890');
                    Mail::to($recipient)->send(new NewsletterWelcomeMail($url));
                    break;

                case 'reset':
                    $url = url('/password/reset/test-token-1234567890');
                    Mail::to($recipient)->send(new ResetPasswordMail($url));
                    break;

                case 'welcome':
                default:
                    $url = url('/email/verify/1/' . sha1($recipient));
                    Mail::to($recipient)->send(new WelcomeShopperMail($dummyUser, $url));
                    break;
            }

            $this->info("✅ Test email ({$type}) dispatched successfully to {$recipient}!");
            return 0;
        } catch (\Throwable $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return 1;
        }
    }
}
