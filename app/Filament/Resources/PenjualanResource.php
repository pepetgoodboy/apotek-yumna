<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Obat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getModelLabel(): string
    {
        return 'Penjualan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Penjualan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('obat_id')
                    ->label('Obat')
                    ->options(Obat::all()->pluck('nama', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => 
                        $set('harga_satuan', Obat::find($state)?->harga ?? 0)
                    ),
                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $get, callable $set) => 
                        $set('total_harga', $state * $get('harga_satuan'))
                    ),
                Forms\Components\TextInput::make('harga_satuan')
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('total_harga')
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp')
                    ->dehydrated(true),
                Forms\Components\DatePicker::make('tanggal_penjualan')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('obat.nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_penjualan')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal_penjualan')
                ->form([
                    Forms\Components\DatePicker::make('tanggal')
                        ->default(now()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['tanggal'],
                            fn (Builder $query, $date): Builder => $query->whereDate('tanggal_penjualan', $date),
                        );
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('downloadPDF')
    ->label('Unduh Laporan')
    ->icon('heroicon-o-document-arrow-down')
    ->form([
        Forms\Components\DatePicker::make('tanggal_awal')
            ->label('Dari Tanggal')
            ->required()
            ->default(now()),
        Forms\Components\DatePicker::make('tanggal_akhir')
            ->label('Sampai Tanggal')
            ->required()
            ->default(now()),
    ])
    ->action(function (array $data) {
        $penjualans = Penjualan::with('obat')
            ->whereBetween('tanggal_penjualan', [
                $data['tanggal_awal'],
                $data['tanggal_akhir'],
            ])
            ->get();
        
        $total = $penjualans->sum('total_harga');

        $pdf = Pdf::loadView('pdf.laporan-harian', [
            'penjualans' => $penjualans,
            'tanggal' => date('d/m/Y', strtotime($data['tanggal_awal'])) . 
                        ' - ' . 
                        date('d/m/Y', strtotime($data['tanggal_akhir'])),
            'total' => $total,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-penjualan-' . now()->format('Y-m-d') . '.pdf');
        })
        ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }    
}