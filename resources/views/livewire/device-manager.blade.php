<div>
    {{-- ## Cabecera de la Página --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Dispositivos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                {{-- ## Botón para Añadir Dispositivo --}}
                <div class="flex justify-end mb-4">
                    <x-button wire:click="showAddDeviceModal">
                        {{ __('Añadir Nuevo Dispositivo') }}
                    </x-button>
                </div>

                {{-- ## Tabla de Dispositivos (con la nueva columna y botón) --}}
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nombre</th>
                                <th scope="col" class="px-6 py-3">ID Único (Serial Number)</th>
                                <th scope="col" class="px-6 py-3">Estado</th>
                                <th scope="col" class="px-6 py-3">Registrado el</th>
                                {{-- /// --- NUEVA COLUMNA PARA ACCIONES --- /// --}}
                                <th scope="col" class="px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($devices as $device)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{-- Convertir el nombre en un enlace --}}<a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline" wire:navigate>{{ $device->name }}</a></td>
                                    <td class="px-6 py-4">{{ $device->serial_number }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $device->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($device->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $device->created_at->format('d/m/Y H:i') }}</td>
                                    {{-- /// --- NUEVA CELDA CON EL BOTÓN DE ELIMINAR --- /// --}}
                                    <td class="px-6 py-4 text-right">
                                        <button wire:click="confirmDeviceDeletion({{ $device->id }})" class="font-medium text-red-600 hover:underline">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No tienes dispositivos registrados. Haz clic en "Añadir Nuevo Dispositivo" para empezar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- ## Modal para Añadir Dispositivo (sin cambios) --}}
    <x-dialog-modal wire:model.live="showingAddDeviceModal">
        <x-slot name="title">
            {{ __('Añadir Nuevo Dispositivo') }}
        </x-slot>
        <x-slot name="content">
            <div wire:loading wire:target="showAddDeviceModal" class="text-center w-full">
                <p class="text-gray-600">Generando token...</p>
            </div>
            <div wire:loading.remove wire:target="showAddDeviceModal">
                @if ($provisioningToken && !str_contains($provisioningToken, 'Error'))
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-4">
                            Usa el siguiente token en el portal de configuración de tu dispositivo ESP8266.
                        </p>
                        <div class="p-4 bg-gray-100 border-dashed border-2 border-gray-300 rounded-lg">
                            <p class="text-3xl font-mono tracking-widest text-gray-800">{{ $provisioningToken }}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Este token es de un solo uso y expirará en 15 minutos.
                        </p>
                    </div>
                @elseif($provisioningToken)
                    <div class="text-center text-red-600">
                        <p>{{ $provisioningToken }}</p>
                        <p class="text-sm text-gray-500 mt-2">Por favor, revisa los logs o contacta a soporte.</p>
                    </div>
                @endif
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showingAddDeviceModal', false)" wire:loading.attr="disabled">
                {{ __('Cerrar') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- /// --- NUEVO: Modal de Confirmación para Eliminar Dispositivo --- /// --}}
    <x-confirmation-modal wire:model.live="confirmingDeviceDeletion">
        <x-slot name="title">
            Eliminar Dispositivo
        </x-slot>

        <x-slot name="content">
            ¿Estás seguro de que deseas eliminar este dispositivo? Esta acción no se puede deshacer y se dejarán de registrar sus mediciones.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingDeviceDeletion', false)" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="deleteDevice()" wire:loading.attr="disabled">
                Eliminar Dispositivo
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>