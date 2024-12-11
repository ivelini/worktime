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
            @if(\Illuminate\Support\Facades\Auth::user()?->type == \App\Models\User::$ADMIN)
                <a class="nav-link collapsed" href="{{ route('report.payrollsheet') }}">
                    <i class="bi bi-grid"></i>
                    <span>Учет оплаты труда</span>
                </a>
            @endif
        </li><!-- End Dashboard Nav -->
    </ul>

</aside><!-- End Sidebar-->

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('report.timesheet') }}">
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
                                    @if(\Illuminate\Support\Facades\Auth::user()->type == 'admin')
                                        <a href="{{ route('report.export.timesheet', request()->all()) }}" class="btn btn-info">Выгрузить в Excel</a>
                                    @endif
                                </div>
                                <div class="col-sm-3">
                                    <a href="{{ route('report.timesheet', ['start_at' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_at' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}">Прошлый месяц&nbsp;&nbsp;&nbsp;</a>
                                    <a href="{{ route('report.timesheet', ['start_at' => now()->startOfMonth()->format('Y-m-d'), 'end_at' => now()->format('Y-m-d')]) }}">&nbsp;&nbsp;&nbsp; Этот месяц </a>
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
                        <h5 class="card-title">Табель рабочего времени c <b>{{ $startAt->format('d-m-Y') }}</b> по <b>{{ $endAt->format('d-m-Y') }}</b></h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">Дата</th>
                                <th scope="col">День недели</th>
                                <th scope="col">Расписание</th>
                                <th scope="col">Приход</th>
                                <th scope="col">Уход</th>
                                <th scope="col">Всего отработано</th>
                            </tr>
                            </thead>
                            <tbody>

                                @foreach($sheetTimeRows as $key => $sheetTimeRowsEmployee)

                                    <tr>
                                        <td colspan="6" style="background: #e9ecef">
                                            <strong>{{ $key }}</strong>
                                        </td>
                                    </tr>

{{--                                    @dd($sheetTimeRowsEmployee);--}}
                                    @foreach($sheetTimeRowsEmployee as $sheetTime)

                                        @if(isset($sheetTime['sheet_time_id']))
                                            <tr id="sheet_time_{{ $sheetTime['sheet_time_id'] + 2 }}">
                                        @else
                                            <tr>
                                        @endif

                                            <td>{{ $sheetTime['date'] }}</td>
                                            <td>{{ $sheetTime['dey_of_the_week'] }}</td>
                                            <td style="overflow: hidden">
                                                @if($sheetTime['is_night'])
                                                    <div style="float: left">20:00-08:00 (ночное)</div>
                                                @else
                                                    <div style="float: left">{{ $sheetTime['schedule_name'] }}</div>
                                                @endif


                                                @if(strpos($key, 'Печник') && !$loop->last)

                                                    <div style="float: right;">


                                                            @if($sheetTime['is_night'])

                                                                <form action="{{ route('sheet-time.set-day-shift') }}" method="POST">
                                                                    @csrf
                                                                    <input name="sheet_time_id" value="{{ $sheetTime['sheet_time_id'] }}" hidden />
                                                                    <input name="anchor" value="sheet_time_{{ $sheetTime['sheet_time_id'] }}" hidden />
                                                                    <button type="submit" style="font-size: 10px; background-color: #b1b1b1; opacity: 0.5">Перевод в дневную смену</button>
                                                                </form>
                                                            @else

                                                                <form action="{{ route('sheet-time.set-night-shift') }}" method="POST">
                                                                    @csrf
                                                                    <input name="sheet_time_id" value="{{ $sheetTime['sheet_time_id'] }}" hidden />
                                                                    <input name="anchor" value="sheet_time_{{ $sheetTime['sheet_time_id'] }}" hidden />
                                                                    <button type="submit" style="font-size: 10px; background-color: #f5f5f5; opacity: 0.5">Перевод в ночную смену</button>
                                                                </form>
                                                            @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $sheetTime['min_time'] }}</td>
                                            <td>{{ $sheetTime['max_time'] }}</td>
                                            <td>
                                                <div style="overflow: hidden">
                                                    <div style="float: left">
                                                        <div style="float: left; width: 30px">
                                                            {{ $sheetTime['duration'] }}
                                                        </div>
                                                        @if($sheetTime['corrected'] instanceof \App\Models\SheetTimeDto\CorrectedDto && $sheetTime['corrected']->is_isset)
                                                            <div style="float: left">
                                                                <i class="bi bi-lightbulb" style="color: orange"
                                                                   data-bs-toggle="tooltip"
                                                                   data-bs-placement="top"
                                                                   data-bs-original-title="{{ $sheetTime['corrected']->userName }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                   Было: {{ $sheetTime['corrected']->original->start }} - {{ $sheetTime['corrected']->original->end }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                   Стало: {{ $sheetTime['corrected']->modify->start }} - {{ $sheetTime['corrected']->modify->end }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                   Комментарий: {{ $sheetTime['corrected']->comment ?? '' }}"></i>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if(! $loop->last)
                                                        <div style="float: right" class="icon" onclick="getModalCorrectSheetTime(event.target.dataset)">
                                                            <i style="cursor: pointer; color: #d5d5d5"
                                                               class="bi bi-pencil"
                                                               data-emp-id = "{{ $sheetTime['emp_id'] }}"
                                                               data-emp-name = "{{ $sheetTime['emp_name'] }}"
                                                               data-date = "{{ $sheetTime['date'] }}"
                                                               data-date-for-form = "{{ $sheetTime['date_for_form'] }}"
                                                               data-day-of-the-week = "{{ $sheetTime['dey_of_the_week'] }}"
                                                               data-sheet-time-id = "{{ $sheetTime['sheet_time_id'] }}"
                                                               data-is-night = "{{ $sheetTime['is_night'] ? 'true' : 'false' }}"
                                                               data-min_time = "{{ $sheetTime['min_time'] }}"
                                                               data-max_time = "{{ $sheetTime['max_time'] }}"
                                                               data-user-id = "{{ $sheetTime['user_id'] }}"
                                                               data-comment = "{{ $sheetTime['corrected']?->comment ?? '' }}"
                                                            ></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<div class="modal fade" id="basicModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #f5f5f5;">
            <form id="sheet-time-form" onsubmit="handleUpdateOrCreateSheetTime(event)">
                <div class="modal-header">
                    <h5 class="modal-title">Корректировка смены</h5>
                    <button type="button" class="btn-close" aria-label="Close" onclick="closeModal()"></button>
                </div>
                <div class="modal-body">
                        <input name="user_id" type="text" class="form-control" hidden />
                        <input name="emp_id" type="text" class="form-control" hidden />
                        <input name="sheet_time_id" type="text" class="form-control" hidden />
                        <input name="date" type="date" class="form-control" hidden />
                        <div class="row mb-3">
                            <label for="inputText" class="col-sm-3 col-form-label">Сотрудник</label>
                            <div class="col-sm-9">
                                <input name="emp_name" type="text" class="form-control" readonly />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputText" class="col-sm-3 col-form-label">Дата</label>
                            <div class="col-sm-9">
                                <input name="date_string" type="text" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Приход</label>
                            <div class="col-sm-9">
                                <input name="min_time" type="time" class="form-control" required/>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Уход</label>
                            <div class="col-sm-9">
                                <input name="max_time" type="time" class="form-control" required/>
                            </div>
                        </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Комментарий</label>
                        <div class="col-sm-9">
                            <input name="comment" type="text" class="form-control" required/>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" style="margin-right: 100px" class="btn btn-danger" onclick="clearSheetTime()">Убрать смену</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Отменить</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

<script>

    const modal = document.getElementById('basicModal')
    const dataForm = document.getElementById('sheet-time-form')

    /**
     *
     * @param payload
     */
    const getModalCorrectSheetTime = (payload) => {

        dataForm.user_id.value = payload.userId
        dataForm.emp_id.value = payload.empId
        dataForm.sheet_time_id.value = payload.sheetTimeId
        dataForm.emp_name.value = payload.empName
        dataForm.date_string.value = payload.date + ' (' + payload.dayOfTheWeek + ')'
        dataForm.date.value = payload.dateForForm
        dataForm.min_time.value = payload.min_time
        dataForm.max_time.value = payload.max_time
        dataForm.comment.value = payload.comment

        showModal()
    }

    const showModal = () => {
        modal.style.display = 'block'
        modal.classList.add('show')
    }

    const closeModal = () => {
        modal.style.display = 'none'
        modal.classList.remove('show')
    }

    const handleUpdateOrCreateSheetTime = async (event) => {

        event.preventDefault()

        let url = (new URL(document.documentURI)).origin + '/api/sheet-time'

        if(dataForm.sheet_time_id.value !== '') {
            url = url + '/' + dataForm.sheet_time_id.value
        }

        let response = await fetch(url, {
            method: 'POST',
            body: new FormData(dataForm)
        });

        if(response.status === 200) {
            closeModal()
            window.location.reload()
        }
    }

    const clearSheetTime = async () => {
        let url = (new URL(document.documentURI)).origin
            + '/api/sheet-time' + '/' + dataForm.sheet_time_id.value
            + '?user_id=' + dataForm.user_id.value
            + '&comment=' + dataForm.comment.value

        let response = await fetch(url, {method: 'DELETE'});

        if(response.status === 200) {
            closeModal()
            window.location.reload()
        }
    }
</script>

</html>
