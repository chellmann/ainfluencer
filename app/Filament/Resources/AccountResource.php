<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Select::make('brand_id')
                    ->relationship(name: 'Brand', titleAttribute: 'name'),
                Forms\Components\TextInput::make('handle')
                    ->required()
                    ->maxLength(255)
                    ->label('Account Handle'),
                Forms\Components\Select::make('platform')
                    ->required()
                    ->options([
                        'instagram' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'youtube' => 'YouTube',
                    ]),
                Forms\Components\TextInput::make('foreign_id')->label('ID auf Platform'),
                Forms\Components\TagsInput::make('hours')
                    ->label('Hours')
                    ->placeholder('Enter hours'),
                Forms\Components\TextInput::make('minute')
                    ->label('Minute')
                    ->placeholder('Enter minutes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('ID'),
                Tables\Columns\TextColumn::make('handle')
                    ->sortable()
                    ->searchable()
                    ->label('Account Handle'),
                Tables\Columns\TextColumn::make('platform')
                    ->sortable()
                    ->searchable()
                    ->label('Platform'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable()
                    ->searchable()
                    ->label('Brand Name'),
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
