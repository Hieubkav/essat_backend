<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Malzariey\FilamentLexicalEditor\LexicalEditor;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cube';
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getModelLabel(): string
    {
        return 'Sản phẩm';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Sản phẩm';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin sản phẩm')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên sản phẩm')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Đường dẫn')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->hidden(),

                        Select::make('categories')
                            ->label('Danh mục')
                            ->multiple()
                            ->relationship('categories', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Chọn danh mục'),

                        TextInput::make('price')
                            ->label('Giá')
                            ->numeric()
                            ->prefix('VNĐ')
                            ->minValue(0),

                        Toggle::make('active')
                            ->label('Hiển thị')
                            ->default(true),

                        Textarea::make('description')
                            ->label('Mô tả ngắn')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Nội dung chi tiết')
                    ->schema([
                        LexicalEditor::make('content')
                            ->label('Nội dung')
                            ->helperText('Ảnh paste/upload sẽ tự lưu ra file, không lưu base64 vào DB.')
                            ->hintActions([
                                Action::make('generateContent')
                                    ->label('Tạo nội dung AI')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('success')
                                    ->action(function ($get, $livewire) {
                                        $name = $get('name') ?: 'sản phẩm bạn muốn';
                                        $prompt = "Viết ngay mô tả sản phẩm tiếng Việt, tên: \"{$name}\".\n\nYêu cầu:\n- Mở đầu: thu hút, nêu điểm nổi bật\n- Thân bài: 3-5 phần với heading rõ ràng (tính năng, ưu điểm, thông số)\n- Kết bài: tóm tắt + kêu gọi mua hàng\n- Tối ưu SEO: từ khóa tự nhiên, dễ đọc\n- Độ dài: 300-500 từ\n\nTrả lời trực tiếp nội dung mô tả sản phẩm, không hỏi lại.";
                                        $url = 'https://chatgpt.com/?model=auto&q=' . urlencode($prompt);
                                        
                                        $livewire->js("window.open('{$url}', '_blank')");
                                    }),
                                Action::make('improveContent')
                                    ->label('Nâng cấp nội dung')
                                    ->icon('heroicon-o-arrow-trending-up')
                                    ->color('warning')
                                    ->action(function ($get, $state, $livewire) {
                                        $name = $get('name') ?: 'sản phẩm bạn muốn';
                                        $content = $state ?? '';
                                        
                                        // Strip HTML và giới hạn độ dài
                                        $plainContent = strip_tags($content);
                                        $plainContent = preg_replace('/\s+/', ' ', $plainContent);
                                        $plainContent = trim($plainContent);
                                        $plainContent = Str::limit($plainContent, 1500, '...');
                                        
                                        if (empty($plainContent)) {
                                            $prompt = "Viết ngay mô tả sản phẩm tiếng Việt, tên: \"{$name}\".\n\nYêu cầu:\n- Mở đầu: thu hút, nêu điểm nổi bật\n- Thân bài: 3-5 phần với heading rõ ràng (tính năng, ưu điểm, thông số)\n- Kết bài: tóm tắt + kêu gọi mua hàng\n- Tối ưu SEO: từ khóa tự nhiên, dễ đọc\n- Độ dài: 300-500 từ\n\nTrả lời trực tiếp nội dung mô tả sản phẩm, không hỏi lại.";
                                        } else {
                                            $prompt = "Nâng cấp mô tả sản phẩm tiếng Việt sau:\n\nTên sản phẩm: \"{$name}\"\n\nNội dung hiện tại:\n{$plainContent}\n\nYêu cầu:\n- Giữ ý chính, bổ sung chi tiết/ví dụ\n- Cải thiện SEO: từ khóa, heading logic\n- Tăng tính thuyết phục, dễ đọc\n- Thêm kêu gọi mua hàng cuối bài\n\nTrả lời trực tiếp mô tả sản phẩm đã nâng cấp, không hỏi lại.";
                                        }
                                        
                                        $url = 'https://chatgpt.com/?model=auto&q=' . urlencode($prompt);
                                        
                                        $livewire->js("window.open('{$url}', '_blank')");
                                    }),
                            ]),

                        FileUpload::make('thumbnail')
                            ->label('Ảnh đại diện')
                            ->image()
                            ->directory('products')
                            ->disk('public')
                            ->imageEditor()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                            ])
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                                $convertibleMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                                $mimeType = $file->getMimeType();

                                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                                $newFileName = Str::slug($originalName) . '-' . time() . '-' . Str::random(6);

                                if (!in_array($mimeType, $convertibleMimeTypes)) {
                                    $extension = $file->getClientOriginalExtension();
                                    $path = 'products/' . $newFileName . '.' . $extension;
                                    $file->storeAs('products', $newFileName . '.' . $extension, 'public');
                                    return $path;
                                }

                                $tempPath = $file->getRealPath();
                                $webpFileName = $newFileName . '.webp';
                                $webpPath = 'products/' . $webpFileName;
                                $fullWebpPath = storage_path('app/public/' . $webpPath);

                                if (!is_dir(dirname($fullWebpPath))) {
                                    mkdir(dirname($fullWebpPath), 0755, true);
                                }

                                Image::useImageDriver(ImageDriver::Gd)
                                    ->load($tempPath)
                                    ->quality(80)
                                    ->save($fullWebpPath);

                                return $webpPath;
                            }),

                        SpatieMediaLibraryFileUpload::make('images')
                            ->label('Ảnh sản phẩm')
                            ->collection('images')
                            ->disk('public')
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->maxFiles(20)
                            ->panelLayout('grid')
                            ->imagePreviewHeight('150')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                            ])
                            ->helperText('Upload nhiều ảnh sản phẩm (tối đa 20 ảnh)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Danh mục')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Giá')
                    ->numeric()
                    ->sortable()
                    ->suffix(' VNĐ'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('order')
            ->filters([
                Tables\Filters\SelectFilter::make('categories')
                    ->label('Danh mục')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hiển thị')
                    ->falseLabel('Đã ẩn'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
