<?php

namespace App\Console\Commands;

use App\Modules\Setting\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RunBackup extends Command
{
    protected $signature = 'app:run-backup';
    protected $description = 'Run database backup and email it';

    public function handle()
    {
        Log::info('Backup process started.');

        try {
            // 1️⃣ Generate a filename with timestamp
            $fileName = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
            $backupDir = storage_path('app/backups');

            // 2️⃣ Make sure the backups folder exists
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $fileName;

            // 3️⃣ Database credentials from .env
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT', 3306);
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            // 4️⃣ Detect mysqldump path dynamically
            $mysqldumpPath = trim(shell_exec('which mysqldump'));
            if (!$mysqldumpPath) {
                throw new \Exception('mysqldump not found. Please install mysql-client.');
            }

            // 5️⃣ Build backup command
            $passwordPart = !empty($dbPass) ? "-p{$dbPass}" : '';
            $command = "{$mysqldumpPath} -h {$dbHost} -u {$dbUser} {$passwordPart} "
                . "--routines --triggers --events --single-transaction --quick --add-drop-table "
                . "{$dbName} > {$filePath}";

            exec($command . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($filePath)) {
                throw new \Exception('Database backup failed. Command output: ' . implode("\n", $output));
            }

            $backupEmail = Setting::first();

            // 6️⃣ Send backup via email using .env credentials
            $recipient = $backupEmail->backup_email ?? 'test@gmail.com'; // Send to your configured email

            $subject = "Database Backup for MERCHANT OVERSEAS – " . date('d M Y, h:i A');

            $htmlMessage = "
    <h2 style='color:#2c3e50;'>Database Backup Successful</h2>
    <p>Hello,</p>
    <p>Your automated database backup has been successfully generated.</p>
    <p><strong>Backup Time:</strong> " . date('d M Y, h:i A') . "</p>
    <p><strong>Backup File:</strong> {$fileName}</p>
    <p>Please find the backup attached.</p>
    <br>
    <p style='color:#7f8c8d;'>— System Auto Backup Service</p>
";

            Mail::send([], [], function ($message) use ($recipient, $filePath, $fileName, $subject, $htmlMessage) {
                $message->to($recipient)
                    ->subject($subject)
                    ->html($htmlMessage) // ✅ Correct method for HTML emails
                    ->attach($filePath, [
                        'as' => $fileName,
                        'mime' => 'application/sql',
                    ]);
            });


            // Optional: delete backup file after sending
            unlink($filePath);

            Log::info('Backup completed and emailed successfully.');

            return 0; // Success

        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return 1; // Failure
        }
    }
}