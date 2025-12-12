<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $title = 'Bài viết';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
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

                Tables\Columns\IconColumn::make('active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang hiển thị')
                    ->falseLabel('Đã ẩn'),
            ])
            ->headerActions([
                // Tạo bài viết mới (mở trang tạo mới ở tab mới)
                Action::make('createPost')
                    ->label('Tạo bài viết')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => PostResource::getUrl('create', [
                        'category_id' => $this->getOwnerRecord()->getKey(),
                    ]))
                    ->openUrlInNewTab(),

                // Thêm bài viết có sẵn vào chuyên mục
                Action::make('addExistingPost')
                    ->label('Thêm bài viết có sẵn')
                    ->icon('heroicon-o-link')
                    ->form([
                        Select::make('post_ids')
                            ->label('Chọn bài viết')
                            ->options(fn () => Post::query()
                                ->where(function ($query) {
                                    $query->whereNull('category_id')
                                        ->orWhere('category_id', '!=', $this->getOwnerRecord()->getKey());
                                })
                                ->pluck('title', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        Post::whereIn('id', $data['post_ids'])
                            ->update(['category_id' => $this->getOwnerRecord()->getKey()]);
                    }),
            ])
            ->actions([
                // Mở trang chỉnh sửa ở tab mới
                Action::make('editInNewTab')
                    ->label('Sửa')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Post $record) => PostResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),

                // Gỡ bài viết khỏi chuyên mục (không xóa)
                Action::make('detach')
                    ->label('Gỡ')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Gỡ bài viết khỏi chuyên mục')
                    ->modalDescription('Bài viết sẽ được gỡ khỏi chuyên mục này nhưng không bị xóa.')
                    ->action(fn (Post $record) => $record->update(['category_id' => null])),

                Actions\DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    // Gỡ nhiều bài viết khỏi chuyên mục
                    Actions\BulkAction::make('detachBulk')
                        ->label('Gỡ khỏi chuyên mục')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Gỡ các bài viết khỏi chuyên mục')
                        ->modalDescription('Các bài viết sẽ được gỡ khỏi chuyên mục này nhưng không bị xóa.')
                        ->action(fn ($records) => $records->each->update(['category_id' => null]))
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
