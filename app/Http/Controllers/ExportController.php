<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $month = $request->query('month'); // e.g., "2026-03"

        if ($month) {
            $date = Carbon::parse($month . '-01');
            $start = $date->clone()->startOfMonth();
            $end = $date->clone()->endOfMonth();
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        $incomes = Income::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();

        $expenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'asc')
            ->get();

        $filename = 'finance-export-' . $start->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($incomes, $expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Type', 'Date', 'Amount', 'Category', 'Description']);

            foreach ($incomes as $income) {
                $date = $income->date instanceof Carbon ? $income->date : Carbon::parse($income->date);
                fputcsv($handle, [
                    'Income',
                    $date->format('Y-m-d'),
                    $income->amount,
                    $income->category ?? '',
                    $income->description ?? '',
                ]);
            }

            foreach ($expenses as $expense) {
                $date = $expense->date instanceof Carbon ? $expense->date : Carbon::parse($expense->date);
                fputcsv($handle, [
                    'Expense',
                    $date->format('Y-m-d'),
                    $expense->amount,
                    $expense->category ?? '',
                    $expense->description ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
