<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <x-app-brand class="p-5 pt-3" />

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if($user = auth()->user())
                <x-menu-separator />

                <x-list-item :item="$user" value="name" sub-value="email" no-separator link="/profile"
                    class="-mx-2 !-my-2 rounded">

                    <x-slot:actions>

                        <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff"
                            no-wire-navigate link="/logout" />

                    </x-slot:actions>
                </x-list-item>
                <x-menu-item title="{{auth()->user()->division_name}}" icon="o-building-storefront" />
                <x-menu-separator />
                @endif
                <x-menu-item title="{{__('Home')}}" icon="o-home" link="/" />
                <x-menu-sub title="{{__('Work Order')}}" icon="o-archive-box">
                    <x-menu-item title="{{__('New')}}" icon="o-rocket-launch" link="/workorder/new" />
                    <x-menu-item title="{{__('List')}}" icon="o-list-bullet" link="/workorder/list" />
                    <x-menu-item title="{{__('Job Status')}}" icon="o-gift" link="/workorder/job-status" />
                </x-menu-sub>
                <x-menu-item title="{{__('Customer')}}" icon="o-user-group" link="/customer" />
                <x-menu-sub title="{{__('Report')}}" icon="o-document-text">
                    <x-menu-item title="{{__('Daily')}}" icon="o-newspaper" link="/report/daily" />
                    <x-menu-item title="{{__('Monthly')}}" icon="o-calendar-days" link="/report/monthly" />
                    <x-menu-item title="{{__('Yearly')}}" icon="o-calendar-days" link="/report/yearly" />
                </x-menu-sub>

                @if(auth()->user()->role !='user')
                <x-menu-sub title="{{__('Settings')}}" icon="o-cog-6-tooth">
                    <x-menu-item title="{{__('Product')}}" icon="o-shopping-bag" link="/product" />
                    {{--
                    <x-menu-item title="Exchange Rate" icon="o-document-currency-dollar" link="/exchange-rate" /> --}}
                    @if(auth()->user()->role =='admin')
                    <x-menu-separator />
                    <x-menu-item title="{{__('User')}}" icon="o-users" link="/user" />
                    <x-menu-item title="{{__('App Group')}}" icon="o-rectangle-group" link="/app-group" />
                    <x-menu-item title="{{__('Division')}}" icon="o-share" link="/division" />
                    <x-menu-item title="{{__('Type')}}" icon="o-chart-bar" link="/type" />
                    <x-menu-item title="{{__('Currency')}}" icon="o-currency-dollar" link="/currency" />
                    {{--
                    <x-menu-item title="{{__('Account Code')}}" icon="o-calculator" link="/account-code" /> --}}
                    @endif
                </x-menu-sub>
                @endif
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{-- TOAST area --}}
    <x-toast />
</body>

</html>