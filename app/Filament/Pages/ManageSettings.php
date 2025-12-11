<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-settings';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hệ thống';
    }

    public static function getNavigationLabel(): string
    {
        return 'Cài đặt';
    }

    public function getTitle(): string
    {
        return 'Cài đặt website';
    }

    public static function getNavigationSort(): ?int
    {
        return 20;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::where('singleton', Setting::SINGLETON_KEY)->first();

        $this->form->fill($setting ? $setting->toArray() : []);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Thông tin chung')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Tên website')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ví dụ: ZenBlog'),

                        Grid::make(2)
                            ->schema([
                                ColorPicker::make('primary_color')
                                    ->label('Màu chủ đạo')
                                    ->default('#000000'),

                                ColorPicker::make('secondary_color')
                                    ->label('Màu phụ')
                                    ->default('#ffffff'),
                            ]),
                    ]),

                Section::make('Liên hệ')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('0123 456 789'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('contact@example.com'),

                        TextInput::make('address')
                            ->label('Địa chỉ')
                            ->maxLength(500)
                            ->placeholder('123 Đường ABC, Quận 1, TP.HCM'),
                    ])
                    ->columns(2),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(255)
                            ->placeholder('Tiêu đề cho công cụ tìm kiếm'),

                        Textarea::make('seo_description')
                            ->label('SEO Description')
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('Mô tả ngắn cho công cụ tìm kiếm'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['singleton'] = Setting::SINGLETON_KEY;

        Setting::updateOrCreate(
            ['singleton' => Setting::SINGLETON_KEY],
            $data
        );

        Notification::make()
            ->success()
            ->title('Đã lưu cài đặt')
            ->body('Cài đặt website đã được cập nhật thành công.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Lưu thay đổi')
                ->submit('save'),
        ];
    }
}
