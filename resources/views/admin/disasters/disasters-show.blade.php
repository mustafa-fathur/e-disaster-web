<x-layouts.app :title="__('Disaster Detail')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Disaster Detail') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
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

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <h3 class="mb-4 text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ __('Disaster Reports') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">{{ __('Title') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">{{ __('Description') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">{{ __('Lat/Long') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">{{ __('Final') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">{{ __('Reported At') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-900">
                                @forelse ($reports as $report)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $report->title ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ Str::limit($report->description ?? '-', 80) }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ ($report->lat ?? '-') . ', ' . ($report->long ?? '-') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $report->is_final_stage ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/30 dark:text-neutral-300' }}">
                                                {{ $report->is_final_stage ? __('Yes') : __('No') }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-6 text-center text-sm text-neutral-600 dark:text-neutral-300">{{ __('No reports found for this disaster.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>


