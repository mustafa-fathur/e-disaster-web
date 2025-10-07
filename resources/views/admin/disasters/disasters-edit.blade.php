<x-layouts.app :title="__('Edit Disaster')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit Disaster') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-800">
                    <form method="POST" action="{{ route('admin.disasters.update', $disaster) }}" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Title') }}</label>
                            <input name="title" value="{{ old('title', $disaster->title) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Source') }}</label>
                            <select name="source" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" required>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->value }}" @selected($disaster->source->value === $source->value)>{{ $source->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Type') }}</label>
                            <select name="types" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" @selected($disaster->types->value === $type->value)>{{ $type->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Status') }}</label>
                            <select name="status" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected($disaster->status->value === $status->value)>{{ $status->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Description') }}</label>
                            <textarea name="description" rows="4" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900">{{ old('description', $disaster->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Date') }}</label>
                            <input type="date" name="date" value="{{ old('date', optional($disaster->date)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Time') }}</label>
                            <input type="time" name="time" value="{{ old('time', optional($disaster->time)->format('H:i')) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Location') }}</label>
                            <input name="location" value="{{ old('location', $disaster->location) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Coordinate') }}</label>
                            <input name="coordinate" value="{{ old('coordinate', $disaster->coordinate) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Latitude') }}</label>
                            <input name="lat" type="number" step="any" value="{{ old('lat', $disaster->lat) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Longitude') }}</label>
                            <input name="long" type="number" step="any" value="{{ old('long', $disaster->long) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Magnitude') }}</label>
                            <input name="magnitude" type="number" step="any" value="{{ old('magnitude', $disaster->magnitude) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Depth') }}</label>
                            <input name="depth" type="number" step="any" value="{{ old('depth', $disaster->depth) }}" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-neutral-500 focus:ring-neutral-500 dark:border-neutral-700 dark:bg-neutral-900" />
                        </div>

                        <div class="md:col-span-2 flex items-center justify-end gap-2">
                            <a href="{{ route('admin.disasters') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 transition hover:bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-200 dark:ring-neutral-700">{{ __('Cancel') }}</a>
                            <button type="submit" class="inline-flex items-center rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-800 dark:bg-neutral-200 dark:text-neutral-900 dark:hover:bg-white">{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>


