<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Penjualan;

class PenjualanChartWidget extends ChartWidget
{
    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $penjualan = Penjualan::selectRaw('DATE(created_at) as date, SUM(total_harga) as total')
            ->groupBy('date')
            ->get();

        return [
            'labels' => $penjualan->pluck('date'),
            'datasets' => [
                [
                    'label' => 'Penjualan Obat',
                    'data' => $penjualan->pluck('total'),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                ],
            ],
        ];
    }
}