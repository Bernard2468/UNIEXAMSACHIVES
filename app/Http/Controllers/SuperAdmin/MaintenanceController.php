<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemMaintenanceLog;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    private SystemNotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new SystemNotificationService();
    }

    /**
     * Display a listing of maintenance logs
     */
    public function index(Request $request)
    {
        $query = SystemMaintenanceLog::with(['performer', 'approver']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('maintenance_type', $request->type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'scheduled_start');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $maintenanceLogs = $query->paginate(20);

        // Get upcoming and active maintenance
        $upcoming = SystemMaintenanceLog::upcoming(14)->get();
        $active = SystemMaintenanceLog::active()->get();

        // Statistics
        $stats = [
            'total' => SystemMaintenanceLog::count(),
            'planned' => SystemMaintenanceLog::planned()->count(),
            'in_progress' => SystemMaintenanceLog::inProgress()->count(),
            'completed' => SystemMaintenanceLog::completed()->count(),
            'upcoming' => $upcoming->count(),
        ];

        return view('super-admin.maintenance.index', compact(
            'maintenanceLogs',
            'upcoming',
            'active',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new maintenance log
     */
    public function create()
    {
        $superAdmins = User::where('role', 'super_admin')->get();
        return view('super-admin.maintenance.create', compact('superAdmins'));
    }

    /**
     * Store a newly created maintenance log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|in:scheduled_maintenance,emergency_maintenance,system_update,security_patch,database_optimization,server_upgrade,backup_restore,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'technical_details' => 'nullable|string',
            'scheduled_start' => 'required|date|after:now',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'impact_level' => 'required|in:low,medium,high,critical',
            'affected_services' => 'nullable|array',
            'requires_downtime' => 'boolean',
            'display_banner' => 'boolean',
            'banner_display_from' => 'nullable|date',
            'banner_display_until' => 'nullable|date',
            'banner_message' => 'nullable|string',
            'team_members' => 'nullable|array',
            'rollback_available' => 'boolean',
            'rollback_procedure' => 'nullable|string',
        ]);

        $validated['performed_by'] = auth()->id();
        $validated['status'] = 'planned';
        $validated['requires_downtime'] = $request->has('requires_downtime');
        $validated['display_banner'] = $request->has('display_banner');
        $validated['rollback_available'] = $request->has('rollback_available');

        // Automatically calculate estimated_downtime_minutes from scheduled_start and scheduled_end
        if ($validated['requires_downtime']) {
            $start = \Carbon\Carbon::parse($validated['scheduled_start']);
            $end = \Carbon\Carbon::parse($validated['scheduled_end']);
            $validated['estimated_downtime_minutes'] = $start->diffInMinutes($end);
        }

        $maintenance = SystemMaintenanceLog::create($validated);

        // Send notification to users if requested
        if ($request->has('notify_users')) {
            $this->notificationService->sendMaintenanceScheduledNotification($maintenance);
        }

        return redirect()->route('super-admin.maintenance.show', $maintenance->id)
            ->with('success', 'Maintenance scheduled successfully.');
    }

    /**
     * Display the specified maintenance log
     */
    public function show(int $id)
    {
        $maintenance = SystemMaintenanceLog::with([
            'performer',
            'approver',
            'rollbackPerformer',
            'notifications'
        ])->findOrFail($id);

        return view('super-admin.maintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the specified maintenance log
     */
    public function edit(int $id)
    {
        $maintenance = SystemMaintenanceLog::findOrFail($id);
        $superAdmins = User::where('role', 'super_admin')->get();

        // Only allow editing of planned or notified maintenance
        if (!in_array($maintenance->status, ['planned', 'notified'])) {
            return back()->with('error', 'Only planned or notified maintenance can be edited.');
        }

        return view('super-admin.maintenance.edit', compact('maintenance', 'superAdmins'));
    }

    /**
     * Update the specified maintenance log
     */
    public function update(Request $request, int $id)
    {
        $maintenance = SystemMaintenanceLog::findOrFail($id);

        // Only allow editing of planned or notified maintenance
        if (!in_array($maintenance->status, ['planned', 'notified'])) {
            return back()->with('error', 'Only planned or notified maintenance can be edited.');
        }

        $validated = $request->validate([
            'maintenance_type' => 'required|in:scheduled_maintenance,emergency_maintenance,system_update,security_patch,database_optimization,server_upgrade,backup_restore,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'technical_details' => 'nullable|string',
            'scheduled_start' => 'required|date',
            'scheduled_end' => 'required|date|after:scheduled_start',
            'impact_level' => 'required|in:low,medium,high,critical',
            'affected_services' => 'nullable|array',
            'requires_downtime' => 'boolean',
            'display_banner' => 'boolean',
            'banner_display_from' => 'nullable|date',
            'banner_display_until' => 'nullable|date',
            'banner_message' => 'nullable|string',
            'team_members' => 'nullable|array',
            'rollback_available' => 'boolean',
            'rollback_procedure' => 'nullable|string',
        ]);

        $validated['requires_downtime'] = $request->has('requires_downtime');
        $validated['display_banner'] = $request->has('display_banner');
        $validated['rollback_available'] = $request->has('rollback_available');

        // Automatically calculate estimated_downtime_minutes from scheduled_start and scheduled_end
        if ($validated['requires_downtime']) {
            $start = \Carbon\Carbon::parse($validated['scheduled_start']);
            $end = \Carbon\Carbon::parse($validated['scheduled_end']);
            $validated['estimated_downtime_minutes'] = $start->diffInMinutes($end);
        } else {
            // Clear estimated_downtime_minutes if requires_downtime is false
            $validated['estimated_downtime_minutes'] = null;
        }

        $maintenance->update($validated);

        return redirect()->route('super-admin.maintenance.show', $maintenance->id)
            ->with('success', 'Maintenance updated successfully.');
    }

    /**
     * Start maintenance
     */
    public function start(int $id)
    {
        $maintenance = SystemMaintenanceLog::findOrFail($id);

        if (!in_array($maintenance->status, ['planned', 'notified'])) {
            return back()->with('error', 'Only planned or notified maintenance can be started.');
        }

        $maintenance->start();

        // Send notification
        $this->notificationService->sendMaintenanceStartedNotification($maintenance);

        return back()->with('success', 'Maintenance started successfully.');
    }

    /**
     * Complete maintenance
     */
    public function complete(Request $request, int $id)
    {
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
            'issues_encountered' => 'nullable|array',
        ]);

        $maintenance = SystemMaintenanceLog::findOrFail($id);

        if ($maintenance->status !== 'in_progress') {
            return back()->with('error', 'Only in-progress maintenance can be completed.');
        }

        $maintenance->complete(
            $validated['completion_notes'] ?? null,
            $validated['issues_encountered'] ?? []
        );

        // Send notification
        $this->notificationService->sendMaintenanceCompletedNotification($maintenance);

        return back()->with('success', 'Maintenance completed successfully.');
    }

    /**
     * Cancel maintenance
     */
    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $maintenance = SystemMaintenanceLog::findOrFail($id);

        if (!in_array($maintenance->status, ['planned', 'notified'])) {
            return back()->with('error', 'Only planned or notified maintenance can be cancelled.');
        }

        $maintenance->cancel($validated['reason']);

        return back()->with('success', 'Maintenance cancelled successfully.');
    }

    /**
     * Rollback maintenance
     */
    public function rollback(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $maintenance = SystemMaintenanceLog::findOrFail($id);

        if (!$maintenance->rollback_available) {
            return back()->with('error', 'This maintenance does not support rollback.');
        }

        if ($maintenance->status !== 'completed') {
            return back()->with('error', 'Only completed maintenance can be rolled back.');
        }

        $maintenance->rollback(auth()->id(), $validated['reason']);

        return back()->with('success', 'Maintenance rolled back successfully.');
    }

    /**
     * Approve emergency maintenance
     */
    public function approve(int $id)
    {
        $maintenance = SystemMaintenanceLog::findOrFail($id);

        if ($maintenance->maintenance_type !== 'emergency_maintenance') {
            return back()->with('error', 'Only emergency maintenance requires approval.');
        }

        if ($maintenance->approved_at) {
            return back()->with('warning', 'This maintenance has already been approved.');
        }

        $maintenance->approve(auth()->id());

        return back()->with('success', 'Emergency maintenance approved successfully.');
    }

    /**
     * Notify users about scheduled maintenance
     */
    public function notifyUsers(int $id)
    {
        $maintenance = SystemMaintenanceLog::findOrFail($id);

        if ($maintenance->notification_sent_to_users) {
            return back()->with('warning', 'Users have already been notified about this maintenance.');
        }

        $this->notificationService->sendMaintenanceScheduledNotification($maintenance);

        return back()->with('success', 'Users notified successfully.');
    }

    /**
     * Delete a maintenance log
     */
    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $maintenance = SystemMaintenanceLog::findOrFail($id);

        // Prevent deletion of in-progress maintenance
        if ($maintenance->status === 'in_progress') {
            return back()->with('error', 'Cannot delete in-progress maintenance. Please complete or cancel it first.');
        }

        $title = $maintenance->title;
        $maintenance->delete();

        return redirect()->route('super-admin.maintenance.index')
            ->with('success', "Maintenance '{$title}' deleted successfully.");
    }
}

