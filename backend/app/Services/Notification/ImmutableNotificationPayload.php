<?php

namespace App\Services\Notification;

class ImmutableNotificationPayload
{
    private string $title;
    private string $body;
    private ?string $icon;
    private ?string $badge;
    private ?string $image;
    private string $url;
    private string $tag;
    private array $actions;
    private array $extraData;

    public function __construct(
        string $title,
        string $body,
        string $url,
        ?string $icon = null,
        ?string $badge = null,
        ?string $image = null,
        string $tag = 'default',
        array $actions = [],
        array $extraData = []
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->icon = $icon ?? asset('/images/logo.png');
        $this->badge = $badge ?? asset('/images/logo.png');
        $this->image = $image;
        $this->tag = $tag;
        $this->actions = $actions;
        $this->extraData = $extraData;
    }

    public static function builder(): NotificationPayloadBuilder
    {
        return new NotificationPayloadBuilder();
    }

    public function getTitle(): string { return $this->title; }
    public function getBody(): string { return $this->body; }
    public function getUrl(): string { return $this->url; }
    public function getIcon(): ?string { return $this->icon; }
    public function getBadge(): ?string { return $this->badge; }
    public function getImage(): ?string { return $this->image; }
    public function getTag(): string { return $this->tag; }
    public function getActions(): array { return $this->actions; }
    public function getExtraData(): array { return $this->extraData; }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon,
            'badge' => $this->badge,
            'image' => $this->image,
            'tag' => $this->tag,
            'actions' => $this->actions,
            'extra' => $this->extraData,
        ]);
    }
}

class NotificationPayloadBuilder
{
    private string $title = '';
    private string $body = '';
    private string $url = '/';
    private ?string $icon = null;
    private ?string $badge = null;
    private ?string $image = null;
    private string $tag = 'latestdeal-alert';
    private array $actions = [];
    private array $extraData = [];

    public function title(string $title): self { $this->title = $title; return $this; }
    public function body(string $body): self { $this->body = $body; return $this; }
    public function url(string $url): self { $this->url = $url; return $this; }
    public function icon(?string $icon): self { $this->icon = $icon; return $this; }
    public function badge(?string $badge): self { $this->badge = $badge; return $this; }
    public function image(?string $image): self { $this->image = $image; return $this; }
    public function tag(string $tag): self { $this->tag = $tag; return $this; }
    public function addAction(string $action, string $title, ?string $icon = null): self
    {
        $this->actions[] = array_filter(['action' => $action, 'title' => $title, 'icon' => $icon]);
        return $this;
    }
    public function extra(array $data): self { $this->extraData = $data; return $this; }

    public function build(): ImmutableNotificationPayload
    {
        return new ImmutableNotificationPayload(
            $this->title,
            $this->body,
            $this->url,
            $this->icon,
            $this->badge,
            $this->image,
            $this->tag,
            $this->actions,
            $this->extraData
        );
    }
}
