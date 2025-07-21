@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('page_title', 'Dashboard Admin')

@section('content')
<!-- Dashboard Content -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Total Pengguna</h5>
        <p class="font-normal text-3xl text-gray-700 dark:text-gray-400">{{ number_format($userCount) }}</p>
    </div>
    <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Pendapatan</h5>
        <p class="font-normal text-3xl text-gray-700 dark:text-gray-400">{{ $pendapatan }}</p>
    </div>
    <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Pesanan Baru</h5>
        <p class="font-normal text-3xl text-gray-700 dark:text-gray-400">{{ $pesananBaru }}</p>
    </div>
    <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Pengunjung</h5>
        <p class="font-normal text-3xl text-gray-700 dark:text-gray-400">{{ number_format($pengunjung) }}</p>
    </div>
</div>
<!-- Table -->
<div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-4">
    <div class="p-4 bg-white dark:bg-gray-800">
        <label for="table-search" class="sr-only">Search</label>
        <div class="relative mt-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input type="text" id="table-search" class="block p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Cari pengguna">
        </div>
    </div>
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Nama
                </th>
                <th scope="col" class="px-6 py-3">
                    Email
                </th>
                <th scope="col" class="px-6 py-3">
                    Status
                </th>
                <th scope="col" class="px-6 py-3">
                    Aksi
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentUsers as $user)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                    <img class="w-10 h-10 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-{{ $loop->iteration }}.jpg" alt="{{ $user->name }} avatar">
                    <div class="pl-3">
                        <div class="text-base font-semibold">{{ $user->name }}</div>
                        <div class="font-normal text-gray-500">{{ $user->role ?? 'Member' }}</div>
                    </div>
                </th>
                <td class="px-6 py-4">
                    {{ $user->email }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        @if($user->is_verified)
                        <div class="h-2.5 w-2.5 rounded-full bg-green-500 mr-2"></div> Aktif
                        @else
                        <div class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"></div> Tidak Aktif
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Chart Section -->
<div class="border-2 p-4 border-gray-200 border-dashed rounded-lg dark:border-gray-700">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <h5 class="mb-2 text-lg font-bold tracking-tight text-gray-900 dark:text-white">Pendapatan Bulanan</h5>
            <div class="relative">
                <canvas id="monthlyRevenue"></canvas>
            </div>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            <h5 class="mb-2 text-lg font-bold tracking-tight text-gray-900 dark:text-white">Distribusi Pengguna</h5>
            <div class="relative">
                <canvas id="userDistribution"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Monthly Revenue Chart
    const revenueCtx = document.getElementById('monthlyRevenue');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Pendapatan (Juta Rupiah)',
                data: [12, 19, 10, 15, 22, 18],
                backgroundColor: 'rgba(37, 99, 235, 0.7)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            responsive: true,
            maintainAspectRatio: true
        }
    });

    // User Distribution Chart
    const userCtx = document.getElementById('userDistribution');
    new Chart(userCtx, {
        type: 'doughnut',
        data: {
            labels: ['Admin', 'Member', 'Visitor'],
            datasets: [{
                data: [10, 65, 25],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(251, 191, 36, 0.7)'
                ],
                borderColor: [
                    'rgba(239, 68, 68, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(251, 191, 36, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true
        }
    });
</script>
@endsection 