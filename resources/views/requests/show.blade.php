<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $serviceRequest->item_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <section class="bg-white p-6 shadow sm:rounded-lg">
                <dl class="space-y-4">
                    <div>
                        <dt class="font-medium text-gray-900">{{ __('Requester') }}</dt>
                        <dd class="text-gray-700">{{ $serviceRequest->requester_name }} ({{ $serviceRequest->requester_email }})</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-900">{{ __('Quantity') }}</dt>
                        <dd class="text-gray-700">{{ $serviceRequest->quantity }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-900">{{ __('Purpose') }}</dt>
                        <dd class="whitespace-pre-wrap text-gray-700">{{ $serviceRequest->purpose }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-900">{{ __('Status') }}</dt>
                        <dd class="text-gray-700">{{ $serviceRequest->status }}</dd>
                    </div>
                </dl>

                @can('updateStatus', $serviceRequest)
                    <form method="POST" action="{{ route('requests.update-status', $serviceRequest) }}" class="mt-6 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="status" value="Update status" />
                            <select id="status" name="status" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="approved">{{ __('Approved') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <x-primary-button>{{ __('Update status') }}</x-primary-button>
                    </form>
                @endcan
            </section>
        </div>
    </div>
</x-app-layout>
