<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Malzariey\FilamentLexicalEditor\LexicalEditor;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Nội dung';
    }

    public static function getModelLabel(): string
    {
        return 'Bài viết';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Bài viết';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin bài viết')
                    ->schema([
                        TextInput::make('title')
                            ->label('Tiêu đề')
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

                        Select::make('category_id')
                            ->label('Chuyên mục')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Chọn chuyên mục'),

                        Toggle::make('active')
                            ->label('Hiển thị')
                            ->default(true),

                        LexicalEditor::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->helperText('Ảnh paste/upload sẽ tự lưu ra file, không lưu base64 vào DB.')
                            ->hintActions([
                                Action::make('generateContent')
                                    ->label('Tạo nội dung AI')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('success')
                                    ->action(function ($get, $livewire) {
                                        $title = $get('title') ?: 'chủ đề bạn muốn';
                                        $prompt = "Viết ngay bài viết tiếng Việt, tiêu đề: \"{$title}\".\n\nYêu cầu:\n- Mở bài: thu hút, nêu vấn đề/lợi ích\n- Thân bài: 3-5 phần với heading rõ ràng\n- Kết bài: tóm tắt + kêu gọi hành động\n- Tối ưu SEO: từ khóa tự nhiên, dễ đọc\n- Độ dài: 500-800 từ\n\nTrả lời trực tiếp nội dung bài viết, không hỏi lại.";
                                        $url = 'https://chatgpt.com/?model=auto&q=' . urlencode($prompt);
                                        
                                        $livewire->js("window.open('{$url}', '_blank')");
                                    }),
                                Action::make('improveContent')
                                    ->label('Nâng cấp nội dung')
                                    ->icon('heroicon-o-arrow-trending-up')
                                    ->color('warning')
                                    ->action(function ($get, $state, $livewire) {
                                        $title = $get('title') ?: 'chủ đề bạn muốn';
                                        $content = $state ?? '';
                                        
                                        // Strip HTML và giới hạn độ dài
                                        $plainContent = strip_tags($content);
                                        $plainContent = preg_replace('/\s+/', ' ', $plainContent);
                                        $plainContent = trim($plainContent);
                                        $plainContent = Str::limit($plainContent, 1500, '...');
                                        
                                        if (empty($plainContent)) {
                                            $prompt = "Viết ngay bài viết tiếng Việt, tiêu đề: \"{$title}\".\n\nYêu cầu:\n- Mở bài: thu hút, nêu vấn đề/lợi ích\n- Thân bài: 3-5 phần với heading rõ ràng\n- Kết bài: tóm tắt + kêu gọi hành động\n- Tối ưu SEO: từ khóa tự nhiên, dễ đọc\n- Độ dài: 500-800 từ\n\nTrả lời trực tiếp nội dung bài viết, không hỏi lại.";
                                        } else {
                                            $prompt = "Nâng cấp bài viết tiếng Việt sau:\n\nTiêu đề: \"{$title}\"\n\nNội dung hiện tại:\n{$plainContent}\n\nYêu cầu:\n- Giữ ý chính, bổ sung chi tiết/ví dụ\n- Cải thiện SEO: từ khóa, heading logic\n- Tăng tính thuyết phục, dễ đọc\n- Thêm kêu gọi hành động cuối bài\n\nTrả lời trực tiếp bài viết đã nâng cấp, không hỏi lại.";
                                        }
                                        
                                        $url = 'https://chatgpt.com/?model=auto&q=' . urlencode($prompt);
                                        
                                        $livewire->js("window.open('{$url}', '_blank')");
                                    }),
                            ]),

                        FileUpload::make('thumbnail')
                            ->label('Ảnh đại diện')
                            ->image()
                            ->directory('thumbnails')
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

                                // Nếu không cần convert, lưu nguyên file
                                if (!in_array($mimeType, $convertibleMimeTypes)) {
                                    $extension = $file->getClientOriginalExtension();
                                    $path = 'thumbnails/' . $newFileName . '.' . $extension;
                                    $file->storeAs('thumbnails', $newFileName . '.' . $extension, 'public');
                                    return $path;
                                }

                                // Convert sang WebP
                                $tempPath = $file->getRealPath();
                                $webpFileName = $newFileName . '.webp';
                                $webpPath = 'thumbnails/' . $webpFileName;
                                $fullWebpPath = storage_path('app/public/' . $webpPath);

                                // Đảm bảo thư mục tồn tại
                                if (!is_dir(dirname($fullWebpPath))) {
                                    mkdir(dirname($fullWebpPath), 0755, true);
                                }

                                Image::useImageDriver(ImageDriver::Gd)
                                    ->load($tempPath)
                                    ->quality(80)
                                    ->save($fullWebpPath);

                                return $webpPath;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Ảnh')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Chuyên mục')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Chưa phân loại'),

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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Chuyên mục')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
