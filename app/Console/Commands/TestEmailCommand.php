<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'mail:test {email?}';

    protected $description = 'Test email configuration by sending a test email';

    public function handle(): int
    {
        $this->info('🔍 Checking email configuration...');

        // Проверяем переменные окружения
        $requiredEnvVars = [
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_FROM_ADDRESS',
        ];

        $missingVars = [];
        foreach ($requiredEnvVars as $var) {
            if (empty(env($var))) {
                $missingVars[] = $var;
            }
        }

        if (!empty($missingVars)) {
            $this->error('❌ Missing required environment variables:');
            foreach ($missingVars as $var) {
                $this->line("   - {$var}");
            }
            return self::FAILURE;
        }

        $this->info('✅ All required environment variables are set');

        // Выводим конфигурацию
        $this->newLine();
        $this->info('📋 Email Configuration:');
        $this->line('   Mailer: ' . env('MAIL_MAILER'));
        $this->line('   Host: ' . env('MAIL_HOST'));
        $this->line('   Port: ' . env('MAIL_PORT'));
        $this->line('   Encryption: ' . (env('MAIL_ENCRYPTION') ?: 'none'));
        $this->line('   Username: ' . env('MAIL_USERNAME'));
        $this->line('   From: ' . env('MAIL_FROM_ADDRESS'));

        // Получаем email получателя
        $recipientEmail = $this->argument('email') ?: env('MAIL_USERNAME');

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Invalid email address: ' . $recipientEmail);
            return self::FAILURE;
        }

        // Отправляем тестовое письмо
        $this->newLine();
        $this->info("⏳ Sending test email to: {$recipientEmail}");

        try {
            Mail::raw('This is a test email from ' . config('app.name') . '. Email configuration is working correctly!', function ($message) use ($recipientEmail) {
                $message->to($recipientEmail)
                    ->subject('Test Email - ' . config('app.name'));
            });

            $this->newLine();
            $this->info('✅ Test email sent successfully!');
            $this->line("   Check inbox at: {$recipientEmail}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());

            // Дополнительная диагностика
            $this->newLine();
            $this->warn('💡 Troubleshooting tips:');
            $this->line('   1. Verify SMTP credentials are correct');
            $this->line('   2. Check if MAIL_HOST resolves correctly (try: ping ' . env('MAIL_HOST') . ')');
            $this->line('   3. Verify SMTP port is open and accessible');
            $this->line('   4. Check if encryption method (SSL/TLS) matches server requirements');
            $this->line('   5. Ensure firewall allows outbound connections to SMTP server');
            $this->line('   6. Try different port (465 for SSL, 587 for TLS, 25 for plain)');

            return self::FAILURE;
        }
    }
}
