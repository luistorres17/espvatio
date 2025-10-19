{{-- path: resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Asegurarse de que las variables existan (pasadas desde la ruta) --}}
            @if (isset($totalMonthlyConsumptionKwh) && isset($totalMonthlyCostEstimate))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Consumo Total (Mes)</h4>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($totalMonthlyConsumptionKwh, 3) }} <span class="text-lg font-normal">kWh</span>
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo Estimado (Mes)</h4>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                            $ {{ number_format($totalMonthlyCostEstimate, 2) }} <span class="text-lg font-normal">MXN</span>
                        </p>
                    </div>

                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>