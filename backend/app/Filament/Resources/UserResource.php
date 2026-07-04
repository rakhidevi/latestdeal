<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    // Assume a User model exists
    protected static ?string $model = \App\Models\User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Platform Management';
    protected static ?string $navigationLabel = 'Publishers & Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\Toggle::make('is_pro')->label('Pro Publisher'),
                Forms\Components\Toggle::make('is_banned')->label('Banned')->default(false),
                Forms\Components\TextInput::make('api_key')->label('Publisher API Key')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\BooleanColumn::make('is_pro')->label('Pro'),
                Tables\Columns\BadgeColumn::make('is_banned')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Banned' : 'Active'),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_banned')->query(fn ($query) => $query->where('is_banned', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_ban')
                    ->label(fn ($record) => $record->is_banned ? 'Unban' : 'Ban')
                    ->color(fn ($record) => $record->is_banned ? 'success' : 'danger')
                    ->action(function ($record) {
                        $record->update(['is_banned' => !$record->is_banned]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
