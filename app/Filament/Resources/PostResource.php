<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Malzariey\FilamentLexicalEditor\LexicalEditor;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                            ->unique(ignoreRecord: true),

                        Toggle::make('active')
                            ->label('Hiển thị')
                            ->default(true),

                        LexicalEditor::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->helperText('Ảnh paste/upload sẽ tự lưu ra file, không lưu base64 vào DB.'),

                        FileUpload::make('thumbnail')
                            ->label('Ảnh đại diện')
                            ->image()
                            ->directory('thumbnails')
                            ->disk('public')
                            ->imageEditor(),
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

                Tables\Columns\TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Thứ tự')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
