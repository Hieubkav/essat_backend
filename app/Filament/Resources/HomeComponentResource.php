<?php

namespace App\Filament\Resources;

use App\Enums\HomeComponentType;
use App\Filament\Resources\HomeComponentResource\Pages;
use App\Models\HomeComponent;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HomeComponentResource extends Resource
{
    protected static ?string $model = HomeComponent::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-home';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hệ thống';
    }

    public static function getModelLabel(): string
    {
        return 'Section Trang chủ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Quản lý Trang chủ';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Cấu hình chung')
                    ->schema([
                        Select::make('type')
                            ->label('Loại Section')
                            ->options(HomeComponentType::options())
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('config', []))
                            ->disabled(fn (?HomeComponent $record) => $record !== null)
                            ->helperText(fn (?HomeComponent $record) => $record
                                ? 'Không thể thay đổi loại sau khi tạo'
                                : 'Chọn loại section bạn muốn thêm')
                            ->columnSpan(2),

                        Toggle::make('active')
                            ->label('Hiển thị')
                            ->default(true)
                            ->helperText('Bật/tắt hiển thị section này'),

                        TextInput::make('order')
                            ->label('Thứ tự')
                            ->numeric()
                            ->default(0)
                            ->helperText('Số nhỏ hơn hiển thị trước')
                            ->hidden(),
                    ])
                    ->columns(4),

                Section::make('Nội dung')
                    ->schema(fn (Get $get) => static::getConfigFields($get('type')))
                    ->visible(fn (Get $get) => $get('type') !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(60),

                Tables\Columns\IconColumn::make('type')
                    ->label('Icon')
                    ->icon(fn (HomeComponent $record): string =>
                        HomeComponentType::tryFrom($record->type)?->getIcon() ?? 'heroicon-o-question-mark-circle'
                    )
                    ->width(60),

                Tables\Columns\TextColumn::make('type')
                    ->label('Loại')
                    ->formatStateUsing(fn (string $state): string =>
                        HomeComponentType::tryFrom($state)?->getLabel() ?? $state
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('config_summary')
                    ->label('Tóm tắt')
                    ->getStateUsing(fn (HomeComponent $record): string =>
                        static::getConfigSummary($record)
                    )
                    ->wrap()
                    ->limit(80),

                Tables\Columns\ToggleColumn::make('active')
                    ->label('Hiển thị'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại')
                    ->options(HomeComponentType::options()),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Trạng thái')
                    ->trueLabel('Đang hiển thị')
                    ->falseLabel('Đang ẩn'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeComponents::route('/'),
            'create' => Pages\CreateHomeComponent::route('/create'),
            'edit' => Pages\EditHomeComponent::route('/{record}/edit'),
        ];
    }

    public static function getConfigFields(?string $type): array
    {
        if (!$type) {
            return [];
        }

        return match ($type) {
            HomeComponentType::HeroCarousel->value => static::heroCarouselFields(),
            HomeComponentType::Stats->value => static::statsFields(),
            HomeComponentType::About->value => static::aboutFields(),
            HomeComponentType::ProductCategories->value => static::productCategoriesFields(),
            HomeComponentType::FeaturedProducts->value => static::featuredProductsFields(),
            HomeComponentType::Partners->value => static::partnersFields(),
            HomeComponentType::News->value => static::newsFields(),
            HomeComponentType::Footer->value => static::footerFields(),
            default => [],
        };
    }

    public static function getConfigSummary(HomeComponent $record): string
    {
        $config = $record->config ?? [];

        return match ($record->type) {
            HomeComponentType::HeroCarousel->value =>
                count($config['slides'] ?? []) . ' slides',

            HomeComponentType::Stats->value =>
                count($config['items'] ?? []) . ' chỉ số',

            HomeComponentType::About->value =>
                $config['title'] ?? 'Chưa có tiêu đề',

            HomeComponentType::ProductCategories->value =>
                count($config['categories'] ?? []) . ' danh mục',

            HomeComponentType::FeaturedProducts->value =>
                count($config['products'] ?? []) . ' sản phẩm',

            HomeComponentType::Partners->value =>
                count($config['partners'] ?? []) . ' đối tác',

            HomeComponentType::News->value =>
                ($config['display_mode'] ?? 'latest') === 'latest'
                    ? 'Hiển thị ' . ($config['limit'] ?? 6) . ' bài mới nhất'
                    : count($config['post_ids'] ?? []) . ' bài được chọn',

            HomeComponentType::Footer->value =>
                $config['company_name'] ?? 'Footer',

            default => 'N/A',
        };
    }

    protected static function heroCarouselFields(): array
    {
        return [
            Repeater::make('config.slides')
                ->label('Danh sách Slides')
                ->schema([
                    FileUpload::make('image')
                        ->label('Ảnh Banner')
                        ->image()
                        ->directory('banners')
                        ->disk('public')
                        ->required(),
                ])
                ->reorderable()
                ->collapsible()
                ->defaultItems(1)
                ->maxItems(10)
                ->itemLabel(fn (array $state): ?string => 'Slide'),
        ];
    }

    protected static function statsFields(): array
    {
        return [
            Repeater::make('config.items')
                ->label('Các chỉ số thống kê')
                ->schema([
                    TextInput::make('value')
                        ->label('Giá trị')
                        ->placeholder('1,500+')
                        ->required()
                        ->maxLength(20),

                    TextInput::make('label')
                        ->label('Mô tả')
                        ->placeholder('Khách hàng tin dùng')
                        ->required()
                        ->maxLength(100),
                ])
                ->columns(2)
                ->reorderable()
                ->defaultItems(4)
                ->maxItems(8),
        ];
    }

    protected static function aboutFields(): array
    {
        return [
            TextInput::make('config.badge')
                ->label('Badge text')
                ->placeholder('Về chúng tôi')
                ->maxLength(50),

            TextInput::make('config.title')
                ->label('Tiêu đề chính (chữ đen)')
                ->placeholder('Đối tác công nghệ')
                ->required()
                ->maxLength(100)
                ->helperText('Phần tiêu đề hiển thị màu đen'),

            TextInput::make('config.subtitle')
                ->label('Tiêu đề phụ (chữ màu)')
                ->placeholder('Chiến lược & Toàn diện')
                ->maxLength(150)
                ->helperText('Phần tiêu đề hiển thị màu chủ đạo (primary)'),

            RichEditor::make('config.description')
                ->label('Mô tả chi tiết')
                ->toolbarButtons([
                    'bold', 'italic', 'underline',
                    'bulletList', 'orderedList',
                    'link',
                ]),

            Textarea::make('config.quote')
                ->label('Trích dẫn / Slogan')
                ->placeholder('Cam kết mang đến giải pháp tối ưu...')
                ->rows(2),

            Repeater::make('config.features')
                ->label('Điểm nổi bật')
                ->schema([
                    TextInput::make('title')
                        ->label('Tiêu đề')
                        ->required()
                        ->maxLength(50),

                    Textarea::make('description')
                        ->label('Mô tả')
                        ->rows(2)
                        ->maxLength(200),
                ])
                ->columns(2)
                ->reorderable()
                ->maxItems(6),
        ];
    }

    protected static function productCategoriesFields(): array
    {
        return [
            TextInput::make('config.title')
                ->label('Tiêu đề section')
                ->default('Danh mục sản phẩm')
                ->maxLength(100),

            Repeater::make('config.categories')
                ->label('Danh sách danh mục')
                ->schema([
                    FileUpload::make('image')
                        ->label('Ảnh đại diện')
                        ->image()
                        ->directory('categories')
                        ->disk('public')
                        ->required(),

                    TextInput::make('name')
                        ->label('Tên danh mục')
                        ->required()
                        ->maxLength(50),

                    Select::make('link_type')
                        ->label('Loại liên kết')
                        ->options([
                            'category' => 'Chọn danh mục có sẵn',
                            'custom' => 'Tự nhập link',
                        ])
                        ->default('category')
                        ->live()
                        ->required()
                        ->helperText('Chọn danh mục sẽ tự động tạo link: /san-pham?category=slug'),

                    Select::make('category_id')
                        ->label('Chọn danh mục')
                        ->options(fn () => \App\Models\ProductCategory::query()
                            ->where('active', true)
                            ->orderBy('order')
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->searchable()
                        ->visible(fn (Get $get) => $get('link_type') === 'category')
                        ->required(fn (Get $get) => $get('link_type') === 'category')
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $category = \App\Models\ProductCategory::find($state);
                                if ($category) {
                                    $set('link', '/san-pham?category=' . $category->slug);
                                }
                            }
                        })
                        ->helperText('Link sẽ tự động được tạo khi chọn danh mục'),

                    TextInput::make('link')
                        ->label('Link tùy chỉnh')
                        ->placeholder('/san-pham?category=custom-slug')
                        ->visible(fn (Get $get) => $get('link_type') === 'custom')
                        ->required(fn (Get $get) => $get('link_type') === 'custom')
                        ->helperText('Sử dụng đường dẫn tương đối (bắt đầu bằng /)'),
                ])
                ->columns(2)
                ->reorderable()
                ->collapsible()
                ->maxItems(12)
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Danh mục'),
        ];
    }

    protected static function featuredProductsFields(): array
    {
        return [
            TextInput::make('config.title')
                ->label('Tiêu đề section')
                ->default('Sản phẩm nổi bật')
                ->maxLength(100),

            TextInput::make('config.subtitle')
                ->label('Mô tả section')
                ->maxLength(200),

            Select::make('config.display_mode')
                ->label('Chế độ hiển thị')
                ->options([
                    'manual' => 'Chọn thủ công',
                    'latest' => 'Sản phẩm mới nhất',
                ])
                ->default('manual')
                ->live(),

            TextInput::make('config.limit')
                ->label('Số lượng hiển thị')
                ->numeric()
                ->default(8)
                ->minValue(4)
                ->maxValue(24)
                ->visible(fn (Get $get) => $get('config.display_mode') !== 'manual'),

            Repeater::make('config.products')
                ->label('Chọn sản phẩm')
                ->visible(fn (Get $get) => $get('config.display_mode') === 'manual')
                ->schema([
                    FileUpload::make('image')
                        ->label('Ảnh sản phẩm')
                        ->image()
                        ->directory('products')
                        ->disk('public'),

                    TextInput::make('name')
                        ->label('Tên sản phẩm')
                        ->required(),

                    TextInput::make('price')
                        ->label('Giá')
                        ->placeholder('Liên hệ'),

                    TextInput::make('link')
                        ->label('Link đến'),
                ])
                ->columns(2)
                ->reorderable()
                ->maxItems(24)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Sản phẩm'),

            TextInput::make('config.view_all_link')
                ->label('Link "Xem tất cả"')
                ->placeholder('/products'),
        ];
    }

    protected static function partnersFields(): array
    {
        return [
            TextInput::make('config.title')
                ->label('Tiêu đề section')
                ->default('Đối tác chiến lược')
                ->maxLength(100),

            Repeater::make('config.partners')
                ->label('Danh sách đối tác')
                ->schema([
                    FileUpload::make('logo')
                        ->label('Logo đối tác')
                        ->image()
                        ->directory('partners')
                        ->disk('public')
                        ->required(),

                    TextInput::make('name')
                        ->label('Tên đối tác')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('link')
                        ->label('Website')
                        ->placeholder('https://partner.com'),
                ])
                ->columns(3)
                ->reorderable()
                ->collapsible()
                ->maxItems(20)
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Đối tác'),

            Toggle::make('config.auto_scroll')
                ->label('Tự động cuộn')
                ->default(true),
        ];
    }

    protected static function newsFields(): array
    {
        return [
            TextInput::make('config.title')
                ->label('Tiêu đề section')
                ->default('Tin tức & Sự kiện')
                ->maxLength(100),

            Select::make('config.display_mode')
                ->label('Chế độ hiển thị')
                ->options([
                    'latest' => 'Bài mới nhất',
                    'manual' => 'Chọn thủ công',
                ])
                ->default('latest')
                ->live(),

            TextInput::make('config.limit')
                ->label('Số bài hiển thị')
                ->numeric()
                ->default(6)
                ->minValue(3)
                ->maxValue(12)
                ->visible(fn (Get $get) => $get('config.display_mode') === 'latest'),

            Repeater::make('config.posts')
                ->label('Chọn bài viết')
                ->visible(fn (Get $get) => $get('config.display_mode') === 'manual')
                ->schema([
                    FileUpload::make('image')
                        ->label('Ảnh bài viết')
                        ->image()
                        ->directory('news')
                        ->disk('public'),

                    TextInput::make('title')
                        ->label('Tiêu đề')
                        ->required(),

                    TextInput::make('link')
                        ->label('Link đến'),
                ])
                ->columns(3)
                ->reorderable()
                ->maxItems(12)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Bài viết'),

            TextInput::make('config.view_all_link')
                ->label('Link "Xem tất cả"')
                ->placeholder('/news'),
        ];
    }

    protected static function footerFields(): array
    {
        return [
            Section::make('Thông tin công ty')
                ->schema([
                    TextInput::make('config.company_name')
                        ->label('Tên công ty')
                        ->required()
                        ->maxLength(100),

                    Textarea::make('config.address')
                        ->label('Địa chỉ')
                        ->rows(2),

                    TextInput::make('config.phone')
                        ->label('Số điện thoại')
                        ->tel(),

                    TextInput::make('config.hotline')
                        ->label('Hotline')
                        ->placeholder('1900 xxxx'),

                    TextInput::make('config.email')
                        ->label('Email')
                        ->email(),
                ])
                ->columns(2),

            Section::make('Mạng xã hội')
                ->schema([
                    Repeater::make('config.social_links')
                        ->label('Liên kết mạng xã hội')
                        ->schema([
                            Select::make('platform')
                                ->label('Nền tảng')
                                ->options([
                                    'facebook' => 'Facebook',
                                    'zalo' => 'Zalo',
                                    'youtube' => 'YouTube',
                                    'tiktok' => 'TikTok',
                                    'messenger' => 'Messenger',
                                ])
                                ->required(),

                            TextInput::make('url')
                                ->label('Đường dẫn')
                                ->required(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->maxItems(8),
                ]),

            Section::make('Chính sách')
                ->schema([
                    Repeater::make('config.policies')
                        ->label('Danh sách chính sách')
                        ->schema([
                            TextInput::make('label')
                                ->label('Tên chính sách')
                                ->required()
                                ->maxLength(50),

                            TextInput::make('link')
                                ->label('Đường dẫn')
                                ->placeholder('/chinh-sach-doi-tra'),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->maxItems(10)
                        ->defaultItems(4),
                ]),

        ];
    }
}
