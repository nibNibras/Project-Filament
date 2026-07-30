<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

use App\Models\Item;

class TransactionForm
{
    public static function updateTotals( $get, $set): void
    {
        $details = $get('detail') ?? [];
        
        $total = 0;
        foreach ($details as $detail) {
            $total += (float) ($detail['subtotal'] ?? 0);
        }
    
        $set('Total', $total);

        $payTotal = (float) $get('Pay_Total');
        $change = $payTotal - $total;
        
        $set('change', $change);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(Auth::id()),
                Hidden::make('date')
                    ->default(now())
                    ->required(),
                Section::make('Payment')
                        ->schema([
                            TextInput::make('Pay_Total')
                                ->prefix('Rp.')
                                ->numeric()
                                ->inlineLabel()
                                ->live(onBlur: true) // Update ketika user klik di luar input (responsif)
                                ->afterStateUpdated(function ( $get, $set) {
                                    // Panggil fungsi hitung kembalian
                                    self::updateTotals($get, $set);
                                }),
                            TextInput::make('change')
                                ->prefix('Rp.')
                                ->numeric()
                                ->inlineLabel()
                                ->readOnly()
                                ->dehydrated(false),
                        ]),
                Section::make('Cart')
                        ->schema([
                            Repeater::make('detail')->hiddenLabel()
                                ->relationship()
                                ->schema([
                                    Select::make('item_id')
                                        ->options(Item::all()->pluck('name', 'id'))
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function($state, $set){
                                            $item = Item::find($state);
                                            if($item){
                                                $set('subtotal', $item->price);
                                            }
                                        }),
                                    TextInput::make('qty')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->reactive()
                                        ->required()
                                        ->afterStateUpdated(function($state, $set, $get){
                                            $item = Item::find($get('item_id'));                                    
                                            $set('subtotal', $item->price * $state);                                            
                                        }),
                                    TextInput::make('subtotal')
                                        ->prefix('Rp.')
                                        ->numeric()
                                        ->readOnly(),
                                ])->columns(3)->addActionLabel('Add Item')->live(),

                                TextInput::make('Total')
                                    ->numeric()
                                    ->inlineLabel()
                                    ->readOnly()
                                    ->placeholder(function($set, $get){
                                        $total = array_sum(array_column($get('detail') ?? [], 'subtotal'));
                                        return $total;
                                    }),
                        ]),
            ]);
    }
}
