<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemConfigurationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemConfigurationResource extends Resource
{
    // Assume a Settings model exists
    protected static ?string $model = \App\Models\User::class; // Fallback placeholder

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'System Command Center';
    protected static ?string $navigationLabel = 'System Configurations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('AdSense Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('adsense_client_id')->label('AdSense Client ID (ca-pub-xxx)'),
                        Forms\Components\TextInput::make('adsense_slot_sidebar')->label('Sidebar Ad Slot ID'),
                        Forms\Components\TextInput::make('adsense_slot_infeed')->label('In-Feed Ad Slot ID'),
                    ]),
                Forms\Components\Section::make('AI Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('ollama_models')
                            ->label('Ollama Fallback Chain (Comma separated)')
                            ->default('llama3.1, hermes, codex'),
                        Forms\Components\Textarea::make('ai_system_prompt')
                            ->label('Worker System Prompt')
                            ->rows(5),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key'),
                Tables\Columns\TextColumn::make('value')->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfigurations::route('/'),
        ];
    }
}
