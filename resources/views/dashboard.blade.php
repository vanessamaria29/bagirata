@extends('layouts.master')

@section('title', 'Dashboard Bagirata')

@section('content')
<div class="max-w-6xl mx-auto mt-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-green-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="text-sm font-semibold uppercase opacity-80">Anda akan menerima</h3>
            <p class="text-3xl font-bold mt-2">Rp 150.000</p>
        </div>

        <div class="bg-red-500 text-white p-6 rounded-xl shadow-lg">
            <h3 class="text-sm font-semibold uppercase opacity-80">Anda berhutang</h3>
            <p class="text-3xl font-bold mt-2">Rp 45.000</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 flex flex-col justify-center items-center">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full font-bold transition">
                + Tambah Tagihan Baru
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Aktivitas Patungan Terbaru</h2>
            <a href="#" class="text-blue-600 text-sm font-semibold">Lihat Semua</a>
        </div>
        <div class="p-0">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-500 text-sm">
                    <tr>
                        <th class="px-6 py-3">Deskripsi</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-6 py-4 font-medium">Makan Siang Bareng Team</td>
                        <td class="px-6 py-4">Rp 250.000</td>
                        <td class="px-6 py-4"><span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">Menunggu Bayar</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection