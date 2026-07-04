<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClickLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClickLogResource extends Resource
{
    // Assume a ClickLog model exists
    protected static ?string $model = \App\Models\User::class; // Fallback placeholder

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';
    protected static ?string $navigationGroup = 'Redirect Engine';
    protected static ?string $navigationLabel = 'Bot & Click Analytics';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')->searchable(),
                Tables\Columns\TextColumn::make('deal.title')->label('Target Deal'),
                Tables\Columns\TextColumn::make('user_agent')->limit(40),
                Tables\Columns\BadgeColumn::make('is_bot')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Bot Traffic' : 'Human'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClickLogs::route('/'),
        ];
    }
}
