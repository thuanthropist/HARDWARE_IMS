<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BackupSettingsRequest;
use App\Http\Requests\GeneralSettingsRequest;
use App\Http\Requests\NotificationSettingsRequest;
use App\Http\Requests\NumberingSettingsRequest;
use App\Http\Requests\TaxSettingsRequest;
use App\Http\Requests\WhatsAppSettingsRequest;
use App\Models\AuditLog;
use App\Models\CalculatorSubmission;
use App\Models\CalculatorType;
use App\Models\Department;
use App\Models\DepartmentAttributeSchema;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\StockTransfer;
use App\Models\User;
use App\Services\BackupService;
use App\Services\DocumentNumberGenerator;
use App\Services\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Mrrh\LicenseClient\LicenseManager;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    /**
     * @var array<string, array{0: class-string<\Illuminate\Database\Eloquent\Model>, 1: string, 2: string}>
     */
    private const NUMBERING_TYPES = [
        'po' => [PurchaseOrder::class, 'po_number', 'Purchase Order'],
        'order' => [Order::class, 'order_number', 'Sales Order / Invoice'],
        'quote' => [Quote::class, 'quote_number', 'Quote'],
        'transfer' => [StockTransfer::class, 'transfer_number', 'Stock Transfer'],
    ];

    /**
     * @var array<string, array{0: string, 1: array<int, string>}>
     */
    private const NOTIFICATION_TYPES = [
        'pending_order' => ['New Pending Order', ['Admin', 'Manager', 'Warehouse Staff']],
        'pending_quote' => ['New Pending Quote Request', ['Admin', 'Manager']],
        'low_stock' => ['Low Stock Alert', ['Admin', 'Manager']],
        'batch_expiry' => ['Upcoming Batch Expiry', ['Admin', 'Manager']],
        'quote_expiring' => ['Quote Expiring Soon', ['Admin', 'Manager']],
    ];

    public function index(SettingsManager $settings, DocumentNumberGenerator $numbers, BackupService $backups, LicenseManager $license): View
    {
        $numbering = [];

        foreach (self::NUMBERING_TYPES as $type => [$modelClass, $column, $label]) {
            $numbering[$type] = [
                'label' => $label,
                'pattern' => $settings->get("numbering.{$type}_pattern", DocumentNumberGenerator::defaultPattern($type)),
                'start' => $settings->get("numbering.{$type}_start", 1),
                'preview' => $numbers->preview($modelClass, $column, $type),
            ];
        }

        $notifications = [];

        foreach (self::NOTIFICATION_TYPES as $type => [$label, $defaultRoles]) {
            $notifications[$type] = [
                'label' => $label,
                'enabled' => $settings->get("notifications.{$type}_enabled", true),
                'roles' => $settings->get("notifications.{$type}_roles", $defaultRoles),
            ];
        }

        return view('settings.index', [
            'business' => $settings->group('business'),
            'dateFormats' => GeneralSettingsRequest::dateFormats(),
            'timezones' => \DateTimeZone::listIdentifiers(),
            'numbering' => $numbering,
            'tax' => [
                'vat_rate_percent' => round($settings->get('tax.vat_rate', 0.18) * 100, 2),
                'currency_code' => $settings->get('tax.currency_code', 'TZS'),
                'currency_symbol' => $settings->get('tax.currency_symbol', 'TZS'),
                'reorder_multiplier' => $settings->get('tax.reorder_multiplier', 2.0),
            ],
            'notifications' => $notifications,
            'allRoles' => Role::query()->orderBy('name')->pluck('name'),
            'smsDriver' => $settings->get('notifications.sms_driver', 'none'),
            'smsDrivers' => NotificationSettingsRequest::SMS_DRIVERS,
            'mailInfo' => [
                'driver' => config('mail.default'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'departmentsSummary' => [
                'departmentsCount' => Department::count(),
                'activeDepartmentsCount' => Department::active()->count(),
                'attributeFieldsCount' => DepartmentAttributeSchema::count(),
            ],
            'usersSummary' => [
                'usersCount' => User::count(),
                'roles' => Role::query()->orderBy('name')->get()->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissionCount' => $role->permissions->count(),
                    'userCount' => User::role($role->name)->count(),
                ]),
            ],
            'whatsapp' => $settings->group('whatsapp') + [
                'number' => $settings->get('whatsapp.number', config('services.whatsapp.number')),
                'default_message' => $settings->get('whatsapp.default_message', "Hi! I'd like some help with a project."),
            ],
            'backups' => $backups->list(),
            'backupFrequency' => $settings->get('backup.frequency', 'none'),
            'mysqldumpPath' => config('backup.mysqldump_path'),
            'licenseSummary' => [
                'status' => $license->status(),
                'isInGracePeriod' => $license->isInGracePeriod(),
                'expiresAt' => $license->expiresAt(),
                'daysUntilExpiry' => $license->daysUntilExpiry(),
            ],
            'auditLogSummary' => [
                'totalCount' => AuditLog::count(),
                'todayCount' => AuditLog::whereDate('created_at', today())->count(),
                'latest' => AuditLog::with('user')->latest()->first(),
            ],
            'planningToolsSummary' => [
                'calculatorCount' => CalculatorType::count(),
                'activeCalculatorCount' => CalculatorType::active()->count(),
                'submissionCount' => CalculatorSubmission::count(),
            ],
        ]);
    }

    public function updateGeneral(GeneralSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $oldPath = $settings->get('business.logo_path');

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $settings->set('business.logo_path', $request->file('logo')->store('settings', 'public'), 'business', 'string');
        }

        $settings->set('business.name', $data['name'], 'business', 'string');
        $settings->set('business.address', $data['address'] ?? null, 'business', 'string');
        $settings->set('business.phone', $data['phone'] ?? null, 'business', 'string');
        $settings->set('business.email', $data['email'] ?? null, 'business', 'string');
        $settings->set('business.timezone', $data['timezone'], 'business', 'string');
        $settings->set('business.date_format', $data['date_format'], 'business', 'string');

        return redirect()->route('settings.index', ['tab' => 'general'])->with('success', 'Business info updated.');
    }

    public function updateNumbering(NumberingSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $data = $request->validated();

        foreach (NumberingSettingsRequest::TYPES as $type) {
            $settings->set("numbering.{$type}_pattern", $data["{$type}_pattern"], 'numbering', 'string');
            $settings->set("numbering.{$type}_start", $data["{$type}_start"] ?? 1, 'numbering', 'integer');
        }

        return redirect()->route('settings.index', ['tab' => 'numbering'])->with('success', 'Numbering formats updated.');
    }

    public function updateTax(TaxSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $data = $request->validated();

        $settings->set('tax.vat_rate', ((float) $data['vat_rate']) / 100, 'tax', 'float');
        $settings->set('tax.currency_code', $data['currency_code'], 'tax', 'string');
        $settings->set('tax.currency_symbol', $data['currency_symbol'], 'tax', 'string');
        $settings->set('tax.reorder_multiplier', (float) $data['reorder_multiplier'], 'tax', 'float');

        return redirect()->route('settings.index', ['tab' => 'tax'])->with('success', 'Tax & currency settings updated.');
    }

    public function updateNotifications(NotificationSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $data = $request->validated();

        foreach (NotificationSettingsRequest::TYPES as $type) {
            $settings->set("notifications.{$type}_enabled", ! empty($data["{$type}_enabled"]), 'notifications', 'boolean');
            $settings->set("notifications.{$type}_roles", $data["{$type}_roles"], 'notifications', 'json');
        }

        $settings->set('notifications.sms_driver', $data['sms_driver'], 'notifications', 'string');

        return redirect()->route('settings.index', ['tab' => 'notifications'])->with('success', 'Notification settings updated.');
    }

    public function updateWhatsApp(WhatsAppSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $data = $request->validated();

        $settings->set('whatsapp.number', $data['number'], 'whatsapp', 'string');
        $settings->set('whatsapp.default_message', $data['default_message'], 'whatsapp', 'string');
        $settings->set('whatsapp.contact_address', $data['contact_address'] ?? null, 'whatsapp', 'string');
        $settings->set('whatsapp.contact_phone', $data['contact_phone'] ?? null, 'whatsapp', 'string');
        $settings->set('whatsapp.contact_email', $data['contact_email'] ?? null, 'whatsapp', 'string');

        return redirect()->route('settings.index', ['tab' => 'whatsapp'])->with('success', 'WhatsApp & contact settings updated.');
    }

    public function runBackup(BackupService $backups): RedirectResponse
    {
        $result = $backups->create();

        return redirect()->route('settings.index', ['tab' => 'backup'])
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function downloadBackup(string $filename): StreamedResponse
    {
        abort_unless(preg_match('/^backup-\d{4}-\d{2}-\d{2}_\d{6}\.sql\.gz$/', $filename) === 1, 404);
        abort_unless(Storage::disk('local')->exists("backups/{$filename}"), 404);

        return Storage::disk('local')->download("backups/{$filename}");
    }

    public function updateBackupFrequency(BackupSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $settings->set('backup.frequency', $request->validated('frequency'), 'backup', 'string');

        return redirect()->route('settings.index', ['tab' => 'backup'])->with('success', 'Backup schedule updated.');
    }
}
