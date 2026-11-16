<?php

namespace App\Http\Controllers;

use App\Models\PaymentReminder;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentReminderController extends Controller
{
    public function index()
    {
        $reminders = PaymentReminder::orderByDesc('is_active')->orderBy('due_date')->paginate(20);

        return view('payment-reminders.index', compact('reminders'));
    }

    public function create()
    {
        return view('payment-reminders.form', [
            'reminder' => new PaymentReminder(['repeat' => 'monthly', 'remind_days' => 3, 'is_active' => true, 'due_date' => today()]),
        ]);
    }

    public function store(Request $request)
    {
        $reminder = PaymentReminder::create($this->validated($request));

        ActivityLogger::log('Payment Reminder', 'Created', 'Created reminder: '.$reminder->title, null, $reminder->toArray());

        return redirect()->route('payment-reminders.index')->with('success', 'Reminder added.');
    }

    public function edit(PaymentReminder $paymentReminder)
    {
        return view('payment-reminders.form', ['reminder' => $paymentReminder]);
    }

    public function update(Request $request, PaymentReminder $paymentReminder)
    {
        $old = $paymentReminder->toArray();

        $paymentReminder->update($this->validated($request));

        ActivityLogger::log('Payment Reminder', 'Updated', 'Updated reminder: '.$paymentReminder->title, $old, $paymentReminder->fresh()->toArray());

        return redirect()->route('payment-reminders.index')->with('success', 'Reminder updated.');
    }

    public function destroy(PaymentReminder $paymentReminder)
    {
        ActivityLogger::log('Payment Reminder', 'Deleted', 'Deleted reminder: '.$paymentReminder->title, $paymentReminder->toArray(), null);

        $paymentReminder->delete();

        return redirect()->route('payment-reminders.index')->with('success', 'Reminder deleted.');
    }

    public function paid(PaymentReminder $paymentReminder)
    {
        if (! $paymentReminder->is_active) {
            return back()->withErrors(['error' => 'This reminder is already closed.']);
        }

        $paymentReminder->markPaid();

        ActivityLogger::log('Payment Reminder', 'Paid', 'Marked paid: '.$paymentReminder->title);

        return back()->with('success', $paymentReminder->is_active
            ? 'Marked as paid. Next due date: '.$paymentReminder->due_date->format('d M Y').'.'
            : 'Marked as paid and closed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'repeat' => ['required', Rule::in(array_keys(PaymentReminder::REPEATS))],
            'remind_days' => 'required|integer|min:0|max:60',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
