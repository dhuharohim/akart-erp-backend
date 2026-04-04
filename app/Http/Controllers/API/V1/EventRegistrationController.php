<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventRegistrationResource;
use App\Models\AttendanceRecord;
use App\Models\EventRegistration;
use App\Models\EventSeries;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventRegistrationController extends Controller
{
    public function index(Request $request, EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        $query = $series->registrations()->with(['category', 'priceSeries'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('payment_status')) {
            $query->where('payment_status', $status);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('event_category_id', $categoryId);
        }

        return EventRegistrationResource::collection(
            $query->paginate($request->integer('per_page', 15))
        );
    }

    public function show(EventSeries $series, EventRegistration $registration)
    {
        Gate::authorize('view', $series->event);
        abort_if($registration->event_series_id !== $series->id, 404);

        return new EventRegistrationResource($registration->load(['category', 'priceSeries']));
    }

    public function checkIn(Request $request, EventSeries $series)
    {
        Gate::authorize('update', $series->event);

        $data = $request->validate([
            'registration_number' => 'required|string',
            'date' => 'required|date',
        ]);

        $registration = EventRegistration::where('event_series_id', $series->id)
            ->where('registration_number', $data['registration_number'])
            ->with(['category', 'priceSeries'])
            ->first();

        abort_if(! $registration, 404, 'Registration not found for this series.');

        $checkDate = $data['date'];

        $alreadyToday = AttendanceRecord::where('attendable_type', 'event_registrations')
            ->where('attendable_id', $registration->id)
            ->where('date', $checkDate)
            ->exists();

        if ($alreadyToday) {
            return response()->json([
                'data' => new EventRegistrationResource($registration),
                'message' => 'Already checked in for this date.',
                'already_checked_in' => true,
            ]);
        }

        AttendanceRecord::create([
            'event_series_id' => $series->id,
            'attendable_type' => 'event_registrations',
            'attendable_id' => $registration->id,
            'date' => $checkDate,
            'checked_in_at' => now(),
            'scanned_by_type' => 'users',
            'scanned_by_id' => $request->user()?->id,
        ]);

        // Keep legacy field updated
        $registration->update([
            'present_status' => 'present',
            'checked_in_at' => now(),
            'scanned_by_type' => 'users',
            'scanned_by_id' => $request->user()?->id,
        ]);

        return response()->json([
            'data' => new EventRegistrationResource($registration->fresh()->load(['category', 'priceSeries', 'attendanceRecords'])),
            'message' => 'Check-in successful.',
            'already_checked_in' => false,
        ]);
    }

    public function lookup(EventSeries $series, string $registrationNumber)
    {
        Gate::authorize('view', $series->event);

        $registration = EventRegistration::where('event_series_id', $series->id)
            ->where('registration_number', $registrationNumber)
            ->with(['category', 'priceSeries'])
            ->first();

        abort_if(! $registration, 404, 'Registration not found.');

        return new EventRegistrationResource($registration);
    }

    public function attendanceHistory(EventSeries $series, string $type, int $id)
    {
        Gate::authorize('view', $series->event);

        $records = AttendanceRecord::where('event_series_id', $series->id)
            ->where('attendable_type', $type)
            ->where('attendable_id', $id)
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => [
                'date' => $r->date->format('Y-m-d'),
                'checked_in_at' => $r->checked_in_at->toISOString(),
                'scanned_by_type' => $r->scanned_by_type,
                'scanned_by_id' => $r->scanned_by_id,
            ]);

        return response()->json(['data' => $records]);
    }

    public function destroy(EventSeries $series, EventRegistration $registration)
    {
        Gate::authorize('delete', $series->event);
        abort_if($registration->event_series_id !== $series->id, 404);

        $registration->delete();

        return response()->json(['message' => 'Registration deleted.']);
    }

    public function guestBookPdf(EventSeries $series)
    {
        Gate::authorize('view', $series->event);

        $registrations = $series->registrations()
            ->with(['category', 'attendanceRecords'])
            ->orderBy('event_category_id')
            ->orderBy('first_name')
            ->get();

        // Build date columns from series date range
        $eventDates = [];
        if ($series->start_date && $series->end_date) {
            $current = \Illuminate\Support\Carbon::parse($series->start_date)->startOfDay();
            $end = \Illuminate\Support\Carbon::parse($series->end_date)->startOfDay();
            while ($current->lte($end)) {
                $eventDates[] = $current->copy();
                $current->addDay();
            }
        }

        $pdf = Pdf::loadView('pdf.guest-book', [
            'event' => $series->event,
            'series' => $series,
            'registrations' => $registrations,
            'eventDates' => $eventDates,
        ])->setPaper(count($eventDates) > 3 ? 'a4' : 'a4', count($eventDates) > 3 ? 'landscape' : 'portrait');

        $filename = 'guest-book-'.($series->series_number ?? $series->id).'.pdf';

        return $pdf->download($filename);
    }
}
