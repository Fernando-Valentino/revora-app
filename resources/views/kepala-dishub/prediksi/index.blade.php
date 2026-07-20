@extends('layouts.app')

@section('title', 'Pemantauan Hasil Prediksi')
@section('subtitle', 'Halaman ini digunakan untuk memantau hasil peramalan retribusi parkir menggunakan model SVR Standar (Default) yang aktif.')

@section('content')
<x-pemantauan-prediksi.index
    :lastRun="$lastRun"
    :bestParams="$best_params"
    :chartActualValues="$chartActualValues"
    :chartPredictValues="$chartPredictValues"
    :chartLabels="$chartLabels"
    :rayons="$rayons"
    :rayonId="$rayonId ?? 0"
    :dataRoute="route('kepala-dishub.prediksi.data')"
    :resetUrl="request()->url()"
/>
@endsection
