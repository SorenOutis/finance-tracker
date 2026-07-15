<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\FinancialGoal;
use App\Models\RecurringTransaction;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $notifications = [];

        // 1. Due bills (next 7 days)
        $upcomingBills = RecurringTransaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->whereBetween('next_due_date', [$today, $today->copy()->addDays(7)])
            ->get();

        foreach ($upcomingBills as $bill) {
            $daysUntil = $today->diffInDays($bill->next_due_date, false);
            $notifications[] = [
                'id' => 'bill-'.$bill->id,
                'type' => 'bill_due',
                'title' => $daysUntil <= 0 ? 'Bill Due Today' : 'Bill Due Soon',
                'message' => $bill->name.' — '.number_format($bill->amount, 2),
                'severity' => $daysUntil <= 0 ? 'danger' : ($daysUntil <= 2 ? 'warning' : 'info'),
                'action_url' => '/bills',
                'created_at' => $bill->next_due_date->toISOString(),
            ];
        }

        // 2. Budget alerts
        $budget = Budget::where('user_id', $user->id)->first();
        if ($budget && $budget->monthly_limit > 0) {
            $monthStart = $today->copy()->startOfMonth();
            $monthEnd = $today->copy()->endOfMonth();
            $spent = \App\Models\Expense::where('user_id', $user->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $usagePercent = ($spent / $budget->monthly_limit) * 100;
            if ($usagePercent >= 80) {
                $notifications[] = [
                    'id' => 'budget-'.$budget->id,
                    'type' => 'budget_alert',
                    'title' => 'Budget Alert',
                    'message' => 'You\'ve used '.round($usagePercent).'% of your monthly budget',
                    'severity' => $usagePercent >= 100 ? 'danger' : 'warning',
                    'action_url' => '/dashboard',
                    'created_at' => $today->toISOString(),
                ];
            }
        }

        // 3. Goal milestones
        $goals = FinancialGoal::where('user_id', $user->id)->get();
        foreach ($goals as $goal) {
            $progress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;
            if ($progress >= 100) {
                $notifications[] = [
                    'id' => 'goal-'.$goal->id.'-complete',
                    'type' => 'goal_complete',
                    'title' => 'Goal Complete!',
                    'message' => 'You reached your "'.$goal->name.'" goal!',
                    'severity' => 'success',
                    'action_url' => '/dashboard',
                    'created_at' => $today->toISOString(),
                ];
            } elseif ($progress >= 75) {
                $notifications[] = [
                    'id' => 'goal-'.$goal->id.'-milestone',
                    'type' => 'goal_milestone',
                    'title' => 'Goal Milestone',
                    'message' => '"'.$goal->name.'" is '.round($progress).'% complete!',
                    'severity' => 'info',
                    'action_url' => '/dashboard',
                    'created_at' => $today->toISOString(),
                ];
            }
        }

        // Sort by severity (danger first) then by date (newest first)
        $severityOrder = ['danger' => 0, 'warning' => 1, 'success' => 2, 'info' => 3];
        usort($notifications, function ($a, $b) use ($severityOrder) {
            $aOrder = $severityOrder[$a['severity']] ?? 99;
            $bOrder = $severityOrder[$b['severity']] ?? 99;
            if ($aOrder !== $bOrder) {
                return $aOrder - $bOrder;
            }

            return strcmp($b['created_at'], $a['created_at']);
        });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => count($notifications),
        ]);
    }
}
