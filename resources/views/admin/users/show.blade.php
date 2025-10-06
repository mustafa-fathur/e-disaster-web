<x-layouts.app :title="__('User Details')">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('User Details') }}
            </h2>
            <a href="{{ route('admin.users') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- User Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-xl font-medium text-gray-700 dark:text-gray-300">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h3>
                            <p class="text-lg text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                            <div class="mt-2 flex space-x-4">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full
                                    @if($user->type->value === 'admin') bg-red-100 text-red-800
                                    @elseif($user->type->value === 'officer') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($user->type->value) }}
                                </span>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full
                                    @if($user->status->value === 'active') bg-green-100 text-green-800
                                    @elseif($user->status->value === 'registered') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($user->status->value) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">User Type</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($user->type->value) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Status</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($user->status->value) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->email_verified_at ? 'Yes (' . $user->email_verified_at->format('M d, Y') . ')' : 'No' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Location & Activity -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Location & Activity</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->location ?? 'Not provided' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Timezone</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->timezone ?? 'Not set' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($user->type->value !== 'admin')
                <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Actions</h4>
                        <div class="flex space-x-4">
                            <form method="POST" action="{{ route('admin.users.status', $user) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $user->status->value === 'active' ? 'inactive' : 'active' }}">
                                <button type="submit" 
                                        class="bg-{{ $user->status->value === 'active' ? 'red' : 'green' }}-500 hover:bg-{{ $user->status->value === 'active' ? 'red' : 'green' }}-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('Are you sure you want to {{ $user->status->value === 'active' ? 'deactivate' : 'activate' }} this user?')">
                                    {{ $user->status->value === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
