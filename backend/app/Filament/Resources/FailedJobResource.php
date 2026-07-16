<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FailedJobResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FailedJobResource extends Resource
{
    protected static ?string $model = \App\Models\FailedJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'System Actions';
    protected static ?string $navigationLabel = 'Failed Jobs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')->disabled(),
                Forms\Components\TextInput::make('connection')->disabled(),
                Forms\Components\TextInput::make('queue')->disabled(),
                Forms\Components\Textarea::make('payload')->disabled()->rows(10)->columnSpanFull(),
                Forms\Components\Textarea::make('exception')->disabled()->rows(20)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('queue')->searchable(),
                Tables\Columns\TextColumn::make('failed_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('exception')
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function ($record) {
                        \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => $record->uuid]);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedJobs::route('/'),
        ];
    }
}
