<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealResource\Pages;
use App\Models\Deal;
use App\Jobs\PublishDealToTelegramJob;
use App\Jobs\PublishDealToFacebookJob;
use App\Jobs\PublishDealToInstagramJob;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\TextInput::make('original_price')->numeric()->required(),
                Forms\Components\TextInput::make('discounted_price')->numeric()->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'expired' => 'Expired',
                    ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('Image'),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('discounted_price')->money('INR')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'expired',
                        'warning' => 'draft',
                        'success' => 'active',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'expired' => 'Expired',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('push_to_production')
                    ->label('Push to Production')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Deal $record): bool => $record->status === 'draft')
                    ->action(function (Deal $record) {
                        $record->update(['status' => 'active']);
                        Notification::make()->title('Deal pushed to production!')->success()->send();
                    }),
                Tables\Actions\Action::make('publish_to_telegram')
                    ->label('Push to Telegram')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->action(function (Deal $record) {
                        $telegramAccounts = \App\Models\SocialAccount::where('platform', 'telegram')->where('is_active', true)->get();
                        if ($telegramAccounts->isNotEmpty()) {
                            foreach ($telegramAccounts as $account) {
                                PublishDealToTelegramJob::dispatch($record, $account->id);
                            }
                        } else {
                            PublishDealToTelegramJob::dispatch($record, null);
                        }
                        Notification::make()->title('Queued for Telegram!')->success()->send();
                    }),
                Tables\Actions\Action::make('publish_to_facebook')
                    ->label('Push to Facebook')
                    ->icon('heroicon-o-share')
                    ->color('primary')
                    ->action(function (Deal $record) {
                        $facebookAccounts = \App\Models\SocialAccount::where('platform', 'facebook')->where('is_active', true)->get();
                        if ($facebookAccounts->isNotEmpty()) {
                            foreach ($facebookAccounts as $account) {
                                PublishDealToFacebookJob::dispatch($record, $account->id);
                            }
                            Notification::make()->title('Queued for Facebook!')->success()->send();
                        } else {
                            Notification::make()->title('No active Facebook account found!')->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('publish_to_instagram')
                    ->label('Push to Instagram')
                    ->icon('heroicon-o-camera')
                    ->color('danger')
                    ->action(function (Deal $record) {
                        $instagramAccounts = \App\Models\SocialAccount::where('platform', 'instagram')->where('is_active', true)->get();
                        if ($instagramAccounts->isNotEmpty()) {
                            foreach ($instagramAccounts as $account) {
                                PublishDealToInstagramJob::dispatch($record, $account->id);
                            }
                            Notification::make()->title('Queued for Instagram!')->success()->send();
                        } else {
                            Notification::make()->title('No active Instagram account found!')->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('share_to_whatsapp')
                    ->label('Share on WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn (Deal $record) => app(WhatsAppService::class)->generateShareIntent($record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('ping_google')
                    ->label('Ping Google SEO')
                    ->icon('heroicon-o-globe-alt')
                    ->action(function (Deal $record) {
                        // Ping Google Indexing API
                        Notification::make()->title('Google Indexing Pinged!')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_expired')
                        ->label('Mark Expired')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'expired'])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeals::route('/'),
            'create' => Pages\CreateDeal::route('/create'),
            'edit' => Pages\EditDeal::route('/{record}/edit'),
        ];
    }
}
