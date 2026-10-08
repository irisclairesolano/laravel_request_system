<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Service Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @can('create', \App\Models\ServiceRequest::class)
                <section class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Create a request') }}</h3>
                    <form method="POST" action="{{ route('requests.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="item_name" value="Item name" />
                            <x-text-input id="item_name" name="item_name" class="mt-1 block w-full" :value="old('item_name')" required maxlength="150" />
                            <x-input-error :messages="$errors->get('item_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="quantity" value="Quantity" />
                            <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" :value="old('quantity')" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="purpose" value="Purpose" />
                            <textarea id="purpose" name="purpose" maxlength="2000" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('purpose') }}</textarea>
                            <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                        </div>

                        <x-primary-button>{{ __('Submit request') }}</x-primary-button>
                    </form>
                </section>
            @endcan

            <section class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">{{ __('Requests') }}</h3>
                <ul class="mt-4 divide-y divide-gray-200">
                    @forelse ($serviceRequests as $serviceRequest)
                        <li class="py-4">
                            <a class="font-medium text-indigo-700 underline" href="{{ route('requests.show', $serviceRequest) }}">
                                {{ $serviceRequest->item_name }}
                            </a>
                            <span class="ml-2 text-sm text-gray-600">{{ $serviceRequest->status }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-gray-600">{{ __('No requests found.') }}</li>
                    @endforelse
                </ul>

                {{ $serviceRequests->links() }}
            </section>
        </div>
    </div>
</x-app-layout>
