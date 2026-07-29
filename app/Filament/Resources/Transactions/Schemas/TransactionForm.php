<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('date')
                    ->required(),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                TextInput::make('pay_total')
                    ->required()
                    ->numeric(),
            ]);
    }
}
