<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Mail;
use App\Models\NotificationTemplate;
use Illuminate\Console\Command;
use App\Models\DocumentUpload;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;

class SendDocumentExpiryReminder extends Command
{
    protected $signature = 'send:documentexpirereminder';
    protected $description = 'Send email reminder for documents nearing expiry';

    private $templateMapping = [
        'daily_90' => 22,
        'once_60' => 1,
        'once_30' => 18,
        'once_21' => 19,
        'once_15' => 20,
        'daily_14' => 21,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $legalTeamUsers = [];
        $today = Carbon::today();

        $userDocuments = [];

        DocumentUpload::expirable()->chunkById(1000, function ($documents) use ($legalTeamUsers, $today, &$userDocuments) {
            foreach ($documents as $document) {
                if (!self::isDateValid($document->expiry_date)) {
                    continue;
                }

                $expiryDate = Carbon::parse($document->expiry_date);
                $notificationType = $this->getNotificationType($today, $expiryDate);

                $documentOwners = User::select('id')->when(!empty($document->document_owners) && is_array($document->document_owners), function ($builder) use ($document) {
                    $builder->whereIn('employee_id', $document->document_owners);
                }, function ($builder) {
                    $builder->where('id', 0);
                })->pluck('id')->toArray();

                $legalTeamUsers = array_merge($legalTeamUsers, $documentOwners);

                if ($notificationType) {
                    $this->addDocumentToUsers($document, $notificationType, $legalTeamUsers, $userDocuments);
                }
            }
        });

        foreach ($userDocuments as $userId => $notificationTypes) {
            $user = User::find($userId);
            if ($user) {
                foreach ($notificationTypes as $notificationType => $documents) {
                    $template = $this->getTemplate($notificationType);
                    if ($template) {
                        $this->sendConsolidatedEmail($user, $documents, $template, $notificationType);
                    }
                }
            }
        }
    }

    private function getNotificationType($today, $expiryDate)
    {
        $daysUntilExpiry = $today->diffInDays($expiryDate, false);

        if ($daysUntilExpiry === 60) {
            return 'once_60';
        }
        if ($daysUntilExpiry === 30) {
            return 'once_30';
        }
        if ($daysUntilExpiry === 21) {
            return 'once_21';
        }
        if ($daysUntilExpiry === 15) {
            return 'once_15';
        }

        if ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 14) {
            return 'daily_14';
        }
        if ($daysUntilExpiry >= 15 && $daysUntilExpiry <= 90) {
            return 'daily_90';
        }

        return null;
    }

    private function getTemplate($notificationType)
    {
        $templateId = $this->templateMapping[$notificationType] ?? null;
        
        if (!$templateId) {
            $this->error("No template mapping found for notification type: {$notificationType}");
            return null;
        }

        $template = NotificationTemplate::find($templateId);
        
        if (!$template) {
            $this->error("Template not found for ID: {$templateId} (notification type: {$notificationType})");
            return null;
        }

        return $template;
    }

    private function addDocumentToUsers($document, $notificationType, $legalTeamUsers, &$userDocuments)
    {
        $domUsers = Store::select('dom_id')->where('id', $document->location_id)->pluck('dom_id')->toArray();
        
        $roleIds = in_array($notificationType, ['daily_90']) 
            ? [Helper::$roles['operations-manager']]
            : [Helper::$roles['operations-manager'], Helper::$roles['director'], Helper::$roles['admin']];

        $operationManagerUsers = User::select('id')->whereHas('roles', function ($builder) use ($roleIds) {
            $builder->whereIn('id', $roleIds);
        })->pluck('id')->toArray();

        $finalUsers = array_unique(array_merge($legalTeamUsers, $domUsers, $operationManagerUsers));

        foreach ($finalUsers as $userId) {
            if (!isset($userDocuments[$userId])) {
                $userDocuments[$userId] = [];
            }
            if (!isset($userDocuments[$userId][$notificationType])) {
                $userDocuments[$userId][$notificationType] = [];
            }
            $userDocuments[$userId][$notificationType][] = $document;
        }
    }

    private function sendConsolidatedEmail($user, $documents, $template, $notificationType)
    {
        $attachments = [];
        $documentList = [];

        foreach ($documents as $document) {
            $documentList[] = [
                'store_name' => $document->store->name ?? 'N/A',
                'store_code' => $document->store->code ?? 'N/A',
                'document_type' => $document->document->name ?? 'N/A',
                'issue_date' => self::isDateValid($document->issue_date) ? date('d-m-Y', strtotime($document->issue_date)) : 'N/A',
                'expiry_date' => self::isDateValid($document->expiry_date) ? date('d-m-Y', strtotime($document->expiry_date)) : 'N/A',
                'days_left' => Carbon::now()->diffInDays(Carbon::createFromFormat('Y-m-d', $document->expiry_date), false)
            ];

            $filePath = public_path("storage/documents/{$document->file_name}");
            if (is_file($filePath)) {
                $attachments[] = $filePath;
            }
        }

        $title = $this->replaceUserVariables($template->title, $user);
        $content = $this->buildConsolidatedContent($template->content, $user, $documentList);

        Mail::to($user->email)->send(new \App\Mail\DocumentExpiryMail($title, $content));
        Mail::to('rain.creatives@gmail.com')->send(new \App\Mail\DocumentExpiryMail($title, $content));
        Mail::to('keval@raincreatives.com')->send(new \App\Mail\DocumentExpiryMail($title, $content));

        $this->info("Sent [{$notificationType}] to: " . ($user->employee_id ?? '') . ' - ' . ($user->name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? '') . ' (' . count($documents) . ' documents)');
    }

    private function buildConsolidatedContent($templateContent, $user, $documentList)
    {
        $userName = ($user->name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? '');
        
        $documentTable = '';

        $groupedDocumentList = collect($documentList)
            ->groupBy('document_type')
            ->toArray();

        foreach ($groupedDocumentList as $groupedDocumentListKey => $groupedDocumentListRow) {

        $documentTable .= ('<h3 style="font-family:Arial,Helvetica,sans-serif; color:#111827; margin:20px 0 10px 0;">' . $groupedDocumentListKey . '</h3>');

        foreach ($groupedDocumentListRow as $doc) {

                $documentTable .= ('
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:0 0 16px 0; background:#fafafa; border:1px solid #e5e7eb; border-radius:6px;">
                    <tr>
                        <td style="padding:16px; font-family:Arial,Helvetica,sans-serif;">

                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">

                                <tr>
                                    <td style="color:#6b7280; font-size:12px; padding:6px 0;">Document Name</td>
                                    <td align="right" style="font-weight:bold; color:#111827; padding:6px 0;">' . $groupedDocumentListKey . '</td>
                                </tr>

                                <tr>
                                    <td style="color:#6b7280; font-size:12px; padding:6px 0;">Store</td>
                                    <td align="right" style="font-weight:bold; color:#111827; padding:6px 0;">' . $doc['store_code'] . ' - ' . $doc['store_name'] . '</td>
                                </tr>

                                <tr>
                                    <td style="color:#6b7280; font-size:12px; padding:6px 0;">Issue Date</td>
                                    <td align="right" style="font-weight:bold; color:#111827; padding:6px 0;">' . $doc['issue_date'] . '</td>
                                </tr>

                                <tr>
                                    <td style="color:#6b7280; font-size:12px; padding:6px 0;">Expiration Date</td>
                                    <td align="right" style="font-weight:bold; color:#111827; padding:6px 0;">' . $doc['expiry_date'] . '</td>
                                </tr>

                                <tr>
                                    <td style="color:#6b7280; font-size:12px; padding:6px 0;">Days Remaining</td>
                                    <td align="right" style="font-weight:bold; color:#cf1322; padding:6px 0;">' . $doc['days_left'] . ' Days</td>
                                </tr>

                            </table>

                        </td>
                    </tr>
                </table>
                ');
            }
        }
    
        $documentTable .= '</div>';

        $replacements = [
            '{$name}' => $userName,
            '{$username}' => $user->username ?? 'N/A',
            '{$phone_number}' => $user->phone_number ?? 'N/A',
            '{$email}' => $user->email ?? 'N/A',
            '{$documents_table}' => $documentTable
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $templateContent);
    }

    private function replaceUserVariables($content, $user)
    {
        $replacements = [
            '{$name}' => ($user->name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''),
            '{$username}' => $user->username ?? 'N/A',
            '{$email}' => $user->email ?? 'N/A',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private static function isDateValid($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);

        if ($value === '') {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            return false;
        }

        return $date->format('Y-m-d') === $value;
    }
}