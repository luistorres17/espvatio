{{-- path: resources/views/livewire/device-dashboard.blade.php --}}
<div wire:poll.5s="fetchData"> {{-- Añadido wire:poll para refrescar automáticamente --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"> {{-- Añadidas clases dark mode --}}
            Dashboard: <span class="font-normal">{{ $device->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- /// --- INICIO: Tarjetas de Estadísticas (Añadido en Tarea 3.9) --- /// --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Consumo Hoy --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Consumo Hoy</h4>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($dailyConsumptionKwh, 3) }} <span class="text-lg font-normal">kWh</span>
                    </p>
                </div>

                {{-- Consumo del Mes --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Consumo del Mes</h4>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($monthlyConsumptionKwh, 3) }} <span class="text-lg font-normal">kWh</span>
                    </p>
                </div>

                {{-- Costo Estimado del Mes --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo Estimado del Mes</h4>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                        $ {{ number_format($monthlyCostEstimate, 2) }} <span class="text-lg font-normal">MXN</span>
                    </p>
                </div>
            </div>
            {{-- /// --- FIN: Tarjetas de Estadísticas --- /// --}}

            {{-- Contenedor del gráfico existente --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6"> {{-- Añadidas clases dark mode --}}
                <div id="chart-container" wire:ignore>
                    <div id="chart"></div>
                </div>
            </div>

        </div>
    </div>

{{-- Eliminado @push y @endpush, usamos @script y @assets de Livewire 3 --}}
@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endassets

@script
<script>
    // Usamos 'livewire:navigated' para inicializar en carga de página y navegación Livewire
    document.addEventListener('livewire:navigated', () => {
        const chartElement = document.querySelector("#chart");
        if (!chartElement) return; // Salir si el elemento no existe

        // Prevenir re-renderizado si ya existe un gráfico en este elemento
        if (chartElement.hasAttribute('data-apexcharts-id')) {
            console.log('Chart already initialized.');
            return;
        }

        const initialMeasurementData = @json($measurements); // Usar $measurements directamente

        const chartOptions = {
            chart: {
                type: 'area',
                height: 350,
                zoom: { enabled: false },
                toolbar: { show: false } // Ocultar toolbar por defecto
            },
            series: [{
                name: 'Consumo (W)',
                data: initialMeasurementData
            }],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 }, // Ajustar grosor línea
            title: {
                text: 'Consumo de Energía (Últimas 24 horas)',
                align: 'left',
                style: {
                    // Adaptar color de título a dark mode
                    color: document.body.classList.contains('dark') ? '#E5E7EB' : '#1F2937'
                }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false,
                     // Adaptar color de etiquetas a dark mode
                    style: { colors: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7280' }
                }
            },
            yaxis: {
                title: {
                    text: 'Watts',
                     // Adaptar color de título Y a dark mode
                    style: { color: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7280' }
                },
                 // Adaptar color de etiquetas Y a dark mode
                labels: { style: { colors: document.body.classList.contains('dark') ? '#9CA3AF' : '#6B7280' } }
            },
            tooltip: {
                x: { format: 'dd MMM yyyy - HH:mm' },
                 // Adaptar tema del tooltip a dark mode
                theme: document.body.classList.contains('dark') ? 'dark' : 'light'
            },
             // Adaptar color de grid a dark mode
            grid: { borderColor: document.body.classList.contains('dark') ? '#374151' : '#E5E7EB' }
        };

        const chart = new ApexCharts(chartElement, chartOptions);
        chart.render();

        // Escuchar el evento de Livewire para actualizar el gráfico
        // Asegúrate que el nombre del evento coincida con el dispatch en el componente PHP
        $wire.on('data-updated', (event) => {
            // Verifica si 'event.data' existe y es un array antes de actualizar
             if (event.data && Array.isArray(event.data)) {
                 chart.updateSeries([{
                     data: event.data
                 }]);
             } else {
                 // Maneja el caso donde los datos no son válidos o están ausentes
                 console.error('Received invalid data for chart update:', event);
                 // Opcionalmente, puedes limpiar el gráfico o mostrar un mensaje
                 // chart.updateSeries([{ data: [] }]);
             }
        });
    });
</script>
@endscript

</div>