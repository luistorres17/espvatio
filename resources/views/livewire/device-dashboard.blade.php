<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard: <span class="font-normal">{{ $device->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                {{-- Contenedor para el gráfico --}}
                <div id="chart-container" wire:ignore>
                    <div id="chart"></div>
                </div>

            </div>
        </div>
    </div>

@push('scripts')
{{-- Incluimos la librería desde node_modules --}}
@vite('resources/js/app.js')
<script>
    // Usamos el evento 'livewire:navigated' que se dispara después de la navegación SPA
    document.addEventListener('livewire:navigated', () => {
        // Obtenemos los datos desde el componente Livewire y los convertimos a JSON
        const measurementData = @json($this->measurements);

        var options = {
            chart: {
                type: 'area',
                height: 350,
                zoom: {
                    enabled: false
                }
            },
            series: [{
                name: 'Consumo (W)',
                data: measurementData
            }],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: 'Consumo de Energía (Últimas 24 horas)',
                align: 'left'
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false // Mostrar en hora local
                }
            },
            yaxis: {
                title: {
                    text: 'Watts'
                }
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy - HH:mm'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    });
</script>
@endpush
</div>