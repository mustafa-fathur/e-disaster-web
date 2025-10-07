<x-layouts.app :title="__('Disaster Detail')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Disaster Detail') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-neutral-500">{{ __('Title') }}</p>
                            <p class="mt-1 text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $disaster->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">{{ __('Status') }}</p>
                            <p class="mt-1 text-base font-medium">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $disaster->status->value === 'ongoing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' }}">
                                    {{ ucfirst($disaster->status->value) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">{{ __('Type') }}</p>
                            <p class="mt-1 text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $disaster->types->value }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">{{ __('Source') }}</p>
                            <p class="mt-1 text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $disaster->source->value }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">{{ __('Date') }}</p>
                            <p class="mt-1 text-base font-medium text-neutral-900 dark:text-neutral-100">{{ optional($disaster->date)->format('Y-m-d') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">{{ __('Time') }}</p>
                            <p class="mt-1 text-base font-medium text-neutral-900 dark:text-neutral-100">{{ optional($disaster->time)->format('H:i') ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-neutral-500">{{ __('Location') }}</p>
                            <p class="mt-1 text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $disaster->location ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-neutral-500">{{ __('Description') }}</p>
                            <p class="mt-1 text-sm text-neutral-700 dark:text-neutral-300">{{ $disaster->description ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-2">
                        <a href="{{ route('admin.disasters.edit', $disaster) }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 transition hover:bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-200 dark:ring-neutral-700">{{ __('Edit') }}</a>
                        <a href="{{ route('admin.disasters') }}" class="inline-flex items-center rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">{{ __('Back to list') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>


