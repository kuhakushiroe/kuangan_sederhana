<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_akun')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('saldo')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Fieldset::make('User')
                    ->relationship('user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email') // Tambahkan input email
                            ->label('Email Pengguna')
                            ->required(),
                        Forms\Components\TextInput::make('password') // Tambahkan input password
                            ->label('Password Pengguna')
                            ->required()
                            ->password(),
                    ]), // Disable input jika user_id otomatis
            ]);
    }
    public function saveFormData(array $data)
    {
        // Step 1: Create the user first
        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']), // Hash the password
        ]);
        if ($user) {
            // Step 2: Create the account with the user_id
            Account::create([
                'nama_akun' => $data['nama_akun'],
                'saldo' => $data['saldo'],
                'user_id' => $user->id, // Use the newly created user's ID
            ]);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_akun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name') // Menampilkan nama pengguna
                    ->label('Nama Pengguna')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email') // Menampilkan email pengguna
                    ->label('Email Pengguna')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
