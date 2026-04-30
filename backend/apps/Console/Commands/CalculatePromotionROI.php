<?php

namespace App\Console\Commands;

use App\Models\Promotion;
use Illuminate\Console\Command;

class CalculatePromotionROI extends Command
{
    protected $signature = 'promotions:calculate-roi {--promotion= : Calculate ROI for specific promotion ID}';
    protected $description = 'Calculate ROI for promotions based on actual usage data';

    public function handle()
    {
        $promotionId = $this->option('promotion');
        
        if ($promotionId) {
            $promotion = Promotion::find($promotionId);
            if (!$promotion) {
                $this->error("Promotion with ID {$promotionId} not found.");
                return 1;
            }
            $promotions = collect([$promotion]);
        } else {
            $promotions = Promotion::where('marketing_cost', '>', 0)->get();
        }

        if ($promotions->isEmpty()) {
            $this->info('No promotions with marketing cost found.');
            return 0;
        }

        $this->info("Calculating ROI for {$promotions->count()} promotion(s)...");
        
        $bar = $this->output->createProgressBar($promotions->count());
        $bar->start();

        foreach ($promotions as $promotion) {
            $promotion->calculateROI();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('ROI calculation completed!');

        // Show summary
        $this->table(
            ['Promotion', 'Revenue', 'Discounts', 'Marketing Cost', 'ROI'],
            $promotions->map(function ($promo) {
                return [
                    $promo->name,
                    '₱' . number_format($promo->total_revenue, 2),
                    '₱' . number_format($promo->total_discounts, 2),
                    '₱' . number_format($promo->marketing_cost, 2),
                    $promo->getFormattedROI()
                ];
            })
        );

        return 0;
    }
}