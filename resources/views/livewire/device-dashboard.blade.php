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


<script>
    // 2. Usamos el evento 'livewire:navigated' para asegurarnos de que el script se ejecuta en cada carga de página
    document.addEventListener('livewire:navigated', () => {
        // Obtenemos los datos desde el componente Livewire
        const measurementData = @json($this->measurements);

        var options = {
            chart: {
                type: 'area',
                height: 350,
                zoom: { enabled: false }
            },
            series: [{
                name: 'Consumo (W)',
                data: measurementData
            }],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            title: {
                text: 'Consumo de Energía (Últimas 24 horas)',
                align: 'left'
            },
            xaxis: {
                type: 'datetime',
                labels: { datetimeUTC: false }
            },
            yaxis: {
                title: { text: 'Watts' }
            },
            tooltip: {
                x: { format: 'dd MMM yyyy - HH:mm' }
            }
        };

        // 3. Renderizamos el gráfico
        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    });
</script>
@endpush
</div>