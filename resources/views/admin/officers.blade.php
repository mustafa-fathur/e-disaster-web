<x-layouts.app :title="__('Officers')">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ __('Officers') }}</h2>
            <button type="button" onclick="document.getElementById('officer-create-modal').showModal()" class="rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">Create Officer</button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Manage officer accounts</h3>
                            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">Create, edit, and remove officers. Officers have access to both web and mobile features.</p>
                        </div>
                        <button type="button" onclick="document.getElementById('officer-create-modal').showModal()" class="hidden sm:inline-flex items-center rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">Create Officer</button>
                    </div>
                </div>
                @if (session('success'))
                    <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                        <div class="sm:col-span-3">
                            <input name="search" value="{{ request('search') }}" placeholder="Search name or email" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
                        </div>
                        <div class="sm:col-span-1"><button class="w-full rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">Filter</button></div>
                        <div class="sm:col-span-1"><button type="button" onclick="document.getElementById('officer-create-modal').showModal()" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Create</button></div>
                    </form>
                </div>

                <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Officer</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Created</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($officers as $officer)
                            <tr>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $officer->name }}</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ $officer->email }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $officer->status->value === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">{{ ucfirst($officer->status->value) }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $officer->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-3 text-right">
                                    <button type="button" onclick="document.getElementById('officer-edit-{{ $officer->id }}').showModal()" class="mr-3 text-blue-600 hover:underline dark:text-blue-400">Edit</button>
                                    <form action="{{ route('admin.officers.destroy', $officer) }}" method="POST" class="inline" onsubmit="return confirm('Delete this officer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline dark:text-red-400">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <dialog id="officer-edit-{{ $officer->id }}" class="mx-auto w-full max-w-lg overflow-hidden rounded-xl bg-white p-0 shadow-xl backdrop:bg-black/40 dark:bg-gray-800">
                                <form method="dialog">
                                    <div class="flex items-center justify-between border-b border-neutral-200 p-4 dark:border-neutral-700">
                                        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Edit Officer</h3>
                                        <button class="rounded-md px-2 py-1 text-sm text-neutral-600 transition-colors hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-700">Close</button>
                                    </div>
                                </form>
                                <form action="{{ route('admin.officers.update', $officer) }}" method="POST" class="grid gap-4 p-6">
                                    @csrf
                                    @method('PATCH')
                                    <input name="name" value="{{ $officer->name }}" placeholder="Full name" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
                                    <input name="email" type="email" value="{{ $officer->email }}" placeholder="Email" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
                                    <input name="password" type="password" placeholder="New password (optional)" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
                                    <div class="flex items-center justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-neutral-700">
                                        <button type="button" onclick="document.getElementById('officer-edit-{{ $officer->id }}').close()" class="rounded-md px-3 py-2 text-sm text-neutral-600 transition-colors hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-700">Cancel</button>
                                        <button class="rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">Save</button>
                                    </div>
                                </form>
                            </dialog>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No officers found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $officers->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<dialog id="officer-create-modal" class="mx-auto w-full max-w-lg overflow-hidden rounded-xl bg-white p-0 shadow-xl backdrop:bg-black/40 dark:bg-gray-800">
    <form method="dialog">
        <div class="flex items-center justify-between border-b border-neutral-200 p-4 dark:border-neutral-700">
            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Create Officer</h3>
            <button class="rounded-md px-2 py-1 text-sm text-neutral-600 transition-colors hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-700">Close</button>
        </div>
    </form>
    <form action="{{ route('admin.officers.store') }}" method="POST" class="grid gap-4 p-6">
        @csrf
        <input name="name" placeholder="Full name" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
        <input name="email" type="email" placeholder="Email" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
        <input name="password" type="password" placeholder="Password" class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-gray-700 dark:text-gray-200" />
        <div class="flex items-center justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-neutral-700">
            <button type="button" onclick="document.getElementById('officer-create-modal').close()" class="rounded-md px-3 py-2 text-sm text-neutral-600 transition-colors hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-700">Cancel</button>
            <button class="rounded-md bg-neutral-900 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">Create</button>
        </div>
    </form>
</dialog>

