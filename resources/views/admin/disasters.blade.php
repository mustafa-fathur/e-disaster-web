<x-layouts.app :title="__('Disasters')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Disasters') }}
        </h2>
    </x-slot>

    <div class="py-6">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ __('Disaster List') }}</h3>
                            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">Browse recent disasters. Use the button to add a new disaster.</p>
                        </div>
                        <a href="{{ route('admin.disasters.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white" wire:navigate>
                            {{ __('Add New Disaster') }}
                        </a>
                    </div>
                </div>

                <div class="rounded-xl bg-white shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">Types</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-900">
                                @forelse ($disasters as $disaster)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $disaster->title }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $disaster->types->value }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $disaster->status->value === 'ongoing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' }}">
                                                {{ ucfirst($disaster->status->value) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ $disaster->location ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">{{ optional($disaster->date)->format('Y-m-d') ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.disasters.show', $disaster) }}" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 transition hover:bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-200 dark:ring-neutral-700">{{ __('View') }}</a>
                                                <a href="{{ route('admin.disasters.edit', $disaster) }}" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 transition hover:bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-200 dark:ring-neutral-700">{{ __('Edit') }}</a>
                                                <form method="POST" action="{{ route('admin.disasters.destroy', $disaster) }}" onsubmit="return confirm('Delete this disaster?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-6 text-center text-sm text-neutral-600 dark:text-neutral-300">No disasters found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4">
                        {{ $disasters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>


