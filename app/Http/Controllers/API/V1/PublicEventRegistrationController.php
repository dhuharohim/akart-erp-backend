<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\EventRegistration;
use App\Models\EventSeries;
use App\Models\EventStaff;
use App\Services\EventRegistrationService;
use Illuminate\Http\Request;

class PublicEventRegistrationController extends Controller
{
    public function __construct(private EventRegistrationService $service) {}

    public function show(string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)
            ->with('event')
            ->firstOrFail();

        return response()->json(['data' => $this->service->getPublicSeriesData($series)]);
    }

    public function register(Request $request, string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)
            ->with('event')
            ->firstOrFail();

        $config = $series->registration_config ?? [];

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required_without:telephone|nullable|email|max:255',
            'telephone' => 'required_without:email|nullable|string|max:50',
            'event_category_id' => 'required|integer|exists:event_categories,id',
            'custom_fields' => 'nullable|array',
            'idempotency_key' => 'required|uuid',
            'turnstile_token' => config('services.turnstile.secret_key') ? 'required|string' : 'nullable|string',
        ];
        if (! empty($config['email_required'])) {
            $rules['email'] = 'required|email|max:255';
        }
        if (! empty($config['telephone_required'])) {
            $rules['telephone'] = 'required|string|max:50';
        }
        if (! empty($config['age_enabled'])) {
            $rules['age'] = 'nullable|integer|min:0|max:150';
        }
        if (! empty($config['address_enabled'])) {
            $rules['address'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules);

        // Verify Cloudflare Turnstile (skip if not configured)
        if (! empty($validated['turnstile_token'])) {
            abort_if(
                ! $this->service->verifyTurnstile($validated['turnstile_token']),
                422,
                'Captcha verification failed. Please try again.'
            );
        }

        $idempotencyKey = $validated['idempotency_key'];
        unset($validated['idempotency_key'], $validated['turnstile_token']);

        $result = $this->service->register($series, $validated, $idempotencyKey);

        // If registration was already processed (idempotency hit)
        if ($result['registration']) {
            return response()->json([
                'data' => [
                    'registration_number' => $result['registration']->registration_number,
                    'amount' => $result['registration']->amount,
                    'invoice_url' => $result['invoice_url'],
                    'ticket_url' => $result['ticket_url'] ?? null,
                    'queued' => false,
                ],
            ], 200);
        }

        // Registration is queued
        return response()->json([
            'data' => [
                'registration_number' => null,
                'amount' => null,
                'invoice_url' => null,
                'ticket_url' => null,
                'queued' => true,
                'message' => 'Your registration is being processed. You will receive a confirmation shortly.',
            ],
        ], 202);
    }

    public function ticket(string $publicId, string $registrationNumber)
    {
        $series = EventSeries::where('public_id', $publicId)
            ->with('event')
            ->firstOrFail();

        $registration = EventRegistration::where('registration_number', $registrationNumber)
            ->with('category')
            ->firstOrFail();

        return response()->json([
            'data' => $this->service->getPublicTicketData($series, $registration),
        ]);
    }

    public function status(string $publicId, string $idempotencyKey)
    {
        $series = EventSeries::where('public_id', $publicId)
            ->with('event')
            ->firstOrFail();

        $registration = EventRegistration::where('idempotency_key', $idempotencyKey)->first();

        if (! $registration) {
            return response()->json([
                'data' => ['status' => 'processing'],
            ]);
        }

        return response()->json([
            'data' => [
                'status' => 'completed',
                'registration_number' => $registration->registration_number,
                'amount' => $registration->amount,
                'invoice_url' => $registration->xendit_invoice_url,
                'ticket_url' => $this->buildTicketUrl($series, $registration),
                'payment_status' => $registration->payment_status,
            ],
        ]);
    }

    // --- Public Check-In (for assigned employees) ---

    public function checkInAuth(Request $request, string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)->with('event')->firstOrFail();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'passcode' => 'required|string|size:6',
        ]);

        abort_if($series->checkin_passcode !== strtoupper($validated['passcode']), 403, 'Invalid passcode.');

        $staff = EventStaff::where('event_series_id', $series->id)
            ->where('employee_number', strtoupper($validated['employee_number']))
            ->with('employee')
            ->first();

        abort_if(! $staff, 403, 'Employee not assigned to this event series.');
        abort_if($series->status !== 'running', 422, 'Check-in is only available when event is running.');

        // Build event date range
        $eventDates = [];
        if ($series->start_date && $series->end_date) {
            $current = \Illuminate\Support\Carbon::parse($series->start_date)->startOfDay();
            $end = \Illuminate\Support\Carbon::parse($series->end_date)->startOfDay();
            while ($current->lte($end)) {
                $eventDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        return response()->json([
            'data' => [
                'employee_name' => $staff->employee?->full_name ?? 'Unknown',
                'employee_number' => $staff->employee_number,
                'role' => $staff->role_in_event,
                'event_name' => $series->event?->name,
                'series_name' => $series->name,
                'series_status' => $series->status,
                'start_date' => $series->start_date?->format('Y-m-d'),
                'end_date' => $series->end_date?->format('Y-m-d'),
                'event_dates' => $eventDates,
            ],
        ]);
    }

    public function checkInLookup(Request $request, string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)->firstOrFail();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'passcode' => 'required|string|size:6',
            'registration_number' => 'required|string',
        ]);

        abort_if($series->checkin_passcode !== strtoupper($validated['passcode']), 403, 'Invalid passcode.');
        abort_if($series->status !== 'running', 422, 'Check-in is only available when event is running.');

        $staff = EventStaff::where('event_series_id', $series->id)
            ->where('employee_number', strtoupper($validated['employee_number']))
            ->exists();
        abort_if(! $staff, 403, 'Unauthorized.');

        $registration = EventRegistration::where('event_series_id', $series->id)
            ->where('registration_number', $validated['registration_number'])
            ->with(['category'])
            ->first();

        abort_if(! $registration, 404, 'Registration not found.');

        return response()->json([
            'data' => [
                'registration_number' => $registration->registration_number,
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'email' => $registration->email,
                'telephone' => $registration->telephone,
                'category' => $registration->category?->name,
                'amount' => $registration->amount,
                'payment_status' => $registration->payment_status,
                'present_status' => $registration->present_status,
                'checked_in_at' => $registration->checked_in_at,
            ],
        ]);
    }

    public function checkInConfirm(Request $request, string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)->firstOrFail();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'passcode' => 'required|string|size:6',
            'registration_number' => 'required|string',
            'date' => 'required|date',
        ]);

        abort_if($series->checkin_passcode !== strtoupper($validated['passcode']), 403, 'Invalid passcode.');
        abort_if($series->status !== 'running', 422, 'Check-in is only available when event is running.');

        $staff = EventStaff::where('event_series_id', $series->id)
            ->where('employee_number', strtoupper($validated['employee_number']))
            ->first();
        abort_if(! $staff, 403, 'Unauthorized.');

        $registration = EventRegistration::where('event_series_id', $series->id)
            ->where('registration_number', $validated['registration_number'])
            ->first();

        abort_if(! $registration, 404, 'Registration not found.');

        $checkDate = $validated['date'];

        $alreadyToday = AttendanceRecord::where('attendable_type', 'event_registrations')
            ->where('attendable_id', $registration->id)
            ->where('date', $checkDate)
            ->exists();

        if (! $alreadyToday) {
            AttendanceRecord::create([
                'event_series_id' => $series->id,
                'attendable_type' => 'event_registrations',
                'attendable_id' => $registration->id,
                'date' => $checkDate,
                'checked_in_at' => now(),
                'scanned_by_type' => 'employees',
                'scanned_by_id' => $staff->employee_id,
            ]);

            $registration->update([
                'present_status' => 'present',
                'checked_in_at' => now(),
                'scanned_by_type' => 'employees',
                'scanned_by_id' => $staff->employee_id,
            ]);
        }

        $attendanceDates = AttendanceRecord::where('attendable_type', 'event_registrations')
            ->where('attendable_id', $registration->id)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->values();

        return response()->json([
            'data' => [
                'registration_number' => $registration->registration_number,
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'present_status' => $registration->present_status,
                'checked_in_at' => $registration->checked_in_at,
                'attendance_dates' => $attendanceDates,
            ],
            'already_checked_in' => $alreadyToday,
            'message' => $alreadyToday ? 'Already checked in today.' : 'Check-in successful.',
        ]);
    }

    // --- Employee Self Check-In ---

    public function employeePresent(Request $request, string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)->firstOrFail();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'passcode' => 'required|string|size:6',
            'date' => 'required|date',
        ]);

        abort_if($series->checkin_passcode !== strtoupper($validated['passcode']), 403, 'Invalid passcode.');
        abort_if($series->status !== 'running', 422, 'Event is not currently running.');

        $staff = EventStaff::where('event_series_id', $series->id)
            ->where('employee_number', strtoupper($validated['employee_number']))
            ->with('employee')
            ->first();

        abort_if(! $staff, 404, 'Employee not found.');

        $checkDate = $validated['date'];

        $alreadyToday = AttendanceRecord::where('attendable_type', 'event_staff')
            ->where('attendable_id', $staff->id)
            ->where('date', $checkDate)
            ->exists();

        if (! $alreadyToday) {
            AttendanceRecord::create([
                'event_series_id' => $series->id,
                'attendable_type' => 'event_staff',
                'attendable_id' => $staff->id,
                'date' => $checkDate,
                'checked_in_at' => now(),
            ]);
            $staff->update(['attendance' => true]);
        }

        $attendanceDates = AttendanceRecord::where('attendable_type', 'event_staff')
            ->where('attendable_id', $staff->id)
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->values();

        return response()->json([
            'data' => [
                'employee_number' => $staff->employee_number,
                'employee_name' => $staff->employee?->full_name,
                'role' => $staff->role_in_event,
                'attendance' => true,
                'attendance_dates' => $attendanceDates,
            ],
            'already_present' => $alreadyToday,
            'message' => $alreadyToday ? 'Already marked present today.' : 'Attendance recorded.',
        ]);
    }

    public function employeeList(Request $request, string $publicId)
    {
        $series = EventSeries::where('public_id', $publicId)->firstOrFail();

        $validated = $request->validate([
            'employee_number' => 'required|string',
            'passcode' => 'required|string|size:6',
        ]);

        abort_if($series->checkin_passcode !== strtoupper($validated['passcode']), 403, 'Invalid passcode.');

        $staffCheck = EventStaff::where('event_series_id', $series->id)
            ->where('employee_number', strtoupper($validated['employee_number']))
            ->exists();
        abort_if(! $staffCheck, 403, 'Unauthorized.');

        $today = now()->toDateString();
        $staffList = EventStaff::where('event_series_id', $series->id)
            ->with(['employee', 'attendanceRecords'])
            ->get()
            ->map(fn ($s) => [
                'employee_number' => $s->employee_number,
                'employee_name' => $s->employee?->full_name,
                'role' => $s->role_in_event,
                'attendance' => (bool) $s->attendance,
                'present_today' => $s->attendanceRecords->contains('date', $today),
                'attendance_dates' => $s->attendanceRecords->sortBy('date')->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->values(),
            ]);

        return response()->json(['data' => $staffList]);
    }

    private function buildTicketUrl(EventSeries $series, EventRegistration $registration): string
    {
        $baseUrl = rtrim((string) env('PUBLIC_WEB_URL', config('app.url')), '/');

        return "{$baseUrl}/register/{$series->public_id}/ticket/{$registration->registration_number}";
    }
}
