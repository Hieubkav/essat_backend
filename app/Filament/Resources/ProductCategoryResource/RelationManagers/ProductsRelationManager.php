<?php

namespace App\Filament\Resources\ProductCategoryResource\RelationManagers;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $inverseRelationship = 'categories';

    protected static ?string $title = 'Sản phẩm';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Ảnh')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('price')
                    ->label('Giá')
                    ->numeric()
                    ->sortable()
                    ->suffix(' VNĐ'),

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
                // Tạo sản phẩm mới (mở trang tạo mới ở tab mới)
                Action::make('createProduct')
                    ->label('Tạo sản phẩm')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => ProductResource::getUrl('create'))
                    ->openUrlInNewTab(),

                // Gắn sản phẩm có sẵn vào danh mục (quan hệ many-to-many)
                Actions\AttachAction::make()
                    ->label('Thêm sản phẩm có sẵn')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->multiple(),
            ])
            ->actions([
                // Mở trang chỉnh sửa ở tab mới
                Action::make('editInNewTab')
                    ->label('Sửa')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Product $record) => ProductResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),

                // Gỡ sản phẩm khỏi danh mục (không xóa sản phẩm)
                Actions\DetachAction::make()
                    ->label('Gỡ'),

                Actions\DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DetachBulkAction::make()
                        ->label('Gỡ khỏi danh mục'),

                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
