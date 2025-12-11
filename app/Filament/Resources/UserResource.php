<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Hệ thống';
    }

    public static function getModelLabel(): string
    {
        return 'Người dùng';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Người dùng';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin cơ bản')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ tên')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),

                Section::make('Bảo mật')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Xác nhận mật khẩu')
                            ->password()
                            ->same('password')
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make('Phân quyền')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Vai trò')
                            ->options([
                                'admin' => 'Quản trị viên',
                                'user' => 'Người dùng',
                            ])
                            ->required()
                            ->default('user')
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Vai trò')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Quản trị viên',
                        'user' => 'Người dùng',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Xác thực')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\SelectFilter::make('role')
                    ->label('Vai trò')
                    ->options([
                        'admin' => 'Quản trị viên',
                        'user' => 'Người dùng',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->before(function (User $record) {
                        if ($record->id === auth()->id()) {
                            throw new \Exception('Không thể xóa tài khoản đang đăng nhập!');
                        }
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
