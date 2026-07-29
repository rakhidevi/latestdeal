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

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@latestdeal.in',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'johndoe@latestdeal.in']);

        Mail::assertQueued(WelcomeShopperMail::class, function ($mail) {
            return $mail->hasTo('johndoe@latestdeal.in');
        });

        Mail::assertQueued(VerifyEmailMail::class, function ($mail) {
            return $mail->hasTo('johndoe@latestdeal.in');
        });
    }

    public function test_api_subscribe_queues_newsletter_welcome_email()
    {
        Mail::fake();

        $response = $this->postJson('/api/subscribe', [
            'email' => 'subscriber_test@latestdeal.in',
        ]);

        $response->assertStatus(201);
        Mail::assertQueued(NewsletterWelcomeMail::class, function ($mail) {
            return $mail->hasTo('subscriber_test@latestdeal.in');
        });
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
