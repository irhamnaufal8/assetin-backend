<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $status = $request->query('status');

        $query = Loan::with(['inventory', 'user']);

        if (!is_null($userId)) {
            $query->where('user_id', $userId);
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        $loans = $query->get();

        return response()->json($loans);
    }

    public function store(Request $request)
    {
        $loan = Loan::create($request->all());
        return response()->json($loan, 201);
    }

    public function show(Loan $loan)
    {
        return response()->json($loan);
    }

    public function update(Request $request, Loan $loan)
    {
        $loan->update($request->all());
        return response()->json($loan);
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return response()->json(null, 204);
    }

    public function getUserLoans(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->query('status');

        $query = Loan::where('user_id', $userId);

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        $loans = $query->get();

        return response()->json($loans);
    }

    public function approveLoan(Request $request, $loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $inventory = Inventory::findOrFail($loan->inventory_id);

        if ($inventory->quantity_available < $request->quantity) {
            return response()->json(['message' => 'Not enough inventory available'], 400);
        }

        $loan->status = 'READY';
        $loan->pickup_location = $request->pickup_location;
        $loan->due_date = $request->due_date;
        $loan->save();

        $inventory->quantity_available -= $request->quantity;
        $inventory->save();

        return response()->json($loan, 200);
    }

    public function startLoan(Request $request, $loanId)
    {
        $loan = Loan::findOrFail($loanId);

        if ($loan->status !== 'READY') {
            return response()->json(['message' => 'Loan is not ready for pickup'], 400);
        }

        $loan->status = 'ON-GOING';
        $loan->due_date = Carbon::now()->addDay();
        $loan->save();

        return response()->json($loan, 200);
    }

    public function finishLoan(Request $request, $loanId)
    {
        $loan = Loan::findOrFail($loanId);

        if ($loan->status !== 'ON-GOING') {
            return response()->json(['message' => 'Loan is not ongoing'], 400);
        }

        $inventory = Inventory::findOrFail($loan->inventory_id);
        $inventory->quantity_available += $loan->quantity;
        $inventory->save();

        $loan->status = 'DONE';
        $loan->save();

        return response()->json($loan, 200);
    }
}