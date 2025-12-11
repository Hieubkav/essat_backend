<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPosts extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Bài viết mới nhất';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Ảnh')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Trạng thái')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('edit')
                    ->label('Sửa')
                    ->url(fn (Post $record): string => route('filament.admin.resources.posts.edit', $record))
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->paginated(false);
    }
}
