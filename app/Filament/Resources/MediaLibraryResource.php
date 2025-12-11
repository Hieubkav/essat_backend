<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaLibraryResource\Pages;
use App\Models\MediaLibrary;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MediaLibraryResource extends Resource
{
    protected static ?string $model = MediaLibrary::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-photo';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Nội dung';
    }

    public static function getModelLabel(): string
    {
        return 'Media';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Thư viện Media';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Thông tin Media')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên file')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('library')
                            ->label('File')
                            ->collection('library')
                            ->disk('public')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                                'image/avif',
                                'image/svg+xml',
                                'application/pdf',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('library')
                    ->label('Ảnh')
                    ->collection('library')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('media_count')
                    ->label('Số file')
                    ->counts('media')
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
            ->filters([])
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
            'index' => Pages\ListMediaLibraries::route('/'),
            'create' => Pages\CreateMediaLibrary::route('/create'),
            'edit' => Pages\EditMediaLibrary::route('/{record}/edit'),
        ];
    }
}
