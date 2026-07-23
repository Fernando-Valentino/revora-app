@extends('layouts.app')

@section('title', 'Kelola Model Prediksi')
@section('subtitle', 'Generate prediksi pendapatan retribusi parkir menggunakan model Support Vector Regression (SVR) standar.')

@section('content')
<div class="container-fluid p-0">
    
    <!-- Custom CSS Styles -->
    <x-prediksi.styles />

    {{-- Skeleton Placeholder --}}
    <div class="sk-wrapper">
        <!-- Stepper Card Skeleton -->
        <div class="card mb-4">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center px-4">
                    @for($i = 0; $i < 4; $i++)
                        <div class="d-flex align-items-center gap-2">
                            <span class="skeleton skeleton-circle" style="width: 32px; height: 32px;"></span>
                            <span class="skeleton skeleton-text" style="width: 100px; margin-bottom: 0;"></span>
                        </div>
                        @if($i < 3)
                            <div class="skeleton" style="height: 2px; flex-grow: 1; margin: 0 15px; background-color: var(--border);"></div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
        
        <!-- Two Column Content Skeletons (representing Step 1) -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="skeleton-card">
                    <span class="skeleton skeleton-text lg mb-4" style="width: 150px;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-0" style="width: 100%;"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="skeleton-card">
                    <span class="skeleton skeleton-text lg mb-4" style="width: 150px;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-3" style="width: 100%;"></span>
                    <span class="skeleton skeleton-text mb-0" style="width: 100%;"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Real Content --}}
    <div class="sk-content">
    <!-- 1. Stepper Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="stepper-wrapper">
                <div class="stepper-item active" id="stepper-item-1" style="cursor: pointer;" onclick="goToStep(1)">
                    <div class="step-number">1</div>
                    <div class="step-title">Validasi Dataset</div>
                </div>
                <div class="stepper-line" id="stepper-line-1"></div>
                <div class="stepper-item" id="stepper-item-2" style="cursor: pointer;" onclick="goToStep(2)">
                    <div class="step-number">2</div>
                    <div class="step-title">Konfigurasi SVR</div>
                </div>
                <div class="stepper-line" id="stepper-line-2"></div>
                <div class="stepper-item" id="stepper-item-3" style="cursor: pointer;" onclick="goToStep(3)">
                    <div class="step-number">3</div>
                    <div class="step-title">Generate Prediksi</div>
                </div>
                <div class="stepper-line" id="stepper-line-3"></div>
                <div class="stepper-item" id="stepper-item-4" style="cursor: pointer;" onclick="goToStep(4)">
                    <div class="step-number">4</div>
                    <div class="step-title">Hasil Evaluasi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 1 CONTENT -->
    <x-prediksi.step1
        :totalPendapatan="$totalPendapatan"
        :periodeAwalFormatted="$periodeAwalFormatted"
        :periodeAkhirFormatted="$periodeAkhirFormatted"
        :jumlahRayon="$jumlahRayon"
        :jumlahHariLibur="$jumlahHariLibur"
        :jumlahWeekend="$jumlahWeekend"
        :datasetReady="$datasetReady"
        :hasPendapatan="$hasPendapatan"
        :hasRayon="$hasRayon"
        :hasJuruParkir="$hasJuruParkir"
        :hasHariLibur="$hasHariLibur"
    />

    <!-- STEP 2 CONTENT -->
    <x-prediksi.step2
        :historyRuns="$historyRuns"
        :lastRun="$lastRun"
    />

    <!-- STEP 3 CONTENT -->
    <x-prediksi.step3
        :lastRun="$lastRun"
        :datasetReady="$datasetReady"
        :pipelineData="$pipelineData"
        :params="$params"
    />

    <!-- STEP 4 CONTENT -->
    <x-prediksi.step4
        :lastRun="$lastRun"
        :chartData="$chartData"
        :rayons="$rayons"
        :metrics="$metrics"
    />

</div> <!-- closes sk-content -->
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const hasLastRun = @json(isset($lastRun) && $lastRun->status == 'success');
    let activeStep = 1;

    window.confirmDeleteRun = function(runId, startedAt) {
        SwalConfirm(
            'Hapus Riwayat Pelatihan?',
            "Riwayat pelatihan tanggal " + startedAt + " beserta hasil prediksinya akan dihapus secara permanen!",
            'Ya, Hapus!',
            function() {
                document.getElementById('reset_run_id').value = runId;
                document.getElementById('resetModelForm').submit();
            }
        );
    };

    window.confirmResetAll = function() {
        SwalConfirm(
            'Reset Semua Riwayat?',
            "Seluruh riwayat pelatihan model dan hasil prediksi SVR akan dihapus secara permanen dari database!",
            'Ya, Reset Semua!',
            function() {
                document.getElementById('reset_run_id').value = '';
                document.getElementById('resetModelForm').submit();
            }
        );
    };

    window.goToStep = function(stepNum) {
        if (stepNum === 4 && !hasLastRun) {
            SwalAlertWarning('Prediksi Belum Dijalankan!', 'Silakan jalankan proses Generate Prediksi SVR terlebih dahulu pada Langkah 3.');
            return;
        }

        // Update stepper UI
        for (let i = 1; i <= 4; i++) {
            const stepperItem = document.getElementById(`stepper-item-${i}`);
            if (stepperItem) {
                stepperItem.classList.remove('active', 'completed');
                if (i < stepNum) {
                    stepperItem.classList.add('completed');
                } else if (i === stepNum) {
                    stepperItem.classList.add('active');
                }
            }

            const stepperLine = document.getElementById(`stepper-line-${i}`);
            if (stepperLine) {
                stepperLine.classList.remove('completed');
                if (i < stepNum) {
                    stepperLine.classList.add('completed');
                }
            }

            const contentSection = document.getElementById(`step-content-${i}`);
            if (contentSection) {
                contentSection.classList.add('d-none');
            }
        }

        // Show active content
        const activeContent = document.getElementById(`step-content-${stepNum}`);
        if (activeContent) {
            activeContent.classList.remove('d-none');
        }
        
        activeStep = stepNum;

        if (stepNum === 4) {
            setTimeout(function() {
                const target = document.getElementById('step-content-4');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 150);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Prediction DataTable
        const predTable = $('#predictionTable').DataTable({
            processing: true,
            ajax: {
                url: '{{ route("operator.prediksi.data") }}'
            },
            columns: [
                { 
                    data: null, 
                    render: function (data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
                    }
                },
                { 
                    data: 'tanggal',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            if (!data) return '-';
                            const p = data.split('-');
                            if (p.length === 3) {
                                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                return parseInt(p[2], 10) + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + p[0];
                            }
                            return data;
                        }
                        return data;
                    }
                },
                { 
                    data: 'rayon_name',
                    render: function (data) {
                        return `<span class="badge bg-light text-dark border">${data}</span>`;
                    }
                },
                { 
                    data: 'actual_value', 
                    className: 'text-end fw-semibold',
                    render: function (data) {
                        return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                    }
                },
                { 
                    data: 'predicted_value', 
                    className: 'text-end fw-bold text-primary-custom',
                    render: function (data) {
                        return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                    }
                },
                { 
                    data: 'error_value', 
                    className: 'text-end text-danger',
                    render: function (data) {
                        return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                    }
                },
                { 
                    data: 'percentage_error', 
                    className: 'text-end fw-semibold',
                    render: function (data) {
                        return parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
                    }
                }
            ],
            columnDefs: [
                { orderable: false, targets: [0] }
            ],
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            order: [[1, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });

        // Set consecutive numbering on table draw
        predTable.on('draw.dt', function () {
            const info = predTable.page.info();
            predTable.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = info.start + i + 1;
            });
        });

        // Trigger client-side filtering on rayon filter change
        $('#filter_rayon_id').on('change', function() {
            const val = $(this).val();
            const text = val > 0 ? $(this).find('option:selected').text() : '';
            predTable.column(2).search(text ? '^' + text + '$' : '', true, false).draw();
            
            if (typeof window.updateSvrChart === 'function') {
                window.updateSvrChart(val);
            }
        });

        // Initialize step from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeStepParam = parseInt(urlParams.get('active_step'));
        if (activeStepParam === 4 && hasLastRun) {
            window.goToStep(4);
        } else {
            window.goToStep(1);
        }

        const btnJalankanSvrProses = document.getElementById('btnJalankanSvrProses');
        
        // Progress step management
        function setStepStatus(stepNum, status) {
            const stepElement = document.getElementById(`step-${stepNum}`);
            if (!stepElement) return;
            const iconSpan = stepElement.querySelector('.step-icon');
            
            // Remove previous classes
            stepElement.classList.remove('active', 'success-step', 'failed-step');
            
            if (status === 'pending') {
                iconSpan.innerHTML = '<i class="bi bi-circle"></i>';
                iconSpan.className = 'step-icon me-2 text-muted';
            } else if (status === 'processing') {
                iconSpan.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" style="width:12px; height:12px; border-width: 1.5px;" role="status"></div>';
                iconSpan.className = 'step-icon me-2';
                stepElement.classList.add('active');
            } else if (status === 'success') {
                iconSpan.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size: 14px;"></i>';
                iconSpan.className = 'step-icon me-2';
                stepElement.classList.add('success-step');
            } else if (status === 'failed') {
                iconSpan.innerHTML = '<i class="bi bi-x-circle-fill text-danger" style="font-size: 14px;"></i>';
                iconSpan.className = 'step-icon me-2';
                stepElement.classList.add('failed-step');
            }
        }
        
        let apiFinished = false;
        let apiError = null;
        let apiData = null;
        let currentStep = 1;
        let stepTimeout = null;
        
        function runStepSequence() {
            if (apiError) {
                // Jika API gagal, tandai langkah aktif dan sisa langkah sebagai gagal
                for (let i = currentStep; i <= 7; i++) {
                    setStepStatus(i, 'failed');
                }
                setTimeout(() => {
                    if (btnJalankanSvrProses) {
                        btnJalankanSvrProses.disabled = false;
                        btnJalankanSvrProses.innerHTML = '<i class="bi bi-play-fill me-1"></i> Generate Prediksi SVR';
                    }
                    
                    SwalError('Gagal!', apiError.message || 'Proses Generate SVR gagal. Silakan periksa layanan Python API atau kelengkapan dataset.');
                }, 1200);
                return;
            }
            
            if (currentStep < 7) {
                // Sukses pada langkah saat ini
                setStepStatus(currentStep, 'success');
                // Pindah ke langkah berikutnya
                currentStep++;
                // Set langkah berikutnya sebagai sedang diproses
                setStepStatus(currentStep, 'processing');
                
                // Jalankan langkah berikutnya setelah 400ms
                stepTimeout = setTimeout(runStepSequence, 400);
            } else {
                // Kita berada di langkah terakhir (7. Prediksi Pendapatan)
                // Tunggu respons API selesai
                if (apiFinished) {
                    setStepStatus(7, 'success');
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: (apiData && apiData.message) || 'Model SVR berhasil dijalankan.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold text-sm'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            window.location.href = "{{ route('operator.prediksi.index') }}?active_step=4";
                        });
                    }, 800);
                } else {
                    // Jika API belum selesai, cek kembali dalam 200ms
                    stepTimeout = setTimeout(runStepSequence, 200);
                }
            }
        }
        
        if (btnJalankanSvrProses) {
            btnJalankanSvrProses.addEventListener('click', function() {
                Swal.fire({
                    title: 'Jalankan Pelatihan Model SVR?',
                    text: "Sistem akan memulai proses preprocessing, training, dan peramalan menggunakan algoritma SVR. Harap tunggu hingga selesai.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#005BAA',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Jalankan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable button and show spinner
                        btnJalankanSvrProses.disabled = true;
                        btnJalankanSvrProses.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses SVR...';
                        
                        // Reset state
                        apiFinished = false;
                        apiError = null;
                        apiData = null;
                        currentStep = 1;
                        if (stepTimeout) clearTimeout(stepTimeout);
                        
                        // Reset semua langkah ke pending
                        for (let i = 1; i <= 7; i++) {
                            setStepStatus(i, 'pending');
                        }
                        
                        // Mulai langkah pertama
                        setStepStatus(1, 'processing');
                        stepTimeout = setTimeout(runStepSequence, 400);
                        
                        // AJAX Request ke backend laravel
                        fetch("{{ route('operator.prediksi.run-svr') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => { throw err; });
                            }
                            return response.json();
                        })
                        .then(data => {
                            apiFinished = true;
                            apiData = data;
                        })
                        .catch(error => {
                            apiFinished = true;
                            apiError = error;
                        });
                    }
                });
            });
        }
        
        // 2. Chart.js Implementation (Hanya jika data chart tersedia)
        @if($lastRun && $allSvrPredictions->count() > 0)
            const allSvrPreds = @json($allSvrMapped);

            function getFilteredData(preds, rayonId) {
                rayonId = parseInt(rayonId);
                if (rayonId === 0) {
                    const grouped = {};
                    preds.forEach(p => {
                        if (!grouped[p.tanggal]) {
                            grouped[p.tanggal] = { actual: 0, predicted: 0 };
                        }
                        grouped[p.tanggal].actual += p.actual_value;
                        grouped[p.tanggal].predicted += p.predicted_value;
                    });
                    const labels = Object.keys(grouped);
                    const actual = labels.map(l => grouped[l].actual);
                    const predicted = labels.map(l => grouped[l].predicted);
                    return { labels, actual, predicted };
                } else {
                    const filtered = preds.filter(p => p.rayon_id === rayonId);
                    const labels = filtered.map(p => p.tanggal);
                    const actual = filtered.map(p => p.actual_value);
                    const predicted = filtered.map(p => p.predicted_value);
                    return { labels, actual, predicted };
                }
            }

            window.updateSvrChart = function(rayonId) {
                const data = getFilteredData(allSvrPreds, rayonId);
                const countEl = document.getElementById('svr-chart-data-count');
                if (countEl) countEl.innerText = `Total Data: ${data.actual.length}`;
                if (window.svrChartInstance) {
                    window.svrChartInstance.data.labels = data.labels;
                    const dsActual = window.svrChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Aktual');
                    if (dsActual) dsActual.data = data.actual;
                    const dsPredict = window.svrChartInstance.data.datasets.find(ds => ds.label === 'Pendapatan Prediksi SVR');
                    if (dsPredict) dsPredict.data = data.predicted;
                    window.svrChartInstance.update();
                }
            }

            const ctx = document.getElementById('svrChart').getContext('2d');
            
            // Gradient Fills
            const gradientActual = ctx.createLinearGradient(0, 0, 0, 380);
            gradientActual.addColorStop(0, 'rgba(0, 91, 170, 0.12)');
            gradientActual.addColorStop(1, 'rgba(0, 91, 170, 0.0)');
 
            const gradientPredict = ctx.createLinearGradient(0, 0, 0, 380);
            gradientPredict.addColorStop(0, 'rgba(244, 197, 66, 0.08)');
            gradientPredict.addColorStop(1, 'rgba(244, 197, 66, 0.0)');
            
            const startRayonId = $('#filter_rayon_id').val() || 0;
            const startData = getFilteredData(allSvrPreds, startRayonId);
            const countEl = document.getElementById('svr-chart-data-count');
            if (countEl) countEl.innerText = `Total Data: ${startData.actual.length}`;
            
            window.svrChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: startData.labels,
                    datasets: [
                        {
                            label: 'Pendapatan Aktual',
                            data: startData.actual,
                            borderColor: '#005BAA',
                            borderWidth: 2,
                            backgroundColor: gradientActual,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#005BAA',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 2.5,
                            pointHoverRadius: 4
                        },
                        {
                            label: 'Pendapatan Prediksi SVR',
                            data: startData.predicted,
                            borderColor: '#F4C542',
                            borderWidth: 2,
                            backgroundColor: gradientPredict,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#F4C542',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 2.5,
                            pointHoverRadius: 4,
                            borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 10,
                                padding: 12,
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '500'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            padding: 10,
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                            bodyFont: { family: 'Inter', size: 11 },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let val = context.raw;
                                    return ' ' + label + ': Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                borderDash: [5, 5],
                                color: '#e2e8f0'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                },
                                font: {
                                    family: 'Inter',
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 9.5
                                },
                                maxRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        }
                    }
                }
            });
        @endif
        
    });
</script>
@endsection
