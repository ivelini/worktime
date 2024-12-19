<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Стан 2000</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{asset('assets/img/favicon.png')}}" rel="icon">
    <link href="{{asset('assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/quill/quill.snow.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/remixicon/remixicon.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/simple-datatables/style.css')}}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
</head>

<body>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center">
            <img src="assets/img/logo.png" alt="">
            <span class="d-none d-lg-block">СТАН 2000</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item dropdown pe-3">

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button class="nav-link nav-profile d-flex align-items-center pe-0" type="submit">
                        Выход
                    </button>
                </form>
                <!-- End Profile Iamge Icon -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->

</header><!-- End Header -->

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('report.timesheet') }}">
                <i class="bi bi-grid"></i>
                <span>Учет рабочего времени</span>
            </a>
            <a class="nav-link collapsed" href="{{ route('report.payrollsheet') }}">
                <i class="bi bi-grid"></i>
                <span>Учет оплаты труда</span>
            </a>
        </li><!-- End Dashboard Nav -->
    </ul>

</aside><!-- End Sidebar-->

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('report.payrollsheet') }}">
                            <br>
                            <div class="row">
                                <label for="inputDate" class="col-sm-1 col-form-label">С: </label>
                                <div class="col-sm-2">
                                    <input type="date" name="start_at" class="form-control" value="{{ $startAt->format('Y-m-d') }}">
                                </div>

                                <label for="inputDate" class="col-sm-1 col-form-label">По: </label>
                                <div class="col-sm-2">
                                    <input type="date" name="end_at" class="form-control" value="{{ $endAt->format('Y-m-d') }}">
                                </div>

                                <div class="col-sm-1">
                                    <button type="submit" class="btn btn-primary">Показать</button>
                                </div>
                                <div class="col-sm-2">
                                    <a href="{{ route('report.export.payroll', request()->all()) }}" class="btn btn-info">Выгрузить в Excel</a>
                                </div>
                                <div class="col-sm-3">
                                    <a href="{{ route('report.payrollsheet', ['start_at' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_at' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}">Прошлый месяц&nbsp;&nbsp;&nbsp;</a>
                                    <a href="{{ route('report.payrollsheet', ['start_at' => now()->startOfMonth()->format('Y-m-d'), 'end_at' => now()->endOfMonth()->format('Y-m-d')]) }}">&nbsp;&nbsp;&nbsp; Этот месяц </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Табель учета заработной платы c <b>{{ $startAt->format('d-m-Y') }}</b> по <b>{{ $endAt->format('d-m-Y') }}</b></h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">ID</th>
                                <th scope="col">ФИО</th>
                                <th scope="col">Должность</th>
                                <th scope="col">Оклад</th>
                                <th scope="col">Аванс</th>
                                <th scope="col">Отработано</th>
                                <th scope="col">Ставка за час</th>
                                <th scope="col">Оплата</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($salaryPayEmployees as $index => $payEmployee)
                                    <tr>
                                        <td>
                                            @if(\Illuminate\Support\Facades\Auth::user()?->email == 'ivelini@yandex.ru')
                                                <div style="overflow: hidden">
                                                    <div style="float: left">
                                                        <form action="{{ route('sheet-time.clear-current-month', ['emp_code' => $payEmployee->emp_code, 'date' => $startAt->format('d-m-Y')]) }}"
                                                              method="POST">
                                                            @csrf
                                                            <button type="submit" style="border: 0; background-color: #ffffff">
                                                                <i class="bi bi-x-circle" style="color: red; cursor: pointer"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div style="float: right">
                                                        {{ $index + 1 }}
                                                    </div>
                                                </div>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>{{ $payEmployee->emp_code }}</td>
                                        <td>{{ $payEmployee->fio }}</td>
                                        <td>{{ $payEmployee->position }}</td>
                                        <td>{{ $payEmployee->salary_amount }}</td>
                                        <td>{{ $payEmployee->advance }}</td>
                                        <td>{{ $payEmployee->month_duration }}</td>
                                        <td>{{ $payEmployee->per_pay_hour }}</td>
                                        <td>{{ number_format($payEmployee->salary_pay, 0, ' ', ' ') }}</td>
                                    </tr>
                                @endforeach

                                <tr>
                                    <td>Итого</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ number_format($fullAdvance, 0, ' ', ' ') }}</td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ number_format($fullSalaryPay, 0, ' ', ' ') }}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
<!-- Vendor JS Files -->
<script src="{{asset('assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
<script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/vendor/chart.js/chart.umd.js')}}"></script>
<script src="{{asset('assets/vendor/echarts/echarts.min.js')}}"></script>
<script src="{{asset('assets/vendor/quill/quill.js')}}"></script>
<script src="{{asset('assets/vendor/simple-datatables/simple-datatables.js')}}"></script>
<script src="{{asset('assets/vendor/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/vendor/php-email-form/validate.js')}}"></script>

<!-- Template Main JS File -->
<script src="{{asset('assets/js/main.js')}}"></script>
</body>

</html>
