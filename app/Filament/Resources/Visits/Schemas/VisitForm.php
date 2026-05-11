<?php

namespace App\Filament\Resources\Visits\Schemas;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Setting;
use App\Models\VisitCollection;
use App\Rules\LocalTenDigitPhone;
use App\Services\VisitContactResolver;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isPrivileged = $user?->canManageAllVisits() ?? false;

        $visitFields = [];
        if ($isPrivileged) {
            $visitFields[] = Select::make('user_id')
                ->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('name'))
                ->searchable()
                ->preload()
                ->required();
        } else {
            $visitFields[] = Hidden::make('user_id')
                ->default(fn () => Auth::id())
                ->dehydrated();
        }

        $visitFields[] = Select::make('customer_id')
            ->label('Customer')
            ->relationship(
                name: 'customer',
                titleAttribute: 'name',
                modifyQueryUsing: function ($query) use ($user, $isPrivileged): void {
                    $query->with('contacts')->orderBy('name');
                    if ($user && ! $isPrivileged) {
                        $query->where('assigned_user_id', $user->id);
                    }
                }
            )
            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->name
                .($record->contacts->firstWhere('is_primary')?->name
                    ? ' — '.$record->contacts->firstWhere('is_primary')->name
                    : ''))
            ->searchable(['name', 'city', 'phone'])
            ->preload()
            ->live()
            ->afterStateUpdated(fn ($set) => $set('contact_id', null))
            ->required();

        $visitFields[] = Select::make('contact_id')
            ->label('Contact person')
            ->options(fn (Get $get): array => Contact::query()
                ->where('customer_id', $get('customer_id'))
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (Contact $c): array => [$c->id => $c->listLabel()])
                ->all())
            ->searchable()
            ->preload()
            ->required()
            ->disabled(fn (Get $get): bool => blank($get('customer_id')))
            ->createOptionForm([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->required()
                    ->maxLength(10)
                    ->placeholder('0541234567')
                    ->extraInputAttributes([
                        'maxlength' => 10,
                        'inputmode' => 'numeric',
                        'pattern' => '0[0-9]{9}',
                        'title' => __('10 digits, numbers only, starting with 0'),
                    ])
                    ->helperText(__('10 digits, numbers only, starting with 0.'))
                    ->rules([new LocalTenDigitPhone()]),
                TextInput::make('position')
                    ->required()
                    ->maxLength(255),
            ])
            ->createOptionUsing(function (array $data, Get $get): int {
                return VisitContactResolver::createContact(
                    (int) $get('customer_id'),
                    $data['name'],
                    $data['phone'],
                    $data['position'],
                )->id;
            });

        if ($isPrivileged) {
            $visitFields[] = DateTimePicker::make('visited_at')
                ->label('Visit date & time')
                ->default(now())
                ->seconds(false)
                ->required();
        } else {
            $visitFields[] = DateTimePicker::make('visited_at')
                ->label('Visit date & time')
                ->default(now())
                ->seconds(false)
                ->disabled()
                ->dehydrated(false)
                ->helperText('Recorded automatically. Admins can change this in Admin.');
        }

        $visitFields[] = Textarea::make('comments')
            ->rows(3)
            ->columnSpanFull();

        $visitFields[] = ViewField::make('geolocation_hint')
            ->view('filament.forms.visit-geolocation')
            ->columnSpanFull();

        return $schema
            ->components([
                Section::make('Visit')
                    ->schema($visitFields)
                    ->columns(2),
                Section::make('Order (items sold)')
                    ->description('Optional. Add one row per product on this visit.')
                    ->schema([
                        Repeater::make('order_lines_block')
                            ->default([])
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn (): array => Product::query()->active()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->prefix(fn (): string => Setting::currencySymbol())
                                    ->helperText('Defaults to catalog price if left empty when saving.'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add product line')
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Samples')
                    ->schema([
                        Repeater::make('samples_block')
                            ->default([])
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn (): array => Product::query()->active()->where('can_be_sampled', true)->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add sample line')
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Collections (payments received)')
                    ->schema([
                        Repeater::make('collections_block')
                            ->default([])
                            ->schema([
                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix(fn (): string => Setting::currencySymbol())
                                    ->required(),
                                Select::make('payment_method')
                                    ->label('Payment method')
                                    ->options(VisitCollection::paymentMethodOptions())
                                    ->required(),
                                TextInput::make('notes')
                                    ->maxLength(500),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add collection')
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
