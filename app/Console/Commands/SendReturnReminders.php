<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendReturnReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-return-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to buyers about their rental return dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting return date reminders check...');

        // 1. Check for orders due tomorrow
        $tomorrow = Carbon::tomorrow();
        $ordersDueTomorrow = Order::where('status', 'Delivered')
            ->whereDate('rental_to', $tomorrow)
            ->with('buyer') // Eager load buyer
            ->get();

        foreach ($ordersDueTomorrow as $order) {
            $this->sendNotification(
                $order,
                'Upcoming Return Due Date',
                "Reminder: Your rental order #{$order->id} is due for return tomorrow ({$tomorrow->toFormattedDateString()}). Please ensure it is ready for pickup or return."
            );
        }

        // 2. Check for orders due today
        $today = Carbon::today();
        $ordersDueToday = Order::where('status', 'Delivered')
            ->whereDate('rental_to', $today)
            ->with('buyer')
            ->get();

        foreach ($ordersDueToday as $order) {
            $this->sendNotification(
                $order,
                'Return Due Today',
                "Urgent: Your rental order #{$order->id} is due for return today. Please ensure it is returned to avoid late fees."
            );
        }
        
        // 3. Optional: Check for overdue items (e.g. 1 day overdue) effectively reminding them they missed it
        $yesterday = Carbon::yesterday();
         $ordersOverdue = Order::where('status', 'Delivered')
            ->whereDate('rental_to', $yesterday)
            ->with('buyer')
            ->get();

         foreach ($ordersOverdue as $order) {
            $this->sendNotification(
                $order,
                'Rental Overdue',
                "Action Required: Your rental order #{$order->id} was due yesterday. Please return it immediately."
            );
        }


        $this->info('Reminders sent successfully.');
    }

    private function sendNotification($order, $title, $message)
    {
        if (!$order->buyer) {
            return;
        }

        try {
            Notification::create([
                'user_id' => $order->buyer_id,
                'title' => $title,
                'message' => $message,
                'type' => 'order_reminder',
                'icon' => 'clock', // Assuming the frontend handles icons, 'clock' or 'calendar' is appropriate
                'data' => [
                    'order_id' => $order->id,
                    'rental_to' => $order->rental_to->format('Y-m-d')
                ],
                'read' => false
            ]);
            
            $this->info("Sent notification to User ID {$order->buyer_id} for Order #{$order->id}");

        } catch (\Exception $e) {
            Log::error("Failed to send return reminder for Order #{$order->id}: " . $e->getMessage());
            $this->error("Failed to send notification for Order #{$order->id}");
        }
    }
}
