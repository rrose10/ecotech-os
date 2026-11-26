<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FundTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = FundTransfer::with(['fromAccount', 'toAccount', 'creator'])
            ->when($request->account, fn ($q, $id) => $q->where(
                fn ($w) => $w->where('from_account_id', $id)->orWhere('to_account_id', $id)
            ))
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $accounts = BankAccount::orderBy('account_name')->get();

        return view('fund-transfers.index', compact('transfers', 'accounts'));
    }

    public function create()
    {
        return view('fund-transfers.create', ['accounts' => $this->accounts()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($data) {
                $this->move($data['from_account_id'], $data['to_account_id'], $data['amount']);

                $transfer = FundTransfer::create($data + ['created_by' => auth()->id()]);

                ActivityLogger::log('Transfer', 'Created', $this->describe($transfer), null, $transfer->toArray());
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('fund-transfers.index')->with('success', 'Transfer saved.');
    }

    public function edit(FundTransfer $fundTransfer)
    {
        return view('fund-transfers.edit', [
            'transfer' => $fundTransfer,
            'accounts' => $this->accounts(),
        ]);
    }

    public function update(Request $request, FundTransfer $fundTransfer)
    {
        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($data, $fundTransfer) {
                $old = $fundTransfer->toArray();

                // Undo the old movement, then apply the new one.
                $this->move($fundTransfer->to_account_id, $fundTransfer->from_account_id, $fundTransfer->amount);
                $this->move($data['from_account_id'], $data['to_account_id'], $data['amount']);

                $fundTransfer->update($data);

                ActivityLogger::log('Transfer', 'Updated', $this->describe($fundTransfer), $old, $fundTransfer->fresh()->toArray());
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('fund-transfers.index')->with('success', 'Transfer updated.');
    }

    public function destroy(FundTransfer $fundTransfer)
    {
        try {
            DB::transaction(function () use ($fundTransfer) {
                // Put the money back where it came from.
                $this->move($fundTransfer->to_account_id, $fundTransfer->from_account_id, $fundTransfer->amount);

                ActivityLogger::log('Transfer', 'Deleted', $this->describe($fundTransfer), $fundTransfer->toArray(), null);

                $fundTransfer->delete();
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('fund-transfers.index')->with('success', 'Transfer deleted and the balances were restored.');
    }

    private function accounts()
    {
        return BankAccount::where('is_active', true)->orderBy('account_name')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id'   => 'required|exists:bank_accounts,id|different:from_account_id',
            'amount'          => 'required|numeric|min:0.01',
            'transfer_date'   => 'required|date',
            'remarks'         => 'nullable|string|max:255',
        ], [
            'to_account_id.different' => 'Choose two different accounts.',
        ]);
    }

    /** Take $amount out of one account and put it into the other (rows are locked). */
    private function move(int $fromId, int $toId, $amount): void
    {
        $accounts = BankAccount::whereIn('id', [$fromId, $toId])->orderBy('id')->lockForUpdate()->get()->keyBy('id');

        $from = $accounts[$fromId];

        if ((float) $from->current_balance < (float) $amount) {
            throw new \RuntimeException('Not enough balance in "' . $from->account_name . '" for this transfer.');
        }

        $from->decrement('current_balance', $amount);
        $accounts[$toId]->increment('current_balance', $amount);
    }

    private function describe(FundTransfer $t): string
    {
        $t->loadMissing(['fromAccount', 'toAccount']);

        return sprintf(
            'Transfer %s: %s -> %s',
            number_format((float) $t->amount, 2),
            $t->fromAccount->account_name ?? '#' . $t->from_account_id,
            $t->toAccount->account_name ?? '#' . $t->to_account_id
        );
    }
}
