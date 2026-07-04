<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    // Assume spatie/laravel-activitylog model
    protected static ?string $model = \App\Models\User::class; // Fallback placeholder

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System Command Center';
    protected static ?string $navigationLabel = 'Audit Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')->label('Admin User'),
                Tables\Columns\TextColumn::make('description')->label('Action Taken')->searchable(),
                Tables\Columns\TextColumn::make('subject_type')->label('Resource Modified'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([]) // Logs are read-only
            ->bulkActions([]); 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
