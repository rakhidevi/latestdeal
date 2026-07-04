<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Actions\Action;
use App\Events\DealScrapeRequested;
use Filament\Notifications\Notification;
use App\Models\Merchant;
use App\Models\Deal;
use Illuminate\Support\Str;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class SystemDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.pages.system-dashboard';
    protected static ?string $navigationGroup = 'System Command Center';

    protected function getHeaderWidgets(): array
    {
        return [
            SystemHealthWidget::class,
        ];
    }

    public ?array $crawlData = [];
    public ?array $affiliateData = [];

    public function mount(): void
    {
        $this->crawlForm->fill();
        $this->affiliateForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'crawlForm',
            'affiliateForm',
        ];
    }

    public function crawlForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('url')
                    ->label('Product URL to Scrape')
                    ->required()
                    ->url(),
            ])
            ->statePath('crawlData');
    }

    public function affiliateForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('raw_url')
                    ->label('Raw Product URL')
                    ->required()
                    ->url(),
            ])
            ->statePath('affiliateData');
    }

    public function triggerCrawl(): void
    {
        $data = $this->crawlForm->getState();
        
        // Dispatch the WebSocket event to instantly wake up Python worker
        event(new DealScrapeRequested($data['url']));

        Notification::make()
            ->title('Scrape Command Sent to Worker!')
            ->success()
            ->send();
            
        $this->crawlForm->fill();
    }

    public function generateAffiliateLink(): void
    {
        $data = $this->affiliateForm->getState();
        $url = $data['raw_url'];
        
        // Simple heuristic: Find matching merchant by domain
        $merchant = Merchant::all()->first(function($m) use ($url) {
            return Str::contains($url, $m->domain);
        });

        if ($merchant) {
            $separator = Str::contains($url, '?') ? '&' : '?';
            $trackedUrl = $url . $separator . $merchant->affiliate_param_key . '=' . $merchant->store_id;
            
            Notification::make()
                ->title('Link Generated: ' . $trackedUrl)
                ->success()
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->title('No matching merchant found in database for this domain.')
                ->danger()
                ->send();
        }
    }
}

class SystemHealthWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Queue Jobs', \Illuminate\Support\Facades\DB::table('jobs')->count())
                ->description('Active tasks waiting to execute')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Total Deals Ingested', Deal::count())
                ->description('All time deals processed')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
            Stat::make('Active Deals', Deal::where('status', 'active')->count())
                ->description('Deals currently live on feed')
                ->descriptionIcon('heroicon-m-fire')
                ->color('primary'),
        ];
    }
}
