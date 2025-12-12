<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-bars-3';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hệ thống';
    }

    public static function getModelLabel(): string
    {
        return 'Menu';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Quản lý Menu';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin menu')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên menu')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Trang chủ, Sản phẩm...'),

                        TextInput::make('url')
                            ->label('Đường dẫn (URL)')
                            ->maxLength(255)
                            ->placeholder('VD: /san-pham, /lien-he')
                            ->helperText('Để trống = trang chủ (/). Nếu là menu cha dropdown thì nhập #'),

                        Select::make('parent_id')
                            ->label('Menu cha')
                            ->relationship(
                                'parent',
                                'name',
                                fn ($query) => $query->whereNull('parent_id')->orderBy('order')
                            )
                            ->placeholder('-- Menu gốc (cấp 1) --')
                            ->preload()
                            ->searchable()
                            ->helperText('Chọn menu cha để tạo menu con (cấp 2)'),

                        Toggle::make('is_active')
                            ->label('Hiển thị')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tên menu')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        // Thêm indent cho menu con
                        if ($record->parent_id) {
                            return '↳ ' . $state;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Menu cha')
                    ->placeholder('(Gốc)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->placeholder('--')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->label('Menu con')
                    ->counts('children')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Hiển thị')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Thứ tự')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Loại menu')
                    ->options([
                        'root' => 'Menu gốc (cấp 1)',
                        'child' => 'Menu con (cấp 2)',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'root') {
                            return $query->whereNull('parent_id');
                        }
                        if ($data['value'] === 'child') {
                            return $query->whereNotNull('parent_id');
                        }
                        return $query;
                    }),

                Tables\Filters\TernaryFilter::make('is_active')
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
