<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\MailCategory;
use App\Mail\WelcomeShopperMail;
use App\Mail\VerifyEmailMail;
use App\Mail\ResetPasswordMail;
use App\Mail\NewsletterWelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionalEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_category_enum_values()
    {
        $this->assertEquals('security', MailCategory::Security->value);
        $this->assertEquals('transactional', MailCategory::Transactional->value);
        $this->assertEquals('notification', MailCategory::Notification->value);
        $this->assertEquals('marketing', MailCategory::Marketing->value);
    }

    public function test_shopper_registration_queues_welcome_and_verification_emails()
    {
        Mail::fake();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'johndoe@latestdeal.in',
            'password' => bcrypt('Password123!'),
            'role' => 'shopper'
        ]);

        Mail::to($user->email)->queue(new WelcomeShopperMail($user, 'https://latestdeal.in/email/verify/1/hash'));
        Mail::to($user->email)->queue(new VerifyEmailMail($user, 'https://latestdeal.in/email/verify/1/hash'));

        $this->assertDatabaseHas('users', ['email' => 'johndoe@latestdeal.in']);

        Mail::assertQueued(WelcomeShopperMail::class);
        Mail::assertQueued(VerifyEmailMail::class);
    }

    public function test_api_subscribe_queues_newsletter_welcome_email()
    {
        Mail::fake();

        Mail::to('subscriber_test@latestdeal.in')->queue(new NewsletterWelcomeMail('https://latestdeal.in/unsubscribe/token'));

        Mail::assertQueued(NewsletterWelcomeMail::class);
    }

    public function test_reset_password_mail_queues_on_critical_queue()
    {
        Mail::fake();

        $resetUrl = 'https://latestdeal.in/password/reset/token123';
        Mail::to('user@latestdeal.in')->queue(new ResetPasswordMail($resetUrl));

        Mail::assertQueued(ResetPasswordMail::class, function ($mail) {
            return $mail->queue === 'critical';
        });
    }
}
